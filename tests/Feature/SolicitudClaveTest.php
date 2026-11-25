<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\SolicitudClaveController as C;
use App\Models\SolicitudClave;
use App\Models\User;
use Database\Seeders\RolesYPermisosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SolicitudClaveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesYPermisosSeeder::class);
    }

    private function usuario(string $rol, array $extra = []): User
    {
        $u = User::factory()->create($extra);
        $u->assignRole($rol);
        $u->givePermissionTo('planta.ganaderia');

        return $u->fresh();
    }

    /** Envía la solicitud y devuelve el token de la cookie del navegador. */
    private function solicitar(string $email): ?string
    {
        $resp = $this->post('/forgot-password', ['email' => $email]);
        $cookie = collect($resp->headers->getCookies())->first(fn ($c) => $c->getName() === C::COOKIE);

        return $cookie ? decrypt($cookie->getValue(), false) : null;
    }

    private function estadoCon(?string $tokenCifrado)
    {
        $token = str_contains((string) $tokenCifrado, '|') ? explode('|', $tokenCifrado, 2)[1] : $tokenCifrado;

        return $this->withCookie(C::COOKIE, $token)->get(route('password.solicitud.estado'));
    }

    public function test_flujo_completo_aprobado(): void
    {
        $op = $this->usuario('operario');
        $super = $this->usuario('super-admin');

        $token = $this->solicitar($op->email);
        $this->assertNotNull($token);
        $solicitud = SolicitudClave::first();
        $this->assertSame(SolicitudClave::PENDIENTE, $solicitud->estado);

        // El usuario ve "en revisión"
        $this->estadoCon($token)->assertOk()->assertSee('Solicitud en revisión')->assertSee($solicitud->folio());

        // El super admin ve la notificación y aprueba
        $this->actingAs($super)->get(route('usuarios.index'))->assertSee('solicitud de cambio de clave');
        $this->post(route('solicitudes.aprobar', $solicitud))->assertSessionHas('exito');
        $this->post('/logout');
        $this->assertTrue($op->fresh()->debeCambiarClave());

        // El usuario ve su clave UNA sola vez
        $html = $this->estadoCon($token)->assertOk()->assertSee('¡Solicitud aprobada!', false)->getContent();
        preg_match('/<code id="clave"[^>]*>([^<]+)</', $html, $m);
        $clave = trim($m[1]);
        $this->assertTrue(Hash::check($clave, $op->fresh()->password));
        $this->assertNull(SolicitudClave::first()->clave_cifrada);
        $this->estadoCon($token)->assertSee('Clave ya entregada')->assertDontSee($clave);

        // Ingresa y debe cambiarla
        $this->post('/login', ['email' => $op->email, 'password' => $clave]);
        $this->get('/')->assertRedirect(route('password.cambiar'));
    }

    public function test_otro_navegador_no_puede_ver_la_clave(): void
    {
        $op = $this->usuario('operario');
        $this->solicitar($op->email);
        $this->actingAs($this->usuario('super-admin'))->post(route('solicitudes.aprobar', SolicitudClave::first()));
        $this->post('/logout');

        $this->estadoCon('token-falso')->assertSee('Sin solicitudes');
        $this->assertNotNull(SolicitudClave::first()->clave_cifrada);
    }

    public function test_denegar_y_bloquear_impide_nuevas_solicitudes(): void
    {
        $op = $this->usuario('operario');
        $token = $this->solicitar($op->email);

        $this->actingAs($this->usuario('super-admin'))
            ->post(route('solicitudes.denegar', SolicitudClave::first()), ['motivo' => 'No reconocida', 'bloquear' => 1]);
        $this->post('/logout');

        $this->assertTrue($op->fresh()->bloqueo_solicitud_clave);
        $this->assertFalse($op->fresh()->debeCambiarClave());
        $this->estadoCon($token)->assertSee('Solicitud denegada')->assertSee('No reconocida');

        $this->post('/forgot-password', ['email' => $op->email])->assertSessionHasErrors('email');
        $this->assertSame(1, SolicitudClave::count());
    }

    public function test_usuario_inactivo_o_inexistente_no_puede_solicitar(): void
    {
        $inactivo = $this->usuario('operario', ['status' => 'inactive']);
        $this->post('/forgot-password', ['email' => $inactivo->email])->assertSessionHasErrors('email');
        $this->post('/forgot-password', ['email' => 'nadie@test.com'])->assertSessionHasErrors('email');
        $this->assertSame(0, SolicitudClave::count());
    }

    public function test_no_se_aprueba_si_el_usuario_fue_dado_de_baja(): void
    {
        $op = $this->usuario('operario');
        $this->solicitar($op->email);
        $op->update(['status' => 'inactive']);

        $this->actingAs($this->usuario('super-admin'))
            ->post(route('solicitudes.aprobar', SolicitudClave::first()))->assertSessionHas('aviso');
        $this->assertSame(SolicitudClave::PENDIENTE, SolicitudClave::first()->estado);
    }

    public function test_dueno_no_atiende_solicitudes_del_super_admin_y_operario_no_entra(): void
    {
        $super = $this->usuario('super-admin');
        $this->solicitar($super->email);

        $this->actingAs($this->usuario('dueno'))
            ->post(route('solicitudes.aprobar', SolicitudClave::first()))->assertSessionHas('aviso');

        $this->actingAs($this->usuario('operario'))->get(route('solicitudes.index'))->assertForbidden();
    }

    public function test_solo_una_solicitud_pendiente_por_usuario(): void
    {
        $op = $this->usuario('operario');
        $this->solicitar($op->email);
        $this->solicitar($op->email);
        $this->assertSame(1, SolicitudClave::count());
    }

    public function test_desbloquear_permite_solicitar_de_nuevo(): void
    {
        $op = $this->usuario('operario', ['bloqueo_solicitud_clave' => true]);
        $this->actingAs($this->usuario('super-admin'))->patch(route('solicitudes.bloqueo', $op));
        $this->post('/logout');

        $this->assertFalse($op->fresh()->bloqueo_solicitud_clave);
        $this->assertNotNull($this->solicitar($op->email));
    }
}
