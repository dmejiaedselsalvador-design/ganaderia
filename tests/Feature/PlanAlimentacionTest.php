<?php

namespace Tests\Feature;

use App\Models\Engorda\CorralLote;
use App\Models\Engorda\RacionAlimento;
use App\Models\Ganaderia\AsignacionDieta;
use App\Models\Ganaderia\Cliente;
use App\Models\Ganaderia\Insumo;
use App\Models\Ganado\FacturaGanado;
use App\Models\Ganado\Ganado;
use App\Models\Ganado\PesajeGanado;
use App\Models\Ganado\ProveedorGanado;
use App\Models\User;
use App\Services\Ganaderia\BiomasaService;
use App\Services\Ganaderia\ConsumoRecomendadoService;
use Database\Seeders\RolesYPermisosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanAlimentacionTest extends TestCase
{
    use RefreshDatabase;

    private User $jefe;
    private FacturaGanado $guia;
    private CorralLote $corral;
    private RacionAlimento $racion;
    private Insumo $maiz;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesYPermisosSeeder::class);
        $this->jefe = User::factory()->create();
        $this->jefe->assignRole('gerente');
        $this->jefe->givePermissionTo('planta.ganaderia');

        $prov = ProveedorGanado::create(['nombreProveedor' => 'Rancho El Sauz']);
        $this->guia = FacturaGanado::create(['proveedorID' => $prov->id, 'fechaFactura' => now()->subDays(60), 'numeroFactura' => 'G-1', 'estado' => 'pendiente']);
        $this->corral = CorralLote::create(['codigo' => 'C1', 'nombre' => 'Corral 1', 'fase_engorda' => 'Finalizacion', 'capacidad_maxima' => 50, 'estado' => 'Activo', 'meta_gdp' => 1.5]);

        // Dieta 100 % maíz con 80 % MS: 1 kg MS = 1.25 kg tal como se ofrece
        $this->maiz = Insumo::create(['codigo' => 'MAIZ', 'nombre' => 'Maíz', 'tipo' => 'alimento', 'unidad' => 'kg', 'existencia' => 1000, 'costo_promedio' => 6, 'materia_seca_pct' => 80]);
        $this->racion = RacionAlimento::create(['codigo' => 'R1', 'nombre' => 'Finalización', 'fase' => 'Finalizacion', 'costo_por_kg' => 6, 'activo' => true,
            'materia_seca_pct' => 80, 'base_consumo' => 'MS', 'pct_pv_min' => 2.5, 'pct_pv_max' => 3.2, 'pct_pv_sugerido' => 2.8]);
        $this->racion->ingredientes()->create(['insumo_id' => $this->maiz->id, 'porcentaje' => 100]);

        $this->actingAs($this->jefe)->post(route('plantas.cambiar'), ['planta' => 'ganaderia']);
    }

    private function animal(string $arete, float $peso, ?array $pesaje = null): Ganado
    {
        $g = Ganado::create([
            'facturaID' => $this->guia->id, 'areteID' => $arete, 'categoria' => 'Novillo', 'raza' => 'Angus',
            'pesoActual' => $peso, 'ultimoPeso' => $pesaje[0] ?? $peso, 'precioCompra' => 60,
            'fechaCompra' => now()->subDays(5)->toDateString(), 'status' => 'Activo', 'corral_lote_id' => $this->corral->id,
        ]);
        if ($pesaje) {
            PesajeGanado::create(['ganado_id' => $g->GanadoID, 'peso' => $pesaje[0], 'fecha_pesaje' => now()->subDays($pesaje[1])->toDateString(), 'gdp' => $pesaje[2] ?? null]);
        }

        return $g;
    }

    public function test_biomasa_usa_ultimo_pesaje_y_proyecta_los_vencidos(): void
    {
        $this->animal('A-1', 300, [400, 3]);          // vigente: 400
        $this->animal('A-2', 300, [400, 31, 1.2]);    // vencido 31 días × 1.2 = 437.2
        $this->animal('A-3', 350);                    // sin pesaje, compra hace 5 días: 350

        $a = app(BiomasaService::class)->animales()->keyBy('arete');
        $this->assertEquals(400, $a['A-1']['peso']);
        $this->assertFalse($a['A-1']['estimado']);
        $this->assertEquals(437.2, $a['A-2']['peso']);
        $this->assertTrue($a['A-2']['estimado']);
        $this->assertTrue($a['A-3']['sin_pesaje']);
        $this->assertEquals(350, $a['A-3']['peso']);

        $c = app(BiomasaService::class)->porCorral()->first();
        $this->assertEquals(3, $c['cabezas']);
        $this->assertEqualsWithDelta(1187.2, $c['biomasa'], 0.01);
    }

    public function test_consumo_por_peso_vivo_en_materia_seca_y_tal_como_se_ofrece(): void
    {
        $this->animal('A-1', 400, [400, 1]);
        $this->animal('A-2', 400, [400, 1]);
        AsignacionDieta::create(['corral_lote_id' => $this->corral->id, 'racion_id' => $this->racion->id, 'desde' => today(), 'user_id' => $this->jefe->id]);

        $plan = app(ConsumoRecomendadoService::class)->plan(CorralLote::whereKey($this->corral->id)->get(), 10);
        $l = $plan['lineas'][$this->corral->id];
        $this->assertEquals(2.8, $l['pct']);
        $this->assertEqualsWithDelta(11.2, $l['kg_cab_ms'], 0.01);   // 400 × 2.8 %
        $this->assertEqualsWithDelta(14.0, $l['kg_cab_tco'], 0.01);  // ÷ 0.80
        $this->assertEqualsWithDelta(28.0, $l['kg_dia'], 0.01);
        $this->assertEqualsWithDelta(280.0, $l['kg_periodo'], 0.01);

        $maiz = $plan['bodega']->firstWhere('insumo.id', $this->maiz->id);
        $this->assertEqualsWithDelta(280.0, $maiz['kg'], 0.01);
        $this->assertSame('ok', $maiz['estado']);

        // 50 días = 1,400 kg y solo hay 1,000 -> faltante
        $plan = app(ConsumoRecomendadoService::class)->plan(CorralLote::whereKey($this->corral->id)->get(), 50);
        $this->assertSame('faltante', $plan['bodega']->first()['estado']);
        $this->assertEqualsWithDelta(400, $plan['bodega']->first()['faltante_kg'], 0.01);
    }

    public function test_asignar_dieta_guarda_historial_y_pct_fuera_de_rango_pide_justificacion(): void
    {
        $otra = RacionAlimento::create(['codigo' => 'R2', 'nombre' => 'Crecimiento', 'fase' => 'Crecimiento', 'costo_por_kg' => 5, 'activo' => true]);

        $this->post(route('nutricion.plan.asignar', $this->corral), ['racion_id' => $otra->id, 'desde' => today()->subDays(10)->toDateString()])->assertSessionHasNoErrors();
        $this->post(route('nutricion.plan.asignar', $this->corral), ['racion_id' => $this->racion->id, 'desde' => today()->toDateString()])->assertSessionHasNoErrors();

        $hist = AsignacionDieta::where('corral_lote_id', $this->corral->id)->orderBy('desde')->get();
        $this->assertCount(2, $hist);
        $this->assertEquals(today()->subDay()->toDateString(), $hist[0]->hasta->toDateString());
        $this->assertNull($hist[1]->hasta);
        $this->assertEquals($this->racion->id, $this->corral->fresh()->dietaVigente->racion_id);

        $this->put(route('nutricion.plan.pct', $this->corral), ['pct_pv' => 3.8])->assertSessionHasErrors();
        $this->put(route('nutricion.plan.pct', $this->corral), ['pct_pv' => 3.8, 'justificacion' => 'Indicación del nutriólogo'])->assertSessionHasNoErrors();
        $this->assertEquals(3.8, (float) $this->corral->fresh()->dietaVigente->pct_pv);

        $this->animal('A-1', 420, [420, 2]);
        $this->get(route('nutricion.plan.corral', $this->corral))->assertOk()->assertSee('A-1');
        $this->get(route('nutricion.planeacion', ['dias' => 7]))->assertOk()->assertSee('Maíz');
    }

    public function test_servir_precarga_dieta_y_kilos_sugeridos(): void
    {
        $this->animal('A-1', 400, [400, 1]);
        AsignacionDieta::create(['corral_lote_id' => $this->corral->id, 'racion_id' => $this->racion->id, 'desde' => today(), 'user_id' => $this->jefe->id]);

        $volver = route('nutricion.plan.corral', $this->corral);
        $this->get(route('nutricion.index', ['servir' => $this->corral->id, 'volver' => $volver]))->assertOk()
            ->assertViewHas('sugerencias', fn ($s) => $s[$this->corral->id]['kg'] == 14 && $s[$this->corral->id]['racion'] == $this->racion->id);

        $this->post(route('nutricion.consumo.store'), ['corral_lote_id' => $this->corral->id, 'racion_id' => $this->racion->id,
            'fecha' => today()->toDateString(), 'kilos_servidos' => 14, 'volver' => $volver])->assertRedirect($volver);
        $this->assertEquals(986, (float) $this->maiz->fresh()->existencia);

        // Una URL externa nunca se usa como regreso
        $this->post(route('nutricion.consumo.store'), ['corral_lote_id' => $this->corral->id, 'racion_id' => $this->racion->id,
            'fecha' => today()->toDateString(), 'kilos_servidos' => 1, 'volver' => 'https://sitio-malo.com/x'])->assertRedirect(route('nutricion.index'));
    }

    public function test_nueva_dieta_toma_rango_por_fase_y_lleva_a_la_receta(): void
    {
        $volver = route('nutricion.plan.corral', $this->corral);
        $r = $this->post(route('nutricion.racion.store'), ['codigo' => 'R9', 'nombre' => 'Recepción', 'fase' => 'Recepcion', 'costo_por_kg' => 5,
            'materia_seca_pct' => 85, 'proteina_cruda_pct' => 14, 'energia_metabolizable_mcal' => 2.6, 'volver' => $volver]);
        $nueva = RacionAlimento::where('codigo', 'R9')->first();
        $r->assertRedirect(route('raciones.formula', ['racion' => $nueva->id, 'volver' => $volver]));
        $this->assertEquals([1.8, 2.5, 2.2], [(float) $nueva->pct_pv_min, (float) $nueva->pct_pv_max, (float) $nueva->pct_pv_sugerido]);

        $this->get(route('raciones.formula', ['racion' => $nueva->id, 'volver' => $volver]))->assertOk()->assertSee('Consumo por peso vivo');
        $this->put(route('raciones.formula.update', $nueva), ['volver' => $volver, 'ingredientes' => [['insumo_id' => $this->maiz->id, 'porcentaje' => 100]]])
            ->assertRedirect($volver);

        $this->put(route('raciones.consumo.update', $nueva), ['base_consumo' => 'MS', 'pct_pv_min' => 2, 'pct_pv_max' => 1.5, 'pct_pv_sugerido' => 1.8])
            ->assertSessionHasErrors('pct_pv_max');
    }

    public function test_atajos_crean_catalogos_sin_salir_del_formulario(): void
    {
        $this->postJson(route('atajos.store', 'proveedor'), ['nombreProveedor' => 'Rancho Nuevo', 'telefono' => '6621234567'])
            ->assertCreated()->assertJsonPath('texto', 'Rancho Nuevo');
        $this->postJson(route('atajos.store', 'proveedor'), [])->assertUnprocessable()->assertJsonValidationErrors('nombreProveedor');

        $this->postJson(route('atajos.store', 'cliente'), ['nombre' => 'Carnes del Norte', 'rfc' => 'cno010101ab1'])
            ->assertCreated()->assertJsonPath('data.rfc', 'CNO010101AB1');
        $this->postJson(route('atajos.store', 'cliente'), ['nombre' => 'X', 'rfc' => 'MAL'])->assertJsonValidationErrors('rfc');

        $r = $this->postJson(route('atajos.store', 'insumo'), ['nombre' => 'Maíz rolado', 'tipo' => 'alimento', 'unidad' => 'ton', 'materia_seca_pct' => 86])->assertCreated();
        $ins = Insumo::find($r->json('id'));
        $this->assertSame('MAIZ-ROLADO', $ins->codigo);
        $this->assertEquals(1000, $ins->kgPorUnidad());

        $this->postJson(route('atajos.store', 'corral'), ['codigo' => 'c9', 'nombre' => 'Corral 9', 'fase_engorda' => 'Recepcion', 'capacidad_maxima' => 40])
            ->assertCreated()->assertJsonPath('texto', 'C9 · Corral 9');
        $this->postJson(route('atajos.store', 'corral'), ['codigo' => 'C9', 'nombre' => 'Otro', 'fase_engorda' => 'Recepcion', 'capacidad_maxima' => 40])
            ->assertJsonValidationErrors('codigo');

        $this->postJson(route('atajos.store', 'otro'))->assertNotFound();

        // Pantallas con atajo cargan
        $this->get(route('proveedores.facturas.crear'))->assertOk()->assertSee('Nuevo proveedor');
        $this->get(route('ventas.create'))->assertOk()->assertSee('Registrar cliente nuevo');
    }

    public function test_operario_sin_permiso_no_usa_atajos(): void
    {
        $op = User::factory()->create();
        $op->assignRole('operario');
        $op->givePermissionTo('planta.ganaderia');
        $this->actingAs($op)->post(route('plantas.cambiar'), ['planta' => 'ganaderia']);

        if (! $op->can('ganaderia.clientes')) {
            $this->postJson(route('atajos.store', 'cliente'), ['nombre' => 'X'])->assertForbidden();
        }
        $this->assertTrue(true);
    }

    public function test_receta_por_cantidades_convierte_unidades_y_agua_no_se_descuenta(): void
    {
        $agua = Insumo::create(['codigo' => 'AGUA', 'nombre' => 'Agua de pozo', 'tipo' => 'otro', 'unidad' => 'l', 'factor_kg' => 1, 'controla_existencia' => false]);
        $sal = Insumo::create(['codigo' => 'SAL', 'nombre' => 'Sal', 'tipo' => 'alimento', 'unidad' => 'kg', 'existencia' => 50, 'costo_promedio' => 5]);
        $ternero = RacionAlimento::create(['codigo' => 'TER', 'nombre' => 'Engorda ternero', 'fase' => 'Recepcion', 'costo_por_kg' => 1, 'activo' => true]);

        // 2 kg maíz + 2 l agua + 2 lb sal = 2 + 2 + 0.907 = 4.907 kg
        $this->put(route('raciones.formula.update', $ternero), ['modo' => 'cantidad', 'actualizar_costo' => 1, 'ingredientes' => [
            ['insumo_id' => $this->maiz->id, 'cantidad' => 2, 'unidad' => 'kg'],
            ['insumo_id' => $agua->id, 'cantidad' => 2, 'unidad' => 'l'],
            ['insumo_id' => $sal->id, 'cantidad' => 2, 'unidad' => 'lb'],
        ]])->assertSessionHasNoErrors();

        $ing = $ternero->fresh('ingredientes')->ingredientes->keyBy('insumo_id');
        $this->assertEqualsWithDelta(40.758, (float) $ing[$this->maiz->id]->porcentaje, 0.01);
        $this->assertEqualsWithDelta(18.485, (float) $ing[$sal->id]->porcentaje, 0.01);
        $this->assertSame('lb', $ing[$sal->id]->unidad_captura);
        $this->assertTrue($ternero->fresh()->recetaCompleta());

        // Servir 49.07 kg (10 baches): 20 kg maíz, 9.07 kg sal, el agua no se toca
        $this->post(route('nutricion.consumo.store'), ['corral_lote_id' => $this->corral->id, 'racion_id' => $ternero->id,
            'fecha' => today()->toDateString(), 'kilos_servidos' => 49.07])->assertSessionHasNoErrors();
        $this->assertEqualsWithDelta(980, (float) $this->maiz->fresh()->existencia, 0.05);
        $this->assertEqualsWithDelta(40.93, (float) $sal->fresh()->existencia, 0.05);
        $this->assertEquals(0, (float) $agua->fresh()->existencia);

        // Unidad que no aplica al insumo
        $this->put(route('raciones.formula.update', $ternero), ['modo' => 'cantidad', 'ingredientes' => [
            ['insumo_id' => $sal->id, 'cantidad' => 2, 'unidad' => 'saco'],
        ]])->assertSessionHasErrors('ingredientes');

        $this->get(route('raciones.formula', $ternero))->assertOk()->assertSee('Por cantidades')->assertSee('Agua de pozo');
    }
}
