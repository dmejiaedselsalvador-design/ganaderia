<?php

namespace Tests\Feature;

use App\Models\Engorda\ConsumoAlimento;
use App\Models\Engorda\CorralLote;
use App\Models\Engorda\RacionAlimento;
use App\Models\Ganaderia\AbonoVenta;
use App\Models\Ganaderia\Cliente;
use App\Models\Ganaderia\EventoEnfermedad;
use App\Models\Ganaderia\GastoOperativo;
use App\Models\Ganaderia\Insumo;
use App\Models\Ganaderia\MovimientoCorral;
use App\Models\Ganaderia\PrecioMercado;
use App\Models\Ganaderia\ProgramacionSanitaria;
use App\Models\Ganado\FacturaGanado;
use App\Models\Ganado\Ganado;
use App\Models\Ganado\ProveedorGanado;
use App\Models\Sanidad\TratamientoSanitario;
use App\Models\User;
use App\Models\Venta\VentaGanado;
use App\Services\Ganaderia\RentabilidadService;
use Database\Seeders\RolesYPermisosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GestionGanaderaTest extends TestCase
{
    use RefreshDatabase;

    private User $jefe;
    private FacturaGanado $guia;
    private CorralLote $corral;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesYPermisosSeeder::class);

        $this->jefe = User::factory()->create();
        $this->jefe->assignRole('gerente');
        $this->jefe->givePermissionTo('planta.ganaderia');

        $prov = ProveedorGanado::create(['nombreProveedor' => 'Rancho El Sauz', 'lugar' => 'Ures, Sonora']);
        $this->guia = FacturaGanado::create(['proveedorID' => $prov->id, 'fechaFactura' => now()->subDays(30), 'numeroFactura' => 'G-001', 'estado' => 'pendiente']);
        $this->corral = CorralLote::create(['codigo' => 'C1', 'nombre' => 'Corral 1', 'fase_engorda' => 'Crecimiento', 'capacidad_maxima' => 50, 'estado' => 'Activo']);

        $this->actingAs($this->jefe)->post(route('plantas.cambiar'), ['planta' => 'ganaderia']);
    }

    private function animal(string $arete, float $peso = 300, float $precio = 60, array $extra = []): Ganado
    {
        return Ganado::create(array_merge([
            'facturaID' => $this->guia->id, 'areteID' => $arete, 'categoria' => 'Torete', 'raza' => 'Angus',
            'pesoActual' => $peso, 'ultimoPeso' => $peso, 'precioCompra' => $precio,
            'fechaCompra' => now()->subDays(30)->toDateString(), 'status' => 'Activo', 'corral_lote_id' => $this->corral->id,
        ], $extra));
    }

    public function test_todas_las_pantallas_nuevas_cargan(): void
    {
        $g = $this->animal('A-1');
        $racion = RacionAlimento::create(['codigo' => 'R1', 'nombre' => 'Crecimiento', 'fase' => 'Crecimiento', 'costo_por_kg' => 7, 'activo' => true]);
        $ins = Insumo::create(['codigo' => 'MAIZ', 'nombre' => 'Maíz', 'tipo' => 'alimento', 'unidad' => 'kg']);
        $cli = Cliente::create(['nombre' => 'Carnes del Norte']);
        $gasto = GastoOperativo::create(['fecha' => today(), 'categoria' => 'Combustible', 'concepto' => 'Diésel', 'monto' => 1000, 'estado' => 'vigente']);

        foreach ([
            route('welcome'), route('configuracion.index'), route('gastos.index'), route('gastos.edit', $gasto),
            route('clientes.index'), route('clientes.create'), route('clientes.show', $cli), route('clientes.edit', $cli), route('cobranza.index'),
            route('inventario.index'), route('inventario.show', $ins), route('raciones.formula', $racion), route('nutricion.planeacion'),
            route('sanidad.calendario'), route('sanidad.enfermedades'), route('ganado.ficha', $g->GanadoID), route('compras.costos.edit', $this->guia->id),
            route('reportes.index'), route('reportes.lotes'), route('reportes.animales'), route('campo.index', ['arete' => 'A-1']),
            route('nutricion.index'), route('sanidad.index'), route('ventas.create'), route('corrales.show', $this->corral->id),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        foreach ([route('gastos.index', ['exportar' => 'csv']), route('reportes.lotes', ['exportar' => 'csv']), route('reportes.animales', ['exportar' => 'csv']),
                  route('reportes.inventario'), route('inventario.index', ['exportar' => 'csv']), route('cobranza.index', ['exportar' => 'csv'])] as $url) {
            $this->get($url)->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        }
        $this->get(route('reportes.lotes', ['exportar' => 'pdf']))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get(route('reportes.inventario', ['exportar' => 'pdf']))->assertOk();
        $this->get(route('clientes.show', [$cli, 'exportar' => 'pdf']))->assertOk();
    }

    public function test_compra_reparte_costos_por_peso_y_calcula_destare(): void
    {
        $a = $this->animal('A-1', 300);
        $b = $this->animal('A-2', 100);

        $this->put(route('compras.costos.update', $this->guia->id), [
            'costo_flete' => 3000, 'costo_comision' => 800, 'costo_tramites' => 200, 'peso_origen_kg' => 420, 'upp_origen' => 'UPP-26-123',
        ])->assertSessionHas('success');

        $this->assertEquals(3000.0, (float) $a->fresh()->costo_adicional_compra); // 75 % del peso
        $this->assertEquals(1000.0, (float) $b->fresh()->costo_adicional_compra);
        $this->assertEquals(300 * 60 + 3000, $a->fresh()->costoAdquisicion());
        $this->assertSame('UPP-26-123', $a->fresh()->upp_origen);
    }

    public function test_rentabilidad_reparte_alimento_gastos_y_valua_a_mercado(): void
    {
        $a = $this->animal('A-1', 300, 60);   // compra 18,000
        $b = $this->animal('A-2', 300, 60);
        $a->update(['ultimoPeso' => 400]);

        $racion = RacionAlimento::create(['codigo' => 'R1', 'nombre' => 'R', 'fase' => 'Crecimiento', 'costo_por_kg' => 5, 'activo' => true]);
        ConsumoAlimento::create(['corral_lote_id' => $this->corral->id, 'racion_id' => $racion->id, 'fecha' => now()->subDays(5), 'kilos_servidos' => 200,
            'kilos_rechazo' => 0, 'kilos_consumidos' => 200, 'costo_total_mxn' => 1000, 'cabezas_contadas' => 2, 'consumo_promedio_cabeza_kg' => 100]);
        GastoOperativo::create(['fecha' => now()->subDays(3), 'categoria' => 'Mantenimiento', 'concepto' => 'Comederos', 'monto' => 400,
            'corral_lote_id' => $this->corral->id, 'estado' => 'vigente']);
        TratamientoSanitario::create(['ganado_id' => $a->GanadoID, 'tipo_evento' => 'Vacunacion', 'medicamento_producto' => 'X', 'dosis_aplicada' => '5 ml',
            'via_administracion' => 'SC', 'fecha_aplicacion' => now()->subDays(10), 'dias_retiro_rastro' => 0, 'costo_medicamento_mxn' => 50]);
        PrecioMercado::create(['fecha' => today(), 'categoria' => 'Torete', 'precio_kg' => 70]);

        $r = app(RentabilidadService::class)->deAnimal($a->fresh());
        $this->assertEquals(18000, $r['compra']);
        $this->assertEquals(500, $r['alimento']);       // 1,000 / 2 cabezas
        $this->assertEquals(200, $r['gasto_corral']);   // 400 / 2 cabezas
        $this->assertEquals(50, $r['medicina']);
        $this->assertEquals(18750, $r['costo_total']);
        $this->assertEquals(28000, $r['ingreso']);      // 400 kg x $70
        $this->assertEquals(9250, $r['utilidad']);
        $this->assertEquals(7.5, $r['costo_kg']);       // (500+200+50) / 100 kg

        $this->get(route('welcome'))->assertSee('Valor de mercado del hato');
    }

    public function test_venta_a_credito_abonos_y_limite(): void
    {
        $cli = Cliente::create(['nombre' => 'Carnes del Norte', 'rfc' => 'CNO010101AB1', 'dias_credito' => 15, 'limite_credito' => 30000]);
        $a = $this->animal('A-1', 400);
        $b = $this->animal('A-2', 400);

        $vender = fn ($g) => $this->post(route('ventas.store'), [
            'cliente_id' => $cli->id, 'cliente_nombre' => '', 'condicion_pago' => 'credito', 'forma_pago' => 'Transferencia', 'fecha_venta' => today()->toDateString(),
            'modalidad_pago' => 'por_kilo', 'animales' => [['ganado_id' => $g->GanadoID, 'peso_venta' => 400, 'precio_unitario' => 50]],
        ]);

        $vender($a)->assertSessionHasNoErrors();
        $venta = VentaGanado::first();
        $this->assertSame('Carnes del Norte', $venta->cliente_nombre);
        $this->assertEquals(20000, (float) $venta->saldo_pendiente);
        $this->assertSame(today()->addDays(15)->toDateString(), $venta->fecha_vencimiento->toDateString());

        // Excede el límite de crédito (20,000 + 20,000 > 30,000)
        $vender($b)->assertSessionHasErrors('error');

        $this->post(route('ventas.abonar', $venta), ['monto' => 25000, 'fecha' => today()->toDateString(), 'forma_pago' => 'Efectivo'])->assertSessionHasErrors('monto');
        $this->post(route('ventas.abonar', $venta), ['monto' => 8000, 'fecha' => today()->toDateString(), 'forma_pago' => 'Efectivo'])->assertSessionHas('success');
        $this->assertEquals(12000, (float) $venta->fresh()->saldo_pendiente);

        $this->post(route('abonos.anular', AbonoVenta::first()), ['motivo' => 'Cheque rebotado']);
        $this->assertEquals(20000, (float) $venta->fresh()->saldo_pendiente);
    }

    public function test_venta_de_contado_queda_pagada(): void
    {
        $a = $this->animal('A-1', 400);
        $this->post(route('ventas.store'), [
            'cliente_nombre' => 'Comprador ocasional', 'condicion_pago' => 'contado', 'forma_pago' => 'Efectivo', 'fecha_venta' => today()->toDateString(),
            'modalidad_pago' => 'por_kilo', 'animales' => [['ganado_id' => $a->GanadoID, 'peso_venta' => 400, 'precio_unitario' => 50]],
        ])->assertSessionHasNoErrors();

        $v = VentaGanado::first();
        $this->assertEquals(0, (float) $v->saldo_pendiente);
        $this->assertSame(1, $v->abonos()->count());
    }

    public function test_rfc_invalido_se_rechaza(): void
    {
        $this->post(route('clientes.store'), ['nombre' => 'X', 'rfc' => 'ABC'])->assertSessionHasErrors('rfc');
        $this->post(route('clientes.store'), ['nombre' => 'X', 'rfc' => 'xaxx010101000'])->assertSessionHasNoErrors();
        $this->assertSame('XAXX010101000', Cliente::first()->rfc);
    }

    public function test_inventario_costo_promedio_ajuste_y_salida_insuficiente(): void
    {
        $ins = Insumo::create(['codigo' => 'MAIZ', 'nombre' => 'Maíz', 'tipo' => 'alimento', 'unidad' => 'kg']);
        $mov = fn ($d) => $this->post(route('inventario.movimiento', $ins), $d + ['fecha' => today()->toDateString()]);

        $mov(['tipo' => 'entrada', 'cantidad' => 1000, 'costo_unitario' => 5])->assertSessionHasNoErrors();
        $mov(['tipo' => 'entrada', 'cantidad' => 1000, 'costo_unitario' => 7])->assertSessionHasNoErrors();
        $this->assertEquals(6.0, (float) $ins->fresh()->costo_promedio);
        $this->assertEquals(2000, (float) $ins->fresh()->existencia);

        $mov(['tipo' => 'salida', 'cantidad' => 5000])->assertSessionHasErrors('cantidad');
        $mov(['tipo' => 'ajuste', 'cantidad' => 1950])->assertSessionHasNoErrors();
        $this->assertEquals(1950, (float) $ins->fresh()->existencia);
        $this->assertSame(3, $ins->movimientos()->count());
    }

    public function test_receta_bom_descuenta_ingredientes_al_servir_alimento(): void
    {
        $this->animal('A-1');
        $maiz = Insumo::create(['codigo' => 'MAIZ', 'nombre' => 'Maíz', 'tipo' => 'alimento', 'unidad' => 'kg', 'existencia' => 1000, 'costo_promedio' => 6]);
        $soya = Insumo::create(['codigo' => 'SOYA', 'nombre' => 'Pasta de soya', 'tipo' => 'alimento', 'unidad' => 'ton', 'existencia' => 1, 'costo_promedio' => 12000]);
        $racion = RacionAlimento::create(['codigo' => 'R1', 'nombre' => 'Crecimiento', 'fase' => 'Crecimiento', 'costo_por_kg' => 1, 'activo' => true]);

        $this->put(route('raciones.formula.update', $racion), ['ingredientes' => [['insumo_id' => $maiz->id, 'porcentaje' => 70], ['insumo_id' => $soya->id, 'porcentaje' => 20]]])
            ->assertSessionHasErrors('ingredientes'); // suma 90 %
        $this->put(route('raciones.formula.update', $racion), ['actualizar_costo' => 1,
            'ingredientes' => [['insumo_id' => $maiz->id, 'porcentaje' => 80], ['insumo_id' => $soya->id, 'porcentaje' => 20]]])->assertSessionHasNoErrors();
        $this->assertEquals(7.2, (float) $racion->fresh()->costo_por_kg); // 0.8*6 + 0.2*12

        $this->post(route('nutricion.consumo.store'), ['corral_lote_id' => $this->corral->id, 'racion_id' => $racion->id, 'fecha' => today()->toDateString(),
            'kilos_servidos' => 500, 'kilos_rechazo' => 10])->assertSessionHasNoErrors();

        $this->assertEquals(600, (float) $maiz->fresh()->existencia);   // 1000 - 400
        $this->assertEquals(0.9, (float) $soya->fresh()->existencia);   // 1 ton - 0.1 ton

        $this->post(route('nutricion.plan.asignar', $this->corral->id), ['racion_id' => $racion->id, 'desde' => today()->toDateString()])->assertSessionHasNoErrors();
        $this->get(route('nutricion.planeacion', ['dias' => 10]))->assertOk()->assertSee('Maíz')->assertSee('Pasta de soya');
    }

    public function test_aplicacion_masiva_al_corral(): void
    {
        $this->animal('A-1');
        $this->animal('A-2');
        $med = Insumo::create(['codigo' => 'VAC', 'nombre' => 'Vacuna 7 vías', 'tipo' => 'medicamento', 'unidad' => 'ml', 'existencia' => 100, 'costo_promedio' => 3, 'dias_retiro' => 21]);

        $this->post(route('sanidad.programar'), ['titulo' => 'Vacuna clostridial', 'tipo_evento' => 'Vacunacion', 'corral_lote_id' => $this->corral->id,
            'insumo_id' => $med->id, 'fecha_programada' => today()->toDateString(), 'repetir_dias' => 21, 'repeticiones' => 2])->assertSessionHasNoErrors();
        $this->assertSame(2, ProgramacionSanitaria::count());

        $prog = ProgramacionSanitaria::orderBy('fecha_programada')->first();
        $this->post(route('sanidad.aplicar', $prog), ['fecha_aplicacion' => today()->toDateString(), 'medicamento_producto' => 'Vacuna 7 vías',
            'dosis_aplicada' => '5 ml', 'via_administracion' => 'Subcutánea', 'cantidad_por_cabeza' => 5, 'dias_retiro_rastro' => 21, 'lote_medicamento' => 'L-77'])
            ->assertSessionHasNoErrors();

        $this->assertSame(2, TratamientoSanitario::where('programacion_id', $prog->id)->count());
        $this->assertEquals(15.0, (float) TratamientoSanitario::first()->costo_medicamento_mxn); // 5 ml x $3
        $this->assertEquals(90, (float) $med->fresh()->existencia);
        $this->assertSame('aplicada', $prog->fresh()->estado);
    }

    public function test_casos_de_enfermedad_y_recaida(): void
    {
        $a = $this->animal('A-1');
        $caso = ['ganado_id' => $a->GanadoID, 'fecha_inicio' => today()->subDays(5)->toDateString(), 'diagnostico' => 'Fiebre de embarque', 'severidad' => 'moderada'];
        $this->post(route('sanidad.casos.store'), $caso)->assertSessionHasNoErrors();
        $this->put(route('sanidad.casos.update', EventoEnfermedad::first()), ['resultado' => 'recuperado', 'fecha_alta' => today()->toDateString()]);
        $this->post(route('sanidad.casos.store'), ['fecha_inicio' => today()->toDateString()] + $caso);

        $this->assertTrue(EventoEnfermedad::latest('id')->first()->es_recaida);
        $this->get(route('sanidad.enfermedades'))->assertOk()->assertSee('RECAÍDA');
    }

    public function test_historial_de_corrales_y_baja_por_robo(): void
    {
        $a = $this->animal('A-1', 300, 60, ['corral_lote_id' => null]);
        $c2 = CorralLote::create(['codigo' => 'C2', 'nombre' => 'Corral 2', 'fase_engorda' => 'Finalizacion', 'capacidad_maxima' => 50, 'estado' => 'Activo']);

        $this->post(route('corrales.asignar.ganado', $this->corral->id), ['ganado_ids' => [$a->GanadoID]]);
        $this->post(route('ganado.mover', $a->GanadoID), ['corral_lote_id' => $c2->id, 'fecha' => today()->toDateString(), 'motivo' => 'Finalización']);
        $this->assertSame(2, MovimientoCorral::count());
        $this->assertSame($c2->id, $a->fresh()->corral_lote_id);

        $this->post(route('ganado.bajas.store'), ['ganado_id' => $a->GanadoID, 'fecha_muerte' => today()->toDateString(),
            'causa_muerte' => 'Robo / Abigeato', 'tipo_baja' => 'Robo', 'valor_recuperado_mxn' => 0])->assertSessionHasNoErrors();
        $this->assertSame('Baja', $a->fresh()->status);
        $this->assertEquals(18000, (float) $a->fresh()->baja->valor_perdido_mxn);
    }

    public function test_importar_pesajes_csv(): void
    {
        $this->animal('A-1', 300);
        $this->animal('A-2', 300);
        $csv = "arete,peso,fecha,cc\nA-1,320,{$this->hoy()},6\nA-2,315.5,{$this->hoy()}\nNO-EXISTE,300,{$this->hoy()}\n";
        $archivo = UploadedFile::fake()->createWithContent('bascula.csv', $csv);

        $this->post(route('ganado.pesajes.importar'), ['archivo' => $archivo, 'bascula' => 'Báscula 1'])
            ->assertSessionHas('success', 'Se importaron 2 pesajes.')->assertSessionHas('errores_importacion');
        $this->assertEquals(320, (float) Ganado::where('areteID', 'A-1')->first()->ultimoPeso);
    }

    public function test_documentos_y_fotos(): void
    {
        Storage::fake('local');
        $a = $this->animal('A-1');

        $this->post(route('documentos.store'), ['adjuntable' => 'compra', 'adjuntable_id' => $this->guia->id, 'tipo' => 'guia_reemo', 'folio' => 'REEMO-1',
            'archivo' => UploadedFile::fake()->create('guia.pdf', 100, 'application/pdf')])->assertSessionHas('success');
        $doc = $this->guia->documentos()->first();
        $this->assertNotNull($doc);
        $this->get(route('documentos.descargar', $doc))->assertOk();

        $this->post(route('ganado.fotos.store', $a->GanadoID), ['foto' => UploadedFile::fake()->create('vaca.jpg', 50, 'image/jpeg')])->assertSessionHas('success');
        $this->assertSame(1, $a->fotos()->count());
    }

    public function test_gastos_anular_y_auditoria(): void
    {
        $this->post(route('gastos.store'), ['fecha' => today()->toDateString(), 'categoria' => 'Mano de obra', 'concepto' => 'Nómina', 'monto' => 15000])->assertSessionHasNoErrors();
        $g = GastoOperativo::first();
        $this->post(route('gastos.anular', $g), ['motivo' => 'Duplicado'])->assertRedirect(route('gastos.index'));
        $this->assertSame('anulado', $g->fresh()->estado);

        $this->assertDatabaseHas('auditoria', ['modelo' => 'GastoOperativo', 'accion' => 'creado', 'user_id' => $this->jefe->id]);
        $this->assertDatabaseHas('auditoria', ['modelo' => 'GastoOperativo', 'accion' => 'actualizado']);
        $this->get(route('auditoria.index'))->assertOk()->assertSee('Nómina');
    }

    public function test_permisos_de_modulos_nuevos(): void
    {
        $op = User::factory()->create();
        $op->assignRole('operario');
        $op->givePermissionTo('planta.ganaderia');
        $this->actingAs($op)->post(route('plantas.cambiar'), ['planta' => 'ganaderia']);

        $this->get(route('gastos.index'))->assertForbidden();
        $this->get(route('clientes.index'))->assertForbidden();
        $this->get(route('reportes.index'))->assertForbidden();
        $this->get(route('auditoria.index'))->assertForbidden();
        $this->get(route('campo.index'))->assertOk();
    }

    private function hoy(): string
    {
        return today()->toDateString();
    }
}
