<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesYPermisosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PlantasYUsuariosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesYPermisosSeeder::class);
    }

    private function usuario(string $rol, array $plantas = ['ganaderia'], array $extra = []): User
    {
        $u = User::factory()->create(array_merge(['status' => 'active'], $extra));
        $u->assignRole($rol);
        foreach ($plantas as $p) {
            $u->givePermissionTo("planta.$p");
        }

        return $u->fresh();
    }

    /* ---------------- Login y plantas ---------------- */

    public function test_usuario_con_una_planta_va_directo_a_su_dashboard(): void
    {
        $u = $this->usuario('operario', ['construccion']);

        $this->post('/login', ['email' => $u->email, 'password' => 'password'])->assertRedirect(route('inicio'));
        $this->get('/')->assertRedirect(route('construccion.dashboard'));
        $this->assertSame('construccion', session('active_division'));
        $this->get(route('construccion.dashboard'))->assertOk()->assertSee('Dashboard de Obras');
    }

    public function test_usuario_con_dos_plantas_ve_el_selector_y_la_eleccion_persiste(): void
    {
        $u = $this->usuario('gerente', ['ganaderia', 'construccion']);

        $this->post('/login', ['email' => $u->email, 'password' => 'password']);
        $this->get('/')->assertRedirect(route('plantas.selector'));
        $this->get(route('plantas.selector'))->assertOk()->assertSee('¿En qué planta vas a trabajar hoy?', false);

        $this->post(route('plantas.cambiar'), ['planta' => 'ganaderia'])->assertRedirect(route('welcome'));
        $this->assertSame('ganaderia', session('active_division'));
        $this->assertSame('ganaderia', $u->fresh()->last_division);

        // Mientras dure la sesión, siempre vuelve a la planta activa
        $this->get('/')->assertRedirect(route('welcome'));
        $this->get(route('welcome'))->assertOk()->assertSee('Dashboard de Análisis');

        // Intentar entrar a la otra planta sin cambiarla -> regresa a la activa
        $this->get(route('construccion.dashboard'))->assertRedirect(route('welcome'));

        // Cambiar de planta desde el menú
        $this->post(route('plantas.cambiar'), ['planta' => 'construccion'])->assertRedirect(route('construccion.dashboard'));
        $this->get(route('construccion.dashboard'))->assertOk();
        $this->get(route('welcome'))->assertRedirect(route('construccion.dashboard'));
    }

    public function test_no_puede_activar_planta_sin_permiso(): void
    {
        $u = $this->usuario('operario', ['ganaderia']);
        $this->actingAs($u);

        $this->post(route('plantas.cambiar'), ['planta' => 'construccion']);
        $this->assertNotSame('construccion', session('active_division'));
        $this->get(route('construccion.dashboard'))->assertRedirect(route('welcome'));
    }

    public function test_usuario_sin_plantas_ve_aviso(): void
    {
        $u = $this->usuario('operario', []);
        $this->actingAs($u)->get('/')->assertOk()->assertSee('Aún no tienes una planta asignada');
    }

    public function test_cerrar_sesion_limpia_la_planta_activa(): void
    {
        $u = $this->usuario('gerente', ['ganaderia', 'construccion']);
        $this->actingAs($u)->post(route('plantas.cambiar'), ['planta' => 'ganaderia']);

        $this->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertNull(session('active_division'));
    }

    public function test_permisos_de_modulo_se_respetan(): void
    {
        $u = $this->usuario('vendedor', ['ganaderia']);
        $this->actingAs($u)->post(route('plantas.cambiar'), ['planta' => 'ganaderia']);

        $this->get(route('ventas.index'))->assertOk();
        $this->get(route('corrales.index'))->assertForbidden();
        $this->get(route('welcome'))->assertOk()->assertDontSee('Flujo financiero mensual');
    }

    public function test_dashboard_en_los_tres_periodos(): void
    {
        $u = $this->usuario('super-admin', []);
        $this->actingAs($u)->post(route('plantas.cambiar'), ['planta' => 'ganaderia']);

        foreach ([3, 6, 12] as $m) {
            $this->get(route('welcome', ['meses' => $m]))->assertOk()->assertSee('Flujo financiero mensual');
        }
    }

    /* ---------------- Recordar sesión ---------------- */

    public function test_mantener_sesion_crea_cookie_de_recuerdo_de_30_dias(): void
    {
        $u = $this->usuario('operario');

        $resp = $this->post('/login', ['email' => $u->email, 'password' => 'password', 'remember' => 'on']);

        $cookie = collect($resp->headers->getCookies())->first(fn ($c) => str_starts_with($c->getName(), 'remember_web_'));
        $this->assertNotNull($cookie, 'No se creó la cookie remember_web_*');
        $this->assertGreaterThan(now()->addDays(29)->timestamp, $cookie->getExpiresTime());
        $this->assertNotNull($u->fresh()->remember_token);
    }

    public function test_sin_mantener_sesion_no_hay_cookie_de_recuerdo(): void
    {
        $u = $this->usuario('operario');
        $resp = $this->post('/login', ['email' => $u->email, 'password' => 'password']);

        $this->assertNull(collect($resp->headers->getCookies())->first(fn ($c) => str_starts_with($c->getName(), 'remember_web_')));
    }

    public function test_usuario_dado_de_baja_es_expulsado(): void
    {
        $u = $this->usuario('operario');
        $this->actingAs($u)->get('/')->assertRedirect();

        $u->update(['status' => 'inactive']);
        $this->get(route('welcome'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /* ---------------- Usuarios y roles ---------------- */

    public function test_dueno_no_puede_eliminar_ni_dar_de_baja_al_super_admin(): void
    {
        $super = $this->usuario('super-admin', ['ganaderia']);
        $dueno = $this->usuario('dueno', ['ganaderia']);
        $this->actingAs($dueno);

        $this->delete(route('usuarios.destroy', $super))->assertSessionHas('aviso');
        $this->patch(route('usuarios.estado', $super))->assertSessionHas('aviso');
        $this->get(route('usuarios.edit', $super))->assertRedirect(route('usuarios.index'));

        $this->assertDatabaseHas('users', ['id' => $super->id, 'status' => 'active']);
    }

    public function test_dueno_puede_crear_editar_dar_de_baja_y_eliminar_usuarios(): void
    {
        $dueno = $this->usuario('dueno', ['ganaderia', 'construccion']);
        $this->actingAs($dueno);

        $this->get(route('usuarios.index'))->assertOk();
        $this->get(route('usuarios.create'))->assertOk()->assertDontSee('value="super-admin"', false);

        $this->post(route('usuarios.store'), [
            'name' => 'Pedro Obra', 'email' => 'pedro@test.com', 'password' => 'Secreto2026x',
            'password_confirmation' => 'Secreto2026x', 'status' => 'active', 'rol' => 'supervisor',
            'plantas' => ['construccion'],
        ])->assertRedirect(route('usuarios.index'));

        $pedro = User::whereEmail('pedro@test.com')->first();
        $this->assertTrue($pedro->hasRole('supervisor'));
        $this->assertTrue($pedro->can('planta.construccion'));
        $this->assertFalse($pedro->can('planta.ganaderia'));

        $this->put(route('usuarios.update', $pedro), [
            'name' => 'Pedro Obra', 'email' => 'pedro@test.com', 'status' => 'active', 'rol' => 'comprador',
            'plantas' => ['construccion', 'ganaderia'],
        ])->assertRedirect(route('usuarios.index'));
        $this->assertTrue($pedro->fresh()->hasRole('comprador'));
        $this->assertTrue($pedro->fresh()->can('planta.ganaderia'));

        $this->patch(route('usuarios.estado', $pedro));
        $this->assertSame('inactive', $pedro->fresh()->status);

        $this->delete(route('usuarios.destroy', $pedro));
        $this->assertDatabaseMissing('users', ['id' => $pedro->id]);
    }

    public function test_dueno_no_puede_asignar_super_admin(): void
    {
        $dueno = $this->usuario('dueno');
        $this->actingAs($dueno)->post(route('usuarios.store'), [
            'name' => 'X', 'email' => 'x@test.com', 'password' => 'Secreto2026x', 'password_confirmation' => 'Secreto2026x',
            'status' => 'active', 'rol' => 'super-admin',
        ])->assertSessionHasErrors('rol');
    }

    public function test_gerente_no_puede_asignar_roles_superiores_ni_gestionar_roles(): void
    {
        $gerente = $this->usuario('gerente');
        $this->actingAs($gerente);

        $this->post(route('usuarios.store'), [
            'name' => 'X', 'email' => 'x@test.com', 'password' => 'Secreto2026x', 'password_confirmation' => 'Secreto2026x',
            'status' => 'active', 'rol' => 'dueno',
        ])->assertSessionHasErrors('rol');

        $this->get(route('roles.index'))->assertForbidden();
        $this->delete(route('usuarios.destroy', $this->usuario('operario')))->assertForbidden();
    }

    public function test_operario_no_entra_a_usuarios(): void
    {
        $this->actingAs($this->usuario('operario'))->get(route('usuarios.index'))->assertForbidden();
    }

    public function test_crear_y_eliminar_rol_personalizado(): void
    {
        $this->actingAs($this->usuario('dueno'));

        $this->get(route('roles.index'))->assertOk();
        $this->post(route('roles.store'), [
            'display_name' => 'Bodeguero', 'description' => 'Control de bodega', 'nivel' => 15,
            'permisos' => ['construccion.materiales'],
        ])->assertRedirect(route('roles.index'));

        $rol = \Spatie\Permission\Models\Role::where('name', 'bodeguero')->first();
        $this->assertNotNull($rol);
        $this->assertTrue($rol->hasPermissionTo('construccion.materiales'));

        $this->delete(route('roles.destroy', $rol))->assertRedirect(route('roles.index'));
        $this->assertNull(\Spatie\Permission\Models\Role::where('name', 'bodeguero')->first());

        $super = \Spatie\Permission\Models\Role::where('name', 'super-admin')->first();
        $this->delete(route('roles.destroy', $super))->assertSessionHas('aviso');
    }

    public function test_tema_se_guarda_en_el_usuario(): void
    {
        $u = $this->usuario('operario');
        $this->actingAs($u)->postJson(route('tema.cambiar'), ['tema' => 'metal'])->assertOk();
        $this->assertSame('metal', $u->fresh()->tema);

        $this->post(route('plantas.cambiar'), ['planta' => 'ganaderia']);
        $this->get(route('welcome'))->assertSee('tema-metal', false);
    }

    public function test_seeder_migra_roles_antiguos(): void
    {
        $viejo = User::factory()->create();
        \Spatie\Permission\Models\Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $viejo->assignRole('admin');
        $viejo->revokePermissionTo('planta.ganaderia');

        $this->seed(RolesYPermisosSeeder::class);

        $this->assertTrue($viejo->fresh()->hasRole('super-admin'));
        $this->assertNull(\Spatie\Permission\Models\Role::where('name', 'admin')->first());
        $this->assertTrue($viejo->fresh()->can('planta.ganaderia'));
    }

    /* ---------------- Clave temporal ---------------- */

    public function test_usuario_nuevo_recibe_clave_temporal_y_debe_cambiarla(): void
    {
        $this->actingAs($this->usuario('dueno', ['ganaderia']));

        $resp = $this->post(route('usuarios.store'), [
            'name' => 'Ana Nueva', 'email' => 'ana@test.com', 'status' => 'active', 'rol' => 'operario',
            'plantas' => ['ganaderia'],
        ])->assertRedirect(route('usuarios.index'));

        $ct = session('clave_temporal');
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}$/', $ct['clave']);
        $ana = User::whereEmail('ana@test.com')->first();
        $this->assertTrue($ana->debeCambiarClave());
        $this->assertNotSame($ct['clave'], $ana->password, 'La clave debe guardarse hasheada');

        // Primer ingreso con la clave temporal
        $this->post('/logout');
        $this->post('/login', ['email' => 'ana@test.com', 'password' => $ct['clave']]);
        $this->assertAuthenticated();

        // Cualquier pantalla la manda a cambiar la clave
        $this->get('/')->assertRedirect(route('password.cambiar'));
        $this->get(route('welcome'))->assertRedirect(route('password.cambiar'));
        $this->get(route('password.cambiar'))->assertOk()->assertSee('Crea tu clave personal');

        // Validaciones: clave temporal incorrecta, clave débil, igual a la temporal
        $this->put(route('password.cambiar.guardar'), ['current_password' => 'mala', 'password' => 'NuevaClave2026', 'password_confirmation' => 'NuevaClave2026'])
            ->assertSessionHasErrors('current_password');
        $this->put(route('password.cambiar.guardar'), ['current_password' => $ct['clave'], 'password' => 'debil', 'password_confirmation' => 'debil'])
            ->assertSessionHasErrors('password');
        $this->put(route('password.cambiar.guardar'), ['current_password' => $ct['clave'], 'password' => $ct['clave'], 'password_confirmation' => $ct['clave']])
            ->assertSessionHasErrors('password');

        // Cambio correcto
        $this->put(route('password.cambiar.guardar'), ['current_password' => $ct['clave'], 'password' => 'NuevaClave2026', 'password_confirmation' => 'NuevaClave2026'])
            ->assertRedirect(route('inicio'));
        $ana->refresh();
        $this->assertFalse($ana->debeCambiarClave());
        $this->assertNotNull($ana->password_changed_at);
        $this->get('/')->assertRedirect(route('welcome'));

        // La clave temporal ya no sirve
        $this->post('/logout');
        $this->post('/login', ['email' => 'ana@test.com', 'password' => $ct['clave']]);
        $this->assertGuest();
    }

    public function test_admin_genera_nueva_clave_temporal(): void
    {
        $op = $this->usuario('operario');
        $this->actingAs($this->usuario('dueno'));

        $this->post(route('usuarios.clave-temporal', $op))->assertSessionHas('clave_temporal');
        $this->assertTrue($op->fresh()->debeCambiarClave());
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check(session('clave_temporal')['clave'], $op->fresh()->password));
    }

    public function test_dueno_no_puede_cambiar_clave_del_super_admin(): void
    {
        $super = $this->usuario('super-admin');
        $this->actingAs($this->usuario('dueno'));

        $this->post(route('usuarios.clave-temporal', $super))->assertSessionHas('aviso');
        $this->assertFalse($super->fresh()->debeCambiarClave());
    }

    public function test_cambio_de_clave_desde_perfil(): void
    {
        $u = $this->usuario('operario');
        $this->actingAs($u)->put(route('password.update'), [
            'current_password' => 'password', 'password' => 'OtraClave2026', 'password_confirmation' => 'OtraClave2026',
        ])->assertSessionHasNoErrors();
        $this->assertNotNull($u->fresh()->password_changed_at);
    }

    /* ---------------- Seeders de producción ---------------- */

    public function test_seeder_de_produccion_no_carga_datos_de_prueba(): void
    {
        config(['auth.admin_inicial.email' => 'jefe@empresa.com', 'auth.admin_inicial.name' => 'Jefe']);
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $jefe = User::whereEmail('jefe@empresa.com')->first();
        $this->assertTrue($jefe->hasRole('super-admin'));
        $this->assertTrue($jefe->debeCambiarClave());
        $this->assertSame(0, \Illuminate\Support\Facades\DB::table('proveedorGanado')->count());
        $this->assertSame(0, \App\Models\Ganado\Ganado::count());
        $this->assertNull(User::whereEmail('luis@test.com')->first());
    }
}
