<?php

namespace Tests\Feature;

use App\Models\Correccion;
use App\Models\Engorda\ConsumoAlimento;
use App\Models\Engorda\CorralLote;
use App\Models\Engorda\RacionAlimento;
use App\Models\Ganaderia\Insumo;
use App\Models\Ganado\FacturaGanado;
use App\Models\Ganado\Ganado;
use App\Models\Ganado\PesajeGanado;
use App\Models\Ganado\ProveedorGanado;
use App\Models\Sanidad\TratamientoSanitario;
use App\Models\User;
use App\Models\Venta\VentaGanado;
use App\Services\MortalidadService;
use App\Services\PesajeService;
use App\Services\VentaService;
use Database\Seeders\RolesYPermisosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorreccionesTest extends TestCase
{
    use RefreshDatabase;

    private User $gerente;
    private CorralLote $corral;
    private Ganado $g;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesYPermisosSeeder::class);
        $this->gerente = User::factory()->create();
        $this->gerente->assignRole('gerente');
        $this->gerente->givePermissionTo('planta.ganaderia');

        $prov = ProveedorGanado::create(['nombreProveedor' => 'Rancho']);
        $f = FacturaGanado::create(['proveedorID' => $prov->id, 'fechaFactura' => now()->subDays(40), 'numeroFactura' => 'G-1', 'estado' => 'pendiente']);
        $this->corral = CorralLote::create(['codigo' => 'C1', 'nombre' => 'C1', 'fase_engorda' => 'Crecimiento', 'capacidad_maxima' => 30, 'estado' => 'Activo']);
        $this->g = Ganado::create(['facturaID' => $f->id, 'areteID' => 'A-100', 'categoria' => 'Torete', 'pesoActual' => 300, 'ultimoPeso' => 300,
            'precioCompra' => 60, 'fechaCompra' => now()->subDays(40)->toDateString(), 'status' => 'Activo', 'corral_lote_id' => $this->corral->id]);

        $this->actingAs($this->gerente)->post(route('plantas.cambiar'), ['planta' => 'ganaderia']);
    }

    public function test_solo_quien_tiene_permiso_corrige(): void
    {
        $this->get(route('correcciones.index', ['arete' => 'A-100']))->assertOk()->assertSee('A-100')->assertSee('Datos de compra');

        $op = User::factory()->create();
        $op->assignRole('operario');
        $op->givePermissionTo('planta.ganaderia');
        $this->actingAs($op)->post(route('plantas.cambiar'), ['planta' => 'ganaderia']);
        $this->get(route('correcciones.index'))->assertForbidden();
    }

    public function test_corregir_y_anular_pesaje_recalcula_ganancia(): void
    {
        $svc = app(PesajeService::class);
        $p1 = $svc->registrarPesaje($this->g->GanadoID, 330, now()->subDays(20)->toDateString());
        $p2 = $svc->registrarPesaje($this->g->GanadoID, 350, now()->subDays(10)->toDateString());

        // Sin motivo no se puede
        $this->put(route('correcciones.pesaje', $p1), ['peso' => 320, 'fecha' => $p1->fecha_pesaje->toDateString()])->assertSessionHasErrors('motivo');

        $this->put(route('correcciones.pesaje', $p1), ['peso' => 320, 'fecha' => $p1->fecha_pesaje->toDateString(), 'motivo' => 'Se capturó 330 en vez de 320'])
            ->assertSessionHas('success');
        $this->assertEquals(1.0, (float) $p1->fresh()->gdp);          // (320-300)/20
        $this->assertEquals(3.0, (float) $p2->fresh()->gdp);          // (350-320)/10

        $this->put(route('correcciones.pesaje', $p2), ['anular' => 1, 'motivo' => 'Pesaje de otro animal'])->assertSessionHas('success');
        $this->assertNull(PesajeGanado::find($p2->id));
        $this->assertEquals(320, (float) $this->g->fresh()->ultimoPeso);
        $this->assertSame(2, Correccion::where('tipo', 'pesaje')->count());
        $this->assertEquals(330, Correccion::where('tipo', 'pesaje')->first()->antes['peso']);
    }

    public function test_corregir_y_anular_servido_ajusta_bodega(): void
    {
        $maiz = Insumo::create(['codigo' => 'M', 'nombre' => 'Maíz', 'tipo' => 'alimento', 'unidad' => 'kg', 'existencia' => 1000, 'costo_promedio' => 6]);
        $r = RacionAlimento::create(['codigo' => 'R', 'nombre' => 'R', 'fase' => 'Crecimiento', 'costo_por_kg' => 6, 'activo' => true]);
        $r->ingredientes()->create(['insumo_id' => $maiz->id, 'porcentaje' => 100]);

        $this->post(route('nutricion.consumo.store'), ['corral_lote_id' => $this->corral->id, 'racion_id' => $r->id, 'fecha' => today()->toDateString(), 'kilos_servidos' => 500]);
        $this->assertEquals(500, (float) $maiz->fresh()->existencia);
        $c = ConsumoAlimento::first();

        // Eran 50 kg, no 500
        $this->put(route('correcciones.servido', $c), ['fecha' => today()->toDateString(), 'racion_id' => $r->id, 'kilos_servidos' => 50, 'kilos_rechazo' => 0,
            'motivo' => 'Se capturó un cero de más'])->assertSessionHas('success');
        $this->assertEquals(950, (float) $maiz->fresh()->existencia);
        $this->assertEquals(300, (float) $c->fresh()->costo_total_mxn);

        // Corregir otra vez no duplica devoluciones
        $this->put(route('correcciones.servido', $c), ['fecha' => today()->toDateString(), 'racion_id' => $r->id, 'kilos_servidos' => 60, 'motivo' => 'Eran 60 kg'])->assertSessionHas('success');
        $this->assertEquals(940, (float) $maiz->fresh()->existencia);

        $this->put(route('correcciones.servido', $c), ['anular' => 1, 'motivo' => 'Servido duplicado'])->assertSessionHas('success');
        $this->assertEquals(1000, (float) $maiz->fresh()->existencia);
        $this->assertSame(0, ConsumoAlimento::count());
    }

    public function test_tratamiento_baja_venta_y_compra(): void
    {
        $med = Insumo::create(['codigo' => 'IV', 'nombre' => 'Ivermectina', 'tipo' => 'medicamento', 'unidad' => 'ml', 'existencia' => 100, 'costo_promedio' => 2]);
        $tr = TratamientoSanitario::create(['ganado_id' => $this->g->GanadoID, 'insumo_id' => $med->id, 'cantidad_insumo' => 10, 'tipo_evento' => 'Desparasitacion',
            'medicamento_producto' => 'Ivermectina', 'dosis_aplicada' => '10 ml', 'fecha_aplicacion' => today()->subDay(), 'dias_retiro_rastro' => 35]);
        app(\App\Services\Ganaderia\InventarioService::class)->salida($med, 10, ['origen_type' => 'TratamientoSanitario', 'origen_id' => $tr->id]);
        $this->put(route('correcciones.tratamiento', $tr), ['fecha' => today()->subDay()->toDateString(), 'cantidad_insumo' => 6, 'dias_retiro_rastro' => 35, 'motivo' => 'Eran 6 ml'])
            ->assertSessionHas('success');
        $this->assertEquals(94, (float) $med->fresh()->existencia);

        // Baja por error y se revierte
        $baja = app(MortalidadService::class)->registrarBaja($this->g->GanadoID, today()->toDateString(), 'Neumonía');
        $this->put(route('correcciones.baja', $baja), ['motivo' => 'Se dio de baja el arete equivocado'])->assertSessionHas('success');
        $this->assertSame('Activo', $this->g->fresh()->status);

        // Venta y anulación
        $venta = app(VentaService::class)->registrarVenta(['cliente_nombre' => 'X', 'fecha_venta' => today()->toDateString()],
            [['ganado_id' => $this->g->GanadoID, 'peso_venta' => 420, 'precio_unitario' => 55]]);
        $this->assertSame('Vendido', $this->g->fresh()->status);
        $this->put(route('correcciones.venta', $venta), ['motivo' => 'El cliente canceló'])->assertSessionHas('success');
        $this->assertSame(VentaGanado::ESTADO_ANULADA, $venta->fresh()->estado);
        $this->assertSame('Activo', $this->g->fresh()->status);
        $this->assertEquals(0, (float) $venta->fresh()->saldo_pendiente);

        // Compra
        $this->put(route('correcciones.compra', $this->g->GanadoID), ['peso' => 310, 'precio' => 62, 'fecha' => $this->g->fechaCompra->toDateString(),
            'categoria' => 'Torete', 'motivo' => 'Peso de báscula de llegada mal capturado'])->assertSessionHas('success');
        $this->assertEquals(310, (float) $this->g->fresh()->pesoActual);

        $this->get(route('correcciones.index', ['arete' => 'A-100']))->assertOk()->assertSee('El cliente canceló');
    }
}
