<?php

namespace Tests\Feature;

use App\Models\Ganaderia\PesoObjetivo;
use App\Models\Ganado\FacturaGanado;
use App\Models\Ganado\Ganado;
use App\Models\Ganado\ProveedorGanado;
use App\Models\User;
use App\Services\LiquidacionService;
use App\Services\PesajeService;
use Database\Seeders\RolesYPermisosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuiasYPesoObjetivoTest extends TestCase
{
    use RefreshDatabase;

    private User $jefe;
    private ProveedorGanado $prov;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesYPermisosSeeder::class);
        $this->jefe = User::factory()->create();
        $this->jefe->assignRole('gerente');
        $this->jefe->givePermissionTo('planta.ganaderia');
        $this->prov = ProveedorGanado::create(['nombreProveedor' => 'Rancho Las Palmas', 'nombreContacto' => 'José']);
        $this->actingAs($this->jefe)->post(route('plantas.cambiar'), ['planta' => 'ganaderia']);
    }

    private function guia(string $num, string $estado, string $fecha): FacturaGanado
    {
        return FacturaGanado::create(['proveedorID' => $this->prov->id, 'fechaFactura' => $fecha, 'numeroFactura' => $num, 'estado' => $estado]);
    }

    public function test_filtros_de_guias_y_cambio_de_numero(): void
    {
        $g1 = $this->guia('G-100', 'pendiente', now()->subDays(5)->toDateString());
        $this->guia('G-200', 'liquidado', now()->subDays(40)->toDateString());
        $this->guia('G-300', 'liquidado', now()->subDays(2)->toDateString());

        $this->get(route('proveedores.facturas.index'))->assertOk()->assertSee('G-100')->assertDontSee('G-200');
        $this->get(route('proveedores.facturas.index', ['estado' => 'liquidadas']))->assertSee('G-200')->assertSee('G-300')->assertDontSee('G-100');
        $this->get(route('proveedores.facturas.index', ['estado' => 'liquidadas', 'desde' => now()->subDays(10)->toDateString()]))
            ->assertSee('G-300')->assertDontSee('G-200');
        $this->get(route('proveedores.facturas.index', ['estado' => 'todas', 'q' => 'Palmas']))->assertSee('G-100')->assertSee('G-300');

        // El número es solo texto: el ganado sigue ligado por id
        $animal = Ganado::create(['facturaID' => $g1->id, 'areteID' => 'A-1', 'categoria' => 'Torete', 'pesoActual' => 300, 'ultimoPeso' => 300,
            'precioCompra' => 60, 'fechaCompra' => now()->subDays(5)->toDateString(), 'status' => 'Activo']);
        $this->put(route('proveedores.facturas.numero', $g1->id), ['numero' => 'REEMO-26-445'])->assertSessionHas('success');
        $this->assertSame('REEMO-26-445', $g1->fresh()->numeroFactura);
        $this->assertSame($g1->id, $animal->fresh()->facturaID);
        $this->put(route('proveedores.facturas.numero', $g1->id), ['numero' => 'G-200'])->assertSessionHasErrors('numero');
    }

    public function test_monto_de_compra_no_cambia_con_los_pesajes(): void
    {
        $g = $this->guia('G-1', 'pendiente', now()->subDays(30)->toDateString());
        $a = Ganado::create(['facturaID' => $g->id, 'areteID' => 'A-9', 'categoria' => 'Torete', 'pesoActual' => 300, 'ultimoPeso' => 300,
            'precioCompra' => 60, 'fechaCompra' => now()->subDays(30)->toDateString(), 'status' => 'Activo']);
        app(PesajeService::class)->registrarPesaje($a->GanadoID, 345, now()->toDateString());

        $this->assertEquals(345, (float) $a->fresh()->ultimoPeso);
        $this->assertEquals(18000, $a->fresh()->precioGanadoTotal);                 // 300 kg × $60
        $this->assertEquals(18000, app(LiquidacionService::class)->calcularResumenFactura($g->id)['totalGanado']);
        $this->get(route('proveedores.facturas.index'))->assertSee('18,000.00');
    }

    public function test_peso_objetivo_por_categoria_configurable(): void
    {
        $g = $this->guia('G-2', 'pendiente', now()->toDateString());
        $a = Ganado::create(['facturaID' => $g->id, 'areteID' => 'B-1', 'categoria' => 'Becerro', 'pesoActual' => 230, 'ultimoPeso' => 230,
            'precioCompra' => 70, 'fechaCompra' => now()->toDateString(), 'status' => 'Activo']);
        $this->assertTrue($a->esta_listo_venta);   // de fábrica: 220

        $pesos = ['Becerro' => 260, 'Torete' => 450, 'Toro' => 500, 'Becerra' => 240, 'Vaquilla' => 380, 'Vaca' => 420];
        $this->put(route('ganado.pesos-objetivo'), ['pesos' => $pesos])->assertSessionHas('success');
        PesoObjetivo::limpiarCache();

        $this->assertEquals(260, $a->fresh()->peso_objetivo_calculado);
        $this->assertFalse($a->fresh()->esta_listo_venta);
        $this->assertSame(0, Ganado::listoParaVenta()->count());

        // Un peso propio en la ficha manda sobre el de la categoría
        $a->update(['peso_objetivo' => 225]);
        $this->assertEquals(225, $a->fresh()->peso_objetivo_calculado);

        $this->put(route('ganado.pesos-objetivo'), ['pesos' => ['Becerro' => 10] + $pesos])->assertSessionHasErrors('pesos.Becerro');
        $this->get(route('compras.ganado.index'))->assertOk()->assertSee('Peso objetivo');
    }

    public function test_liquidacion_muestra_promedios_y_no_se_liquida_dos_veces(): void
    {
        $g = $this->guia('G-L', 'pendiente', now()->subDays(3)->toDateString());
        foreach ([[200, 90], [300, 80]] as $i => [$kg, $p]) {
            Ganado::create(['facturaID' => $g->id, 'areteID' => "T-{$i}", 'categoria' => 'Torete', 'pesoActual' => $kg, 'ultimoPeso' => $kg,
                'precioCompra' => $p, 'fechaCompra' => now()->subDays(3)->toDateString(), 'status' => 'Activo']);
        }
        // Total 18,000 + 24,000 = 42,000 · 500 kg · precio promedio ponderado 84.00 · peso promedio 250
        $this->get(route('proveedores.facturas.liquidar', $g->id))->assertOk()
            ->assertSee('42,000.00')->assertSee('84.00')->assertSee('250.0 kg')->assertSee('Rancho Las Palmas');

        $this->post(route('proveedores.facturas.liquidar.pago', $g->id))->assertRedirect(route('proveedores.facturas.liquidar.exito', $g->id));
        $this->assertSame('liquidado', $g->fresh()->estado);

        $this->get(route('proveedores.facturas.liquidar', $g->id))->assertOk()->assertSee('ya fue liquidada')->assertDontSee('Confirmar pago y generar PDF');
        $this->get(route('proveedores.facturas.liquidar.generarPdf', $g->id))->assertOk();
        $this->post(route('proveedores.facturas.liquidar.pago', $g->id))->assertSessionHas('aviso');
    }

    public function test_anticipo_aplicado_se_ve_despues_de_liquidar_y_reemo(): void
    {
        $g = $this->guia('G-A', 'pendiente', now()->toDateString());
        $g->update(['guia_reemo' => 'RM-26-111222']);
        Ganado::create(['facturaID' => $g->id, 'areteID' => 'X-1', 'categoria' => 'Torete', 'pesoActual' => 200, 'ultimoPeso' => 200,
            'precioCompra' => 90, 'fechaCompra' => now()->toDateString(), 'status' => 'Activo']);
        $ad = \App\Models\Ganado\Adelanto::create(['proveedor_id' => $this->prov->id, 'concepto' => 'Anticipo de compra', 'dinero' => 5000, 'date' => now()->toDateString(), 'status' => 'entregado']);

        $this->get(route('proveedores.facturas.liquidar', $g->id))->assertSee('RM-26-111222')->assertSee('Anticipo de compra');
        $this->post(route('proveedores.facturas.liquidar.pago', $g->id), ['adelantos' => [$ad->id]]);

        $r = app(LiquidacionService::class)->calcularResumenFactura($g->id);
        $this->assertEquals(5000, $r['totalAdelantos']);
        $this->assertEquals(13000, $r['montoAbsoluto']);
        $this->get(route('proveedores.facturas.liquidar', $g->id))->assertSee('Anticipo de compra')->assertSee('Anticipos aplicados');
    }

    private function guiaConGanado(string $num, float $importe): FacturaGanado
    {
        $g = $this->guia($num, 'pendiente', now()->toDateString());
        Ganado::create(['facturaID' => $g->id, 'areteID' => 'A-' . $num, 'categoria' => 'Torete', 'pesoActual' => 100, 'ultimoPeso' => 100,
            'precioCompra' => $importe / 100, 'fechaCompra' => now()->toDateString(), 'status' => 'Activo']);

        return $g;
    }

    public function test_anticipos_no_se_cobran_dos_veces_y_el_sobrante_pasa_a_la_siguiente_compra(): void
    {
        $svc = app(LiquidacionService::class);
        $anticipo = fn (float $m) => \App\Models\Ganado\Adelanto::create(['proveedor_id' => $this->prov->id, 'concepto' => "Anticipo {$m}", 'dinero' => $m, 'date' => now()->toDateString(), 'status' => 'entregado']);

        // Caso del cliente: anticipo 10,000, ganado 12,000 → paga 2,000 y el anticipo ya no vuelve a salir
        $a1 = $anticipo(10000);
        $g1 = $this->guiaConGanado('L1', 12000);
        $this->post(route('proveedores.facturas.liquidar.pago', $g1->id), ['adelantos' => [$a1->id]]);
        $this->assertSame('liquidado', $a1->fresh()->status);
        $this->assertEquals(2000, $svc->calcularResumenFactura($g1->id)['montoAbsoluto']);
        $g2 = $this->guiaConGanado('L2', 5000);
        $this->assertCount(0, $svc->calcularResumenFactura($g2->id)['adelantosProveedor']);

        // Anticipo mayor que la compra: se descuenta solo lo necesario y el resto queda para la siguiente
        $a2 = $anticipo(10000);
        $g3 = $this->guiaConGanado('L3', 8000);
        $this->post(route('proveedores.facturas.liquidar.pago', $g3->id), ['adelantos' => [$a2->id]]);
        $this->assertSame('parcial', $a2->fresh()->status);
        $this->assertEquals(2000, $a2->fresh()->saldoDisponible());
        $this->assertEquals(0, $svc->calcularResumenFactura($g3->id)['montoAbsoluto']);

        $r2 = $svc->calcularResumenFactura($g2->id);
        $this->assertEquals(2000, $r2['totalAdelantos']);
        $this->post(route('proveedores.facturas.liquidar.pago', $g2->id), ['adelantos' => [$a2->id]]);
        $this->assertSame('liquidado', $a2->fresh()->status);
        $this->assertEquals(3000, $svc->calcularResumenFactura($g2->id)['montoAbsoluto']);   // 5,000 − 2,000
        $this->assertEquals(0, $a2->fresh()->saldoDisponible());
    }

    public function test_ficha_precarga_siniiga_procedencia_y_upp(): void
    {
        $this->prov->update(['lugar' => 'Ures, Sonora']);
        $g = $this->guia('G-F', 'pendiente', now()->toDateString());
        $g->update(['upp_origen' => '26-030-0451']);
        // Animal registrado antes del cambio (sin esos datos guardados)
        $viejo = Ganado::create(['facturaID' => $g->id, 'areteID' => '888888', 'categoria' => 'Vaca', 'pesoActual' => 400, 'ultimoPeso' => 400,
            'precioCompra' => 50, 'fechaCompra' => now()->toDateString(), 'status' => 'Activo']);
        $this->get(route('ganado.ficha', $viejo->GanadoID))->assertOk()
            ->assertSee('value="888888"', false)->assertSee('Rancho Las Palmas · Ures, Sonora')->assertSee('26-030-0451');

        // Registro nuevo: se guardan solos
        $nuevo = app(\App\Services\GanadoService::class)->registrarLote($g->id, now()->toDateString(), [['arete' => '777777', 'categoria' => 'Torete', 'peso' => 300, 'precio' => 60]])->first();
        $this->assertSame('777777', $nuevo->fresh()->arete_siniiga);
        $this->assertSame('Rancho Las Palmas · Ures, Sonora', $nuevo->fresh()->procedencia);
        $this->assertSame('26-030-0451', $nuevo->fresh()->upp_origen);
    }
}
