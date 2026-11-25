<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Plantas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $estado = $request->input('estado', 'activos');
        $rol = $request->input('rol');
        $buscar = trim((string) $request->input('q'));

        $users = User::with(['roles', 'permissions'])
            ->when($estado === 'activos', fn ($q) => $q->where('status', User::ESTADO_ACTIVO))
            ->when($estado === 'inactivos', fn ($q) => $q->where('status', '!=', User::ESTADO_ACTIVO))
            ->when($rol, fn ($q) => $q->role($rol))
            ->when($buscar !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$buscar}%")
                ->orWhere('email', 'like', "%{$buscar}%")))
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.usuarios.index', [
            'users'    => $users,
            'roles'    => Role::orderByDesc('nivel')->get(),
            'plantas'  => Plantas::todas(),
            'estado'   => $estado,
            'resumen'  => [
                'total'     => User::count(),
                'activos'   => User::where('status', User::ESTADO_ACTIVO)->count(),
                'inactivos' => User::where('status', '!=', User::ESTADO_ACTIVO)->count(),
                'roles'     => Role::count(),
            ],
            'actor'    => $request->user(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.usuarios.form', [
            'user'            => new User(['status' => User::ESTADO_ACTIVO]),
            'rolesAsignables' => $this->rolesAsignables($request->user()),
            'plantas'         => Plantas::accesibles($request->user()),
            'rolActual'       => null,
            'plantasUsuario'  => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $actor = $request->user();
        $data = $this->validar($request);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'telefono' => $data['telefono'] ?? null,
            'password' => Hash::make($data['password']),
            'status'   => $data['status'],
        ]);

        $user->syncRoles([$data['rol']]);
        $this->sincronizarPlantas($actor, $user, $data['plantas'] ?? []);

        return redirect()->route('usuarios.index')->with('exito', "Usuario {$user->name} creado correctamente.");
    }

    public function edit(Request $request, User $user): View|RedirectResponse
    {
        if ($error = $this->protegido($request->user(), $user, 'editar')) {
            return redirect()->route('usuarios.index')->with('aviso', $error);
        }

        return view('admin.usuarios.form', [
            'user'            => $user,
            'rolesAsignables' => $this->rolesAsignables($request->user(), $user),
            'plantas'         => Plantas::accesibles($request->user()),
            'rolActual'       => $user->roles->sortByDesc('nivel')->first()?->name,
            'plantasUsuario'  => $user->getDirectPermissions()->pluck('name')
                ->filter(fn ($p) => str_starts_with($p, 'planta.'))
                ->map(fn ($p) => substr($p, 7))->values()->all(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();

        if ($error = $this->protegido($actor, $user, 'editar')) {
            return redirect()->route('usuarios.index')->with('aviso', $error);
        }

        $data = $this->validar($request, $user);

        // Nadie se puede desactivar ni quitar su propio rol desde aquí
        if ($actor->is($user)) {
            $data['status'] = User::ESTADO_ACTIVO;
            $data['rol'] = $user->roles->sortByDesc('nivel')->first()?->name ?? $data['rol'];
        }

        $user->fill([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'telefono' => $data['telefono'] ?? null,
            'status'   => $data['status'],
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
            $user->setRememberToken(null); // invalida "Mantener sesión" en otros equipos
        }

        $user->save();
        $user->syncRoles([$data['rol']]);
        $this->sincronizarPlantas($actor, $user, $data['plantas'] ?? []);

        return redirect()->route('usuarios.index')->with('exito', "Usuario {$user->name} actualizado.");
    }

    /** Dar de baja / reactivar. */
    public function toggleEstado(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();

        if ($actor->is($user)) {
            return back()->with('aviso', 'No puedes darte de baja a ti mismo.');
        }

        if ($error = $this->protegido($actor, $user, 'dar de baja')) {
            return back()->with('aviso', $error);
        }

        $activar = ! $user->estaActivo();
        $user->status = $activar ? User::ESTADO_ACTIVO : User::ESTADO_INACTIVO;

        if (! $activar) {
            $user->setRememberToken(null); // cierra "Mantener sesión" en todos sus equipos
        }

        $user->save();

        return back()->with('exito', $activar
            ? "{$user->name} fue reactivado."
            : "{$user->name} fue dado de baja y ya no podrá ingresar.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();

        if ($actor->is($user)) {
            return back()->with('aviso', 'No puedes eliminar tu propia cuenta desde aquí.');
        }

        if ($error = $this->protegido($actor, $user, 'eliminar')) {
            return back()->with('aviso', $error);
        }

        $nombre = $user->name;
        $user->syncRoles([]);
        $user->syncPermissions([]);
        $user->delete();

        return redirect()->route('usuarios.index')->with('exito', "Usuario {$nombre} eliminado definitivamente.");
    }

    /* ------------------------------------------------------------------ */

    private function validar(Request $request, ?User $user = null): array
    {
        $roles = $this->rolesAsignables($request->user(), $user)->pluck('name')->all();

        return $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'telefono'  => ['nullable', 'string', 'max:30'],
            'password'  => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)],
            'status'    => ['required', Rule::in([User::ESTADO_ACTIVO, User::ESTADO_INACTIVO])],
            'rol'       => ['required', Rule::in($roles)],
            'plantas'   => ['array'],
            'plantas.*' => [Rule::in(Plantas::todas()->keys()->all())],
        ], [
            'rol.in'       => 'No tienes permiso para asignar ese rol.',
            'rol.required' => 'Selecciona un rol.',
        ]);
    }

    /**
     * Reglas de protección:
     * - Solo un Súper Admin puede modificar, dar de baja o eliminar a otro Súper Admin.
     * - Un usuario sin acceso total no puede tocar a usuarios de su mismo nivel o superior.
     */
    private function protegido(User $actor, User $objetivo, string $accion): ?string
    {
        if ($objetivo->esSuperAdmin() && ! $actor->esSuperAdmin()) {
            return "Por seguridad, no puedes {$accion} al Súper Administrador.";
        }

        if (! $actor->tieneAccesoTotal() && ! $actor->is($objetivo) && $objetivo->nivel() >= $actor->nivel()) {
            return "No puedes {$accion} a un usuario con un rol igual o superior al tuyo.";
        }

        return null;
    }

    /** Roles que el usuario actual puede asignar. */
    private function rolesAsignables(User $actor, ?User $objetivo = null)
    {
        $roles = Role::orderByDesc('nivel')->orderBy('name')->get();

        if ($actor->esSuperAdmin()) {
            return $roles;
        }

        if ($actor->esDueno()) {
            return $roles->where('name', '!=', User::ROL_SUPER_ADMIN)->values();
        }

        $nivel = $actor->nivel();

        return $roles->filter(fn ($r) => $r->nivel < $nivel
            // al editarse a sí mismo puede conservar su propio rol
            || ($objetivo && $actor->is($objetivo) && $objetivo->hasRole($r->name)))->values();
    }

    /** Solo se modifican las plantas a las que el propio actor tiene acceso. */
    private function sincronizarPlantas(User $actor, User $user, array $plantas): void
    {
        foreach (Plantas::accesibles($actor) as $clave => $p) {
            in_array($clave, $plantas, true)
                ? $user->givePermissionTo($p['permiso'])
                : $user->revokePermissionTo($p['permiso']);
        }
    }
}
