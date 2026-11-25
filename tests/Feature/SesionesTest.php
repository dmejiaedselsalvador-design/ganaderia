<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Sesiones;
use Database\Seeders\RolesYPermisosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SesionesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'database']);
        $this->seed(RolesYPermisosSeeder::class);
    }

    private function usuario(string $rol): User
    {
        $u = User::factory()->create();
        $u->assignRole($rol);

        return $u;
    }

    private function sesion(User $u, string $id, string $ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0) Mobile Safari', int $haceMin = 1): void
    {
        DB::table('sessions')->insert(['id' => $id, 'user_id' => $u->id, 'ip_address' => '10.0.0.5', 'user_agent' => $ua,
            'payload' => base64_encode(serialize(['active_division' => 'ganaderia'])), 'last_activity' => now()->subMinutes($haceMin)->getTimestamp()]);
    }

    public function test_dueno_ve_y_expulsa_pero_no_al_super_admin(): void
    {
        $dueno = $this->usuario('dueno');
        $op = $this->usuario('operario');
        $admin = $this->usuario('super-admin');
        $op->forceFill(['remember_token' => 'token-viejo'])->save();
        $this->sesion($op, 'cel-operario');
        $this->sesion($op, 'pc-operario', 'Mozilla/5.0 (Windows NT 10.0) Chrome/120', 30);
        $this->sesion($admin, 'pc-admin', 'Mozilla/5.0 (Windows NT 10.0) Chrome/120');

        $this->actingAs($dueno)->get(route('sesiones.index'))->assertOk()
            ->assertSee($op->name)->assertSee('Safari · iPhone')->assertSee('Chrome · Windows')->assertSee('Protegido');

        $this->delete(route('sesiones.expulsar', 'cel-operario'))->assertSessionHas('exito');
        $this->assertDatabaseMissing('sessions', ['id' => 'cel-operario']);
        $this->assertDatabaseHas('sessions', ['id' => 'pc-operario']);
        $this->assertNotSame('token-viejo', $op->fresh()->remember_token, 'Debe anular el «Mantener sesión»');

        $this->delete(route('sesiones.expulsar', 'pc-admin'))->assertSessionHas('aviso');
        $this->assertDatabaseHas('sessions', ['id' => 'pc-admin']);

        $this->delete(route('sesiones.usuario', $op))->assertSessionHas('exito');
        $this->assertSame(0, DB::table('sessions')->where('user_id', $op->id)->count());
    }

    public function test_solo_super_admin_y_dueno_entran(): void
    {
        $this->actingAs($this->usuario('gerente'))->get(route('sesiones.index'))->assertForbidden();
        $this->actingAs($this->usuario('super-admin'))->get(route('sesiones.index'))->assertOk();
    }

    public function test_clave_temporal_y_baja_cierran_sus_sesiones(): void
    {
        $admin = $this->usuario('super-admin');
        $op = $this->usuario('operario');
        $this->sesion($op, 'cel-1');
        $op->asignarClaveTemporal('Temporal123*');
        $this->assertSame(0, DB::table('sessions')->where('user_id', $op->id)->count());

        $this->sesion($op, 'cel-2');
        $this->actingAs($admin)->patch(route('usuarios.estado', $op));
        $this->assertSame(0, DB::table('sessions')->where('user_id', $op->id)->count());
    }

    public function test_usuario_cierra_sus_otros_equipos(): void
    {
        $op = $this->usuario('operario');
        $this->sesion($op, 'otro-equipo');
        $this->actingAs($op)->delete(route('profile.sesiones'))->assertSessionHas('exito');
        $this->assertDatabaseMissing('sessions', ['id' => 'otro-equipo']);
        $this->assertSame('Safari · iPhone', Sesiones::dispositivo('Mozilla/5.0 (iPhone) Safari')['texto']);
    }
}
