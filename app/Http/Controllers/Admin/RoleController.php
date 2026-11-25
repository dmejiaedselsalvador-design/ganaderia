<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Permisos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::withCount('users')->with('permissions')
            ->orderByDesc('nivel')->orderBy('name')->get();

        return view('admin.roles.index', [
            'roles'       => $roles,
            'sistema'     => Permisos::ROLES_SISTEMA,
            'totalPermisos' => count(Permisos::todos()) - count(Permisos::GRUPOS['Acceso a plantas']),
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.form', [
            'role'     => new Role(['nivel' => 10]),
            'grupos'   => $this->gruposEditables(),
            'marcados' => [],
            'sistema'  => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validar($request);

        $role = Role::create([
            'name'         => $data['name'],
            'guard_name'   => 'web',
            'display_name' => $data['display_name'],
            'description'  => $data['description'] ?? null,
            'nivel'        => $data['nivel'],
        ]);

        $role->syncPermissions($data['permisos'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('exito', "Rol «{$role->display_name}» creado.");
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.form', [
            'role'     => $role,
            'grupos'   => $this->gruposEditables(),
            'marcados' => $role->permissions->pluck('name')->all(),
            'sistema'  => in_array($role->name, Permisos::ROLES_SISTEMA, true),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if (in_array($role->name, Permisos::ROLES_SISTEMA, true)) {
            // Súper Admin y Dueño: solo se edita la descripción; tienen todos los permisos.
            $role->update(['description' => $request->string('description')->limit(500)->toString()]);

            return redirect()->route('roles.index')->with('exito', 'Descripción actualizada. Este rol siempre tiene todos los permisos.');
        }

        $data = $this->validar($request, $role);

        $role->update([
            'name'         => $data['name'],
            'display_name' => $data['display_name'],
            'description'  => $data['description'] ?? null,
            'nivel'        => $data['nivel'],
        ]);

        $role->syncPermissions($data['permisos'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('exito', "Rol «{$role->display_name}» actualizado.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, Permisos::ROLES_SISTEMA, true)) {
            return back()->with('aviso', 'Este rol es del sistema y no se puede eliminar.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('aviso', "El rol «{$role->display_name}» tiene usuarios asignados. Cámbialos de rol antes de eliminarlo.");
        }

        $nombre = $role->display_name ?? $role->name;
        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('exito', "Rol «{$nombre}» eliminado.");
    }

    /* ------------------------------------------------------------------ */

    private function validar(Request $request, ?Role $role = null): array
    {
        $request->merge([
            'name' => Str::slug($request->input('name') ?: $request->input('display_name')),
        ]);

        $maxNivel = $request->user()->tieneAccesoTotal() ? 89 : max(1, $request->user()->nivel() - 1);

        return $request->validate([
            'display_name' => ['required', 'string', 'max:80'],
            'name'         => ['required', 'string', 'max:80', Rule::notIn(Permisos::ROLES_SISTEMA),
                               Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role?->id)],
            'description'  => ['nullable', 'string', 'max:500'],
            'nivel'        => ['required', 'integer', 'min:1', "max:{$maxNivel}"],
            'permisos'     => ['array'],
            'permisos.*'   => [Rule::in(array_keys($this->permisosEditables()))],
        ], [
            'name.unique' => 'Ya existe un rol con ese identificador.',
            'nivel.max'   => "El nivel máximo que puedes asignar es {$maxNivel}.",
        ]);
    }

    /** Los permisos de acceso a plantas se asignan por usuario, no por rol. */
    private function gruposEditables(): array
    {
        return array_diff_key(Permisos::GRUPOS, ['Acceso a plantas' => true]);
    }

    private function permisosEditables(): array
    {
        return array_merge(...array_values($this->gruposEditables()));
    }
}
