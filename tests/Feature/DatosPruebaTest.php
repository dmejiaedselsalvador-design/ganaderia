<?php

namespace Tests\Feature;

use App\Models\Engorda\ConsumoAlimento;
use App\Models\Ganaderia\Cliente;
use App\Models\Ganaderia\Insumo;
use App\Models\Ganado\Ganado;
use App\Models\User;
use App\Support\Simulacion;
use Database\Seeders\RolesYPermisosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatosPruebaTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_carga_y_elimina_la_simulacion_sin_tocar_datos_reales(): void
    {
        $this->seed(RolesYPermisosSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $gerente = User::factory()->create();
        $gerente->assignRole('gerente');

        // Un dato real capturado antes (catálogo): debe sobrevivir
        $real = Cliente::create(['nombre' => 'Cliente real']);
        $usuariosAntes = User::count();
        $rolesAntes = DB::table('roles')->count();

        $this->actingAs($gerente)->get(route('datos-prueba.index'))->assertForbidden();

        $this->actingAs($admin)->get(route('datos-prueba.index'))->assertOk()->assertSee('Cargar datos de prueba');
        $this->post(route('datos-prueba.cargar'))->assertRedirect(route('datos-prueba.index'))->assertSessionHas('exito');

        $this->assertTrue(Simulacion::activa());
        $this->assertSame(100, Ganado::count());
        $this->assertGreaterThan(100, ConsumoAlimento::count());
        $this->assertSame(11, Cliente::count());
        $this->assertSame($usuariosAntes + 6, User::count());
        $this->assertTrue(auth()->user()->is($admin), 'La carga no debe cambiar la sesión del administrador');

        // No se puede cargar dos veces
        $this->post(route('datos-prueba.cargar'))->assertSessionHas('aviso');

        // Las pantallas principales funcionan con datos
        foreach (['welcome', 'nutricion.planeacion', 'reportes.lotes', 'cobranza.index', 'inventario.index', 'ventas.index', 'sanidad.calendario'] as $ruta) {
            $this->actingAs($admin)->post(route('plantas.cambiar'), ['planta' => 'ganaderia']);
            $this->get(route($ruta))->assertOk()->assertSee('datos de prueba');
        }

        // Eliminar exige confirmación
        $this->delete(route('datos-prueba.eliminar'), ['confirmacion' => 'eliminar'])->assertSessionHasErrors('confirmacion');
        $this->delete(route('datos-prueba.eliminar'), ['confirmacion' => 'ELIMINAR'])->assertSessionHas('exito');

        $this->assertFalse(Simulacion::activa());
        $this->assertSame(0, Ganado::count());
        $this->assertSame(0, ConsumoAlimento::count());
        $this->assertSame(0, Insumo::withTrashed()->count());
        $this->assertSame(0, DB::table('movimientos_insumo')->count());
        $this->assertSame(0, DB::table('ventas_ganado')->count());
        // En la bitácora solo quedan los registros reales y la constancia de la eliminación
        $this->assertSame(['User', 'User', 'Cliente', 'Simulacion'], DB::table('auditoria')->orderBy('id')->pluck('modelo')->all());
        $this->assertSame(1, Cliente::count());
        $this->assertTrue(Cliente::first()->is($real));
        $this->assertSame($usuariosAntes, User::count());
        $this->assertSame($rolesAntes, DB::table('roles')->count());
        $this->assertTrue($gerente->fresh()->hasRole('gerente'));

        // Se puede volver a cargar
        $this->assertTrue(Simulacion::sePuedeCargar());
    }
}
