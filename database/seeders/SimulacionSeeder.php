<?php

namespace Database\Seeders;

use App\Models\Calidad\EvaluacionCanal;
use App\Models\Engorda\ConsumoAlimento;
use App\Models\Engorda\CorralLote;
use App\Models\Engorda\RacionAlimento;
use App\Models\Ganaderia\AsignacionDieta;
use App\Models\Ganaderia\Cliente;
use App\Models\Ganaderia\EventoEnfermedad;
use App\Models\Ganaderia\GastoOperativo;
use App\Models\Ganaderia\Insumo;
use App\Models\Ganaderia\MovimientoCorral;
use App\Models\Ganaderia\PrecioMercado;
use App\Models\Ganaderia\ProgramacionSanitaria;
use App\Models\Ganaderia\RacionIngrediente;
use App\Models\Ganaderia\Ubicacion;
use App\Models\Ganado\FacturaGanado;
use App\Models\Ganado\Ganado;
use App\Models\Ganado\ProveedorGanado;
use App\Models\PreguntaAyuda;
use App\Models\Sanidad\TratamientoSanitario;
use App\Models\User;
use App\Services\Ganaderia\CobranzaService;
use App\Services\Ganaderia\ConsumoRecomendadoService;
use App\Services\Ganaderia\ExplosionMaterialesService;
use App\Services\Ganaderia\InventarioService;
use App\Services\GanadoService;
use App\Services\LiquidacionService;
use App\Services\MortalidadService;
use App\Services\PesajeService;
use App\Services\VentaService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * SIMULACIÓN DE OPERACIÓN (≈ 3 meses de una engorda en Hermosillo, Sonora) para ver el sistema lleno.
 *
 *   php artisan db:seed --class=SimulacionSeeder
 *
 * - También desde Administración → Datos de prueba (solo Súper Administrador), con botón para eliminarla.
 * - Solo se carga si no hay ganado registrado. Todo lo que crea queda anotado (App\Support\Simulacion)
 *   y se borra exactamente eso al eliminar.
 *
 * Genera 10 de cada catálogo principal y la operación que los conecta:
 * proveedores, guías (10 × 10 animales), corrales, dietas con receta, insumos y medicamentos con compras,
 * pesajes, movimientos de corral, dieta asignada, servidos de 14 días (descuentan bodega), protocolos
 * sanitarios, enfermedades, bajas, clientes, ventas de contado y crédito, abonos, canales, gastos,
 * precios de mercado, usuarios de prueba por rol y preguntas de ayuda.
 */
class SimulacionSeeder extends Seeder
{
    public const CLAVE_USUARIOS = 'Simulacion2026*';

    /** El botón del Súper Administrador permite cargarla también en el servidor (solo sobre una base sin ganado). */
    public bool $permitirEnProduccion = false;

    private Carbon $hoy;
    private ?User $admin = null;
    /** @var array<string, Insumo> */
    private array $ins = [];
    /** @var array<string, CorralLote> */
    private array $corrales = [];
    /** @var array<string, RacionAlimento> */
    private array $dietas = [];

    public function run(): void
    {
        if (app()->isProduction() && ! $this->permitirEnProduccion) {
            $this->command?->error('En producción la simulación solo se carga desde Administración → Datos de prueba.');

            return;
        }
        if (! \App\Support\Simulacion::sePuedeCargar()) {
            throw new \RuntimeException('La simulación solo se carga sobre una operación vacía (sin ganado) y sin otra simulación cargada.');
        }

        \App\Support\Simulacion::rastrear(fn () => $this->simular());
    }

    private function simular(): void
    {
        mt_srand(2026);
        $this->hoy = Carbon::today();

        $this->call(RolesYPermisosSeeder::class);
        // Desde el botón: el usuario que la carga. Desde la consola: el primer Súper Administrador.
        $this->admin = Auth::user() ?? User::role('super-admin')->first() ?? User::first();
        if ($this->admin && ! Auth::check()) {
            Auth::setUser($this->admin);
        }

        $this->paso('Catálogos (ubicaciones, precios, proveedores, corrales, clientes)', function () {
            $this->ubicacionesYPrecios();
            $this->proveedoresYCorrales();
            $this->clientes();
        });
        $this->paso('Insumos y dietas con receta', fn () => $this->insumosYDietas());
        $this->paso('Guías de compra, animales y movimientos de corral', fn () => $this->comprasYAnimales());
        $this->paso('Pesajes', fn () => $this->pesajes());
        $this->paso('Dietas asignadas, compras de bodega y servidos de 14 días', fn () => $this->alimentacion());
        $this->paso('Sanidad: protocolos, calendario y enfermedades', fn () => $this->sanidad());
        $this->paso('Bajas', fn () => $this->bajas());
        $this->paso('Ventas, cobranza y calidad de canal', fn () => $this->ventas());
        $this->paso('Gastos, liquidaciones, usuarios y preguntas de ayuda', function () {
            $this->gastos();
            $this->liquidaciones();
            $this->usuarios();
            $this->preguntas();
        });

        $this->command?->info('Simulación lista: ' . Ganado::count() . ' animales, ' . ConsumoAlimento::count() . ' servidos, '
            . \App\Models\Venta\VentaGanado::count() . ' ventas.');
    }

    private function paso(string $texto, callable $fn): void
    {
        $this->command?->line("  · {$texto}…");
        $fn();
    }

    private function dia(int $haceDias): string
    {
        return $this->hoy->copy()->subDays($haceDias)->toDateString();
    }

    private function azar(float $min, float $max, int $dec = 2): float
    {
        return round($min + mt_rand() / mt_getrandmax() * ($max - $min), $dec);
    }

    // ───────────────────────── Catálogos ─────────────────────────

    private function ubicacionesYPrecios(): void
    {
        Ubicacion::create(['nombre' => 'Rancho El Taurino (corrales de engorda)', 'clave_upp' => '26-030-0451', 'municipio' => 'Hermosillo', 'estado' => 'Sonora', 'responsable' => 'Ing. Ramón Valenzuela', 'activo' => true]);
        Ubicacion::create(['nombre' => 'Anexo La Victoria (recepción)', 'clave_upp' => '26-030-0452', 'municipio' => 'Hermosillo', 'estado' => 'Sonora', 'responsable' => 'Jesús Moreno', 'activo' => true]);

        // 10 semanas de precios de mercado (tendencia al alza)
        foreach (range(9, 0) as $i => $semana) {
            $fecha = $this->dia($semana * 7);
            foreach (['General' => 54, 'Torete' => 56, 'Becerro' => 66, 'Vaquilla' => 50, 'Becerra' => 60] as $cat => $base) {
                PrecioMercado::create(['fecha' => $fecha, 'categoria' => $cat, 'precio_kg' => $base + $i * 0.35 + $this->azar(-0.4, 0.4),
                    'fuente' => 'Unión Ganadera Regional de Sonora (referencia)', 'user_id' => $this->admin?->id]);
            }
        }
    }

    private function proveedoresYCorrales(): void
    {
        $ranchos = [
            ['Rancho Las Palmas', 'José Luis Durazo', 'Ures'], ['Ganadera El Sahuaro', 'María Elena Bustamante', 'Carbó'],
            ['Rancho San Isidro', 'Francisco Encinas', 'Mazatán'], ['Agropecuaria La Cueva', 'Héctor Ruiz', 'San Pedro de la Cueva'],
            ['Rancho El Bellotal', 'Rosario Félix', 'Moctezuma'], ['Rancho Los Álamos', 'Ramiro Acuña', 'Cumpas'],
            ['Ganadería Río Sonora', 'Guadalupe Ochoa', 'Baviácora'], ['Rancho La Esperanza', 'Martín Valenzuela', 'Arizpe'],
            ['Rancho El Pinito', 'Adrián Romero', 'Bacoachi'], ['Agrícola y Ganadera Sahuaripa', 'Lucía Montaño', 'Sahuaripa'],
        ];
        foreach ($ranchos as $i => [$nombre, $contacto, $lugar]) {
            ProveedorGanado::create(['nombreProveedor' => $nombre, 'nombreContacto' => $contacto, 'telefono' => '662' . mt_rand(1000000, 9999999),
                'lugar' => "{$lugar}, Sonora", 'razon_social' => $i % 3 === 0 ? "{$nombre} SPR de RL" : null, 'estado' => 'activo']);
        }

        $ubicacion = Ubicacion::first();
        $defs = [
            ['C-01', 'Recepción Norte', 'Recepcion'], ['C-02', 'Recepción Sur', 'Recepcion'], ['C-03', 'Enfermería y cuarentena', 'Recepcion'],
            ['C-04', 'Desarrollo 1', 'Crecimiento'], ['C-05', 'Desarrollo 2', 'Crecimiento'], ['C-06', 'Desarrollo 3', 'Crecimiento'], ['C-07', 'Desarrollo 4', 'Crecimiento'],
            ['C-08', 'Finalización A', 'Finalizacion'], ['C-09', 'Finalización B', 'Finalizacion'], ['C-10', 'Finalización C', 'Finalizacion'],
        ];
        foreach ($defs as [$codigo, $nombre, $fase]) {
            $this->corrales[$codigo] = CorralLote::create([
                'codigo' => $codigo, 'nombre' => $nombre, 'fase_engorda' => $fase, 'ubicacion_id' => $ubicacion?->id,
                'capacidad_maxima' => $fase === 'Finalizacion' ? 40 : 30, 'peso_promedio_objetivo' => $fase === 'Finalizacion' ? 520 : 400,
                'meta_gdp' => ['Recepcion' => 1.0, 'Crecimiento' => 1.45, 'Finalizacion' => 1.6][$fase], 'meta_costo_kg' => 42,
                'meta_mortalidad_pct' => 1.5, 'meta_conversion' => 6.8, 'presupuesto_mxn' => 450000,
                'fecha_inicio_lote' => $this->dia(100), 'fecha_proyectada_salida' => $this->hoy->copy()->addDays(60)->toDateString(), 'estado' => 'Activo',
            ]);
        }
    }

    private function clientes(): void
    {
        $lista = [
            ['Empacadora de Carnes del Desierto', 'EMPACADORA DE CARNES DEL DESIERTO SA DE CV', 'ECD150312AB4', '601', 500000, 30],
            ['Frigorífico Hermosillo', 'FRIGORIFICO HERMOSILLO SA DE CV', 'FHE980722KL1', '601', 800000, 21],
            ['Carnes Selectas Pitic', 'CARNES SELECTAS PITIC S DE RL DE CV', 'CSP110905TR8', '601', 300000, 15],
            ['Exportadora Ganadera del Noroeste', 'EXPORTADORA GANADERA DEL NOROESTE SA DE CV', 'EGN070119MN2', '601', 1000000, 30],
            ['Carnicería La Sonorense', null, 'SOGJ7805146H3', '612', 80000, 7],
            ['Rastro TIF Santa Ana', 'RASTRO TIF SANTA ANA SA DE CV', 'RTS030627QW5', '601', 600000, 15],
            ['Distribuidora de Carnes Río Sonora', 'DISTRIBUIDORA DE CARNES RIO SONORA SA DE CV', 'DCR190404PL9', '601', 250000, 15],
            ['Comercializadora El Vaquero', 'COMERCIALIZADORA EL VAQUERO SA DE CV', 'CVA120830ZX3', '601', 200000, 0],
            ['Taquerías El Torito (compras)', null, 'TOMA820113DF7', '612', 0, 0],
            ['Engordadora Valle del Yaqui', 'ENGORDADORA VALLE DEL YAQUI SPR DE RL', 'EVY091112HG6', '601', 400000, 30],
        ];
        foreach ($lista as [$nombre, $razon, $rfc, $regimen, $limite, $dias]) {
            Cliente::create(['nombre' => $nombre, 'razon_social' => $razon, 'rfc' => $rfc, 'regimen_fiscal' => $regimen, 'uso_cfdi' => 'G01',
                'codigo_postal' => (string) mt_rand(83000, 83299), 'telefono' => '662' . mt_rand(1000000, 9999999),
                'contacto' => 'Compras', 'limite_credito' => $limite, 'dias_credito' => $dias, 'activo' => true]);
        }
    }

    // ───────────────────────── Bodega y dietas ─────────────────────────

    private function insumosYDietas(): void
    {
        // clave => [código, nombre, tipo, unidad, % MS, kg por unidad, presentación, stock mínimo, días de retiro]
        $defs = [
            'maiz'   => ['MAIZ-ROL', 'Maíz rolado', 'alimento', 'ton', 88, 1000, 'Granel', 5, null],
            'sorgo'  => ['SORGO-MOL', 'Sorgo molido', 'alimento', 'ton', 88, 1000, 'Granel', 3, null],
            'soya'   => ['PASTA-SOYA', 'Pasta de soya 46 %', 'alimento', 'kg', 89, 1, 'Saco 40 kg', 800, null],
            'ddgs'   => ['DDGS', 'Granos secos de destilería (DDGS)', 'alimento', 'kg', 90, 1, 'Granel', 1000, null],
            'heno'   => ['HENO-ALF', 'Heno de alfalfa', 'alimento', 'kg', 90, 1, 'Paca 25 kg', 1500, null],
            'rastrojo' => ['RASTROJO', 'Rastrojo de maíz molido', 'alimento', 'kg', 85, 1, 'Paca 20 kg', 1000, null],
            'melaza' => ['MELAZA', 'Melaza de caña', 'alimento', 'l', 75, 1.4, 'Tambo 200 l', 300, null],
            'sal'    => ['SAL', 'Sal común', 'alimento', 'saco', 99, 25, 'Saco 25 kg', 4, null],
            'premezcla' => ['PREMEZCLA', 'Premezcla mineral engorda', 'alimento', 'kg', 98, 1, 'Saco 25 kg', 150, null],
            'agua'   => ['AGUA-POZO', 'Agua de pozo', 'otro', 'l', 0, 1, null, 0, null],
            // medicamentos
            'ivermec'  => ['IVERMEC-1', 'Ivermectina 1 %', 'medicamento', 'ml', null, 1, 'Frasco 500 ml', 500, 35],
            'oxitet'   => ['OXITET-LA', 'Oxitetraciclina LA 20 %', 'medicamento', 'ml', null, 1, 'Frasco 250 ml', 250, 28],
            'florfen'  => ['FLORFEN', 'Florfenicol 30 %', 'medicamento', 'ml', null, 1, 'Frasco 250 ml', 250, 38],
            'tilmico'  => ['TILMICOSINA', 'Tilmicosina 30 %', 'medicamento', 'ml', null, 1, 'Frasco 100 ml', 100, 42],
            'meloxi'   => ['MELOXICAM', 'Meloxicam 2 %', 'medicamento', 'ml', null, 1, 'Frasco 100 ml', 100, 15],
            'vitade'   => ['VIT-ADE', 'Vitaminas A, D, E', 'medicamento', 'ml', null, 1, 'Frasco 500 ml', 500, 0],
            'vacresp'  => ['VAC-RESP', 'Vacuna respiratoria (IBR/BVD/PI3/BRSV)', 'medicamento', 'dosis', null, 1, 'Frasco 50 dosis', 50, 21],
            'vacclos'  => ['VAC-CLOS', 'Bacterina clostridial 7 vías', 'medicamento', 'dosis', null, 1, 'Frasco 50 dosis', 50, 21],
            'albenda'  => ['ALBENDAZOL', 'Albendazol 10 % oral', 'medicamento', 'ml', null, 1, 'Galón 3.8 l', 1000, 27],
            'implante' => ['IMPLANTE-TE', 'Implante anabólico (TE/E2)', 'medicamento', 'pieza', null, 1, 'Caja 100 piezas', 20, 0],
        ];
        foreach ($defs as $clave => [$codigo, $nombre, $tipo, $unidad, $ms, $factor, $pres, $minimo, $retiro]) {
            $this->ins[$clave] = Insumo::create([
                'codigo' => $codigo, 'nombre' => $nombre, 'tipo' => $tipo, 'unidad' => $unidad, 'materia_seca_pct' => $ms,
                'factor_kg' => $factor, 'controla_existencia' => $clave !== 'agua', 'presentacion' => $pres,
                'stock_minimo' => $minimo, 'dias_retiro' => $retiro, 'activo' => true, 'existencia' => 0, 'costo_promedio' => 0,
            ]);
        }

        // Dietas: [código, nombre, fase, proteína, energía, receta en % ó en cantidades]
        $dietas = [
            ['DT-REC-01', 'Recepción fibra larga', 'Recepcion', 14, 2.5, ['heno' => 40, 'rastrojo' => 15, 'maiz' => 25, 'soya' => 12, 'melaza' => 5, 'premezcla' => 3]],
            ['DT-REC-02', 'Recepción con sorgo', 'Recepcion', 14, 2.55, ['heno' => 35, 'rastrojo' => 15, 'sorgo' => 30, 'soya' => 12, 'melaza' => 5, 'premezcla' => 3]],
            ['DT-CRE-01', 'Desarrollo 55 % grano', 'Crecimiento', 13.5, 2.75, ['maiz' => 45, 'sorgo' => 10, 'heno' => 20, 'ddgs' => 13, 'melaza' => 7, 'premezcla' => 3, 'sal' => 2]],
            ['DT-CRE-02', 'Desarrollo con DDGS', 'Crecimiento', 14, 2.8, ['maiz' => 40, 'ddgs' => 22, 'heno' => 18, 'rastrojo' => 8, 'melaza' => 8, 'premezcla' => 3, 'sal' => 1]],
            ['DT-CRE-03', 'Desarrollo económico sorgo', 'Crecimiento', 13, 2.7, ['sorgo' => 50, 'heno' => 20, 'rastrojo' => 10, 'soya' => 10, 'melaza' => 7, 'premezcla' => 3]],
            ['DT-FIN-01', 'Finalización alta energía', 'Finalizacion', 12.5, 3.05, ['maiz' => 68, 'ddgs' => 12, 'heno' => 8, 'melaza' => 8, 'premezcla' => 3, 'sal' => 1]],
            ['DT-FIN-02', 'Finalización maíz-sorgo', 'Finalizacion', 12.5, 3.0, ['maiz' => 45, 'sorgo' => 23, 'ddgs' => 12, 'heno' => 9, 'melaza' => 8, 'premezcla' => 3]],
            ['DT-FIN-03', 'Finalización 90 días', 'Finalizacion', 12, 3.1, ['maiz' => 72, 'soya' => 6, 'rastrojo' => 9, 'melaza' => 9, 'premezcla' => 3, 'sal' => 1]],
            ['DT-MAN-01', 'Mantenimiento enfermería', 'Mantenimiento', 12, 2.3, ['heno' => 60, 'rastrojo' => 20, 'maiz' => 12, 'melaza' => 5, 'premezcla' => 3]],
            // Capturada por cantidades, como en la libreta
            ['DT-TER-01', 'Comida engorda ternero', 'Recepcion', 15, 2.6, ['cantidades' => [
                ['maiz', 20, 'kg'], ['soya', 6, 'kg'], ['heno', 10, 'kg'], ['melaza', 2, 'l'], ['sal', 1, 'lb'], ['agua', 4, 'l'],
            ]]],
        ];
        foreach ($dietas as [$codigo, $nombre, $fase, $pc, $em, $receta]) {
            [$min, $max, $sug] = RacionAlimento::RANGOS_PV[$fase];
            $racion = RacionAlimento::create([
                'codigo' => $codigo, 'nombre' => $nombre, 'fase' => $fase, 'costo_por_kg' => 1, 'proteina_cruda_pct' => $pc,
                'energia_metabolizable_mcal' => $em, 'materia_seca_pct' => 85, 'activo' => true,
                'base_consumo' => 'MS', 'pct_pv_min' => $min, 'pct_pv_max' => $max, 'pct_pv_sugerido' => $sug,
            ]);

            if (isset($receta['cantidades'])) {
                $kg = collect($receta['cantidades'])->map(fn ($r) => $this->ins[$r[0]]->kgDe($r[1], $r[2]));
                foreach ($receta['cantidades'] as $i => [$clave, $cant, $unidad]) {
                    RacionIngrediente::create(['racion_id' => $racion->id, 'insumo_id' => $this->ins[$clave]->id,
                        'porcentaje' => round($kg[$i] / $kg->sum() * 100, 3), 'cantidad' => $cant, 'unidad_captura' => $unidad]);
                }
            } else {
                foreach ($receta as $clave => $pct) {
                    RacionIngrediente::create(['racion_id' => $racion->id, 'insumo_id' => $this->ins[$clave]->id, 'porcentaje' => $pct]);
                }
            }
            $racion->update(['materia_seca_pct' => $racion->fresh()->materiaSecaCalculada() ?? 85]);
            $this->dietas[$codigo] = $racion;
        }
    }

    // ───────────────────────── Compras y animales ─────────────────────────

    /** Días desde la compra de cada guía (de la más antigua a la más reciente). */
    private const GUIAS_DIAS = [98, 90, 82, 74, 64, 55, 46, 37, 26, 12];

    private function comprasYAnimales(): void
    {
        $proveedores = ProveedorGanado::orderBy('id')->get();
        $razas = ['Charolais x Cebú', 'Angus x Brahman', 'Beefmaster', 'Simmental x Cebú', 'Brangus', 'Hereford x Cebú'];
        $consecutivo = 1;

        foreach (self::GUIAS_DIAS as $g => $hace) {
            $prov = $proveedores[$g];
            $categoria = [0 => 'Torete', 1 => 'Torete', 2 => 'Becerro', 3 => 'Torete', 4 => 'Vaquilla', 5 => 'Torete', 6 => 'Becerro', 7 => 'Torete', 8 => 'Vaquilla', 9 => 'Becerro'][$g];
            [$pMin, $pMax, $precio] = ['Torete' => [255, 320, 64], 'Becerro' => [185, 235, 72], 'Vaquilla' => [220, 275, 58]][$categoria];

            $factura = FacturaGanado::create([
                'proveedorID' => $prov->id, 'fechaFactura' => $this->dia($hace), 'numeroFactura' => 'GC-2026-' . str_pad((string) ($g + 1), 3, '0', STR_PAD_LEFT),
                'estado' => 'pendiente', 'costo_flete' => mt_rand(80, 160) * 100, 'costo_tramites' => 1850, 'otros_costos' => mt_rand(0, 1) * 1200,
                'guia_reemo' => 'RM-26-' . mt_rand(100000, 999999), 'upp_origen' => '26-0' . mt_rand(10, 70) . '-' . mt_rand(1000, 9999),
                'notas' => "Lote de {$categoria}s de {$prov->lugar}",
            ]);

            $animales = [];
            for ($a = 0; $a < 10; $a++, $consecutivo++) {
                $peso = $this->azar($pMin, $pMax, 0);
                $animales[] = [
                    'arete' => '26' . str_pad((string) (40210000 + $consecutivo * 7), 8, '0', STR_PAD_LEFT), 'categoria' => $categoria,
                    'raza' => $razas[($g + $a) % count($razas)], 'peso' => $peso, 'precio' => $precio + $this->azar(-2, 2),
                    'peso_origen' => round($peso * $this->azar(1.03, 1.055, 3), 0), 'arete_siniiga' => '26' . mt_rand(10000000, 99999999),
                    'fierro' => strtoupper(substr($prov->nombreProveedor, 7, 2)) . '-' . ($g + 1),
                ];
            }
            $valor = collect($animales)->sum(fn ($x) => $x['peso'] * $x['precio']);
            $factura->update(['costo_comision' => round($valor * 0.015, 2), 'peso_origen_kg' => collect($animales)->sum('peso_origen')]);

            $ganados = app(GanadoService::class)->registrarLote($factura->id, $this->dia($hace), $animales);

            // Ruta de corrales según los días en engorda: recepción → desarrollo (día 21) → finalización (día 56)
            $recepcion = $this->corrales['C-0' . (1 + $g % 2)];
            $desarrollo = $this->corrales['C-0' . (4 + $g % 4)];
            $final = $this->corrales['C-' . str_pad((string) (8 + $g % 3), 2, '0', STR_PAD_LEFT)];
            foreach ($ganados as $i => $ganado) {
                $ganado->forceFill(['procedencia' => $prov->lugar, 'upp_origen' => $factura->upp_origen,
                    'denticion' => $categoria === 'Becerro' ? 'Dientes de leche' : '2 dientes', 'color_senas' => ['Bayo', 'Colorado', 'Pinto', 'Negro', 'Blanco'][$i % 5]])->save();
                MovimientoCorral::mover($ganado, $g === 9 ? $this->corrales['C-0' . (1 + $i % 2)] : $recepcion, 'Llegada de la compra ' . $factura->numeroFactura, $this->dia($hace));
                if ($hace > 21) {
                    MovimientoCorral::mover($ganado->fresh(), $desarrollo, 'Cambio de fase: desarrollo', $this->dia($hace - 21));
                }
                if ($hace > 56) {
                    MovimientoCorral::mover($ganado->fresh(), $final, 'Cambio de fase: finalización', $this->dia($hace - 56));
                }
            }
        }
    }

    private function pesajes(): void
    {
        $servicio = app(PesajeService::class);
        foreach (Ganado::orderBy('GanadoID')->get() as $ganado) {
            $hace = (int) Carbon::parse($ganado->fechaCompra)->diffInDays($this->hoy);
            $guia = array_search($hace, self::GUIAS_DIAS, true);
            $peso = (float) $ganado->pesoActual;
            $potencial = $this->azar(0.85, 1.15); // cada animal gana distinto

            foreach ([21, 42, 63, 84] as $diaPesaje) {
                // La guía 4 dejó de pesarse (para ver pesos «estimados») y la última aún no llega al primer pesaje
                if ($diaPesaje >= $hace || ($guia === 4 && $diaPesaje > 21)) {
                    break;
                }
                $gdp = ($diaPesaje <= 21 ? 1.0 : ($diaPesaje <= 56 ? 1.45 : 1.6)) * $potencial;
                $peso = round($peso + $gdp * 21 + $this->azar(-4, 4), 1);
                $servicio->registrarPesaje($ganado->GanadoID, $peso, $this->dia($hace - $diaPesaje), 'Báscula ganadera 1', $ganado->lote_potrero,
                    null, $this->admin?->id, $this->azar(2.75, 3.75, 1));
            }
        }
    }

    // ───────────────────────── Alimentación ─────────────────────────

    private function alimentacion(): void
    {
        $asignar = [
            'C-01' => 'DT-REC-01', 'C-02' => 'DT-REC-02', 'C-03' => 'DT-MAN-01',
            'C-04' => 'DT-CRE-01', 'C-05' => 'DT-CRE-02', 'C-06' => 'DT-CRE-01', 'C-07' => 'DT-CRE-03',
            'C-08' => 'DT-FIN-01', 'C-09' => 'DT-FIN-02', 'C-10' => 'DT-FIN-03',
        ];
        foreach ($asignar as $codigo => $dieta) {
            $corral = $this->corrales[$codigo];
            // Historial: los corrales de finalización empezaron con otra dieta
            if (str_starts_with($dieta, 'DT-FIN')) {
                AsignacionDieta::create(['corral_lote_id' => $corral->id, 'racion_id' => $this->dietas['DT-CRE-01']->id,
                    'desde' => $this->dia(45), 'hasta' => $this->dia(31), 'notas' => 'Transición a finalización', 'user_id' => $this->admin?->id]);
            }
            AsignacionDieta::create(['corral_lote_id' => $corral->id, 'racion_id' => $this->dietas[$dieta]->id,
                'pct_pv' => $codigo === 'C-10' ? 3.0 : null, 'desde' => $this->dia(str_starts_with($dieta, 'DT-FIN') ? 30 : 60),
                'notas' => $codigo === 'C-10' ? 'Indicación del nutriólogo: subir consumo la última fase' : null, 'user_id' => $this->admin?->id]);
        }

        // Requerimiento diario según peso de los animales → compras sizing (algunas a propósito cortas para ver el semáforo)
        $corrales = CorralLote::whereIn('id', collect($this->corrales)->pluck('id'))->get();
        $plan = app(ConsumoRecomendadoService::class)->plan($corrales, 1);
        $diasCompra = ['premezcla' => 41, 'ddgs' => 45.6, 'soya' => 54];
        $precios = ['maiz' => 6.9, 'sorgo' => 5.8, 'soya' => 11.2, 'ddgs' => 6.1, 'heno' => 5.4, 'rastrojo' => 2.6, 'melaza' => 4.9, 'sal' => 3.2, 'premezcla' => 24];
        $inventario = app(InventarioService::class);

        foreach ($this->ins as $clave => $insumo) {
            if ($insumo->tipo !== 'alimento') {
                continue;
            }
            $kgDia = (float) ($plan['bodega']->firstWhere('insumo.id', $insumo->id)['kg'] ?? 0);
            $kgCompra = max(500, $kgDia * ($diasCompra[$clave] ?? 64));
            $unidades = round($kgCompra / $insumo->kgPorUnidad(), 1);
            $costoKg = $precios[$clave];
            // Dos compras con distinto precio (el costo promedio se recalcula)
            $inventario->entrada($insumo, round($unidades * 0.45, 1), $costoKg * $insumo->kgPorUnidad() * 0.96,
                ['fecha' => $this->dia(50), 'proveedor' => 'Forrajes y Granos del Noroeste', 'referencia' => 'Factura FGN-' . mt_rand(1000, 9999)]);
            $inventario->entrada($insumo, round($unidades * 0.55, 1), $costoKg * $insumo->kgPorUnidad(),
                ['fecha' => $this->dia(25), 'proveedor' => 'Agroinsumos Pitic', 'referencia' => 'Factura AP-' . mt_rand(1000, 9999)]);
        }

        // Costo por kg de cada dieta con el costo promedio de sus ingredientes
        foreach ($this->dietas as $dieta) {
            $dieta->load('ingredientes.insumo');
            $dieta->update(['costo_por_kg' => round($dieta->ingredientes->sum(fn ($i) => $i->insumo->fresh()->costoKg() * (float) $i->porcentaje / 100), 2)]);
        }

        // Servidos de los últimos 45 días (como los registra el operario; descuentan bodega).
        // Los animales pesaban menos hace semanas: el servido se escala hacia atrás.
        $explosion = app(ExplosionMaterialesService::class);
        $corrales->load('ganadosActivos');
        foreach (range(45, 1) as $hace) {
            $fecha = $this->dia($hace);
            foreach ($plan['lineas'] as $linea) {
                $corral = $corrales->firstWhere('id', $linea['corral']->id);
                $asignacion = AsignacionDieta::where('corral_lote_id', $corral->id)->whereDate('desde', '<=', $fecha)
                    ->where(fn ($q) => $q->whereNull('hasta')->orWhereDate('hasta', '>=', $fecha))->latest('desde')->first();
                $racion = $asignacion ? $this->dietas[$asignacion->racion->codigo] ?? null : null;
                if (! $racion || $linea['kg_dia'] <= 0) {
                    continue;
                }
                $racion->refresh();
                $servidos = round($linea['kg_dia'] * (1 - 0.007 * $hace) * $this->azar(0.93, 1.06), 0);
                $rechazo = round($servidos * $this->azar(0.01, 0.05), 0);
                $consumidos = $servidos - $rechazo;
                $cabezas = max(1, $corral->ganadosActivos->count());
                $consumo = ConsumoAlimento::create([
                    'corral_lote_id' => $corral->id, 'racion_id' => $racion->id, 'fecha' => $fecha,
                    'kilos_servidos' => $servidos, 'kilos_rechazo' => $rechazo, 'kilos_consumidos' => $consumidos,
                    'costo_total_mxn' => round($consumidos * (float) $racion->costo_por_kg, 2), 'cabezas_contadas' => $cabezas,
                    'consumo_promedio_cabeza_kg' => round($consumidos / $cabezas, 2), 'user_id' => $this->admin?->id,
                    'observaciones' => $rechazo > $servidos * 0.04 ? 'Comedero con sobrante, revisar lectura de comedero' : null,
                ]);
                $explosion->descontarServido($racion, $servidos, ['fecha' => $fecha, 'origen_type' => 'ConsumoAlimento', 'origen_id' => $consumo->id,
                    'referencia' => "Consumo #{$consumo->id} · {$corral->codigo} · {$racion->codigo}", 'corral_lote_id' => $corral->id]);
            }
        }
    }

    // ───────────────────────── Sanidad ─────────────────────────

    private function sanidad(): void
    {
        $inventario = app(InventarioService::class);
        $compras = ['ivermec' => [3000, 1.8], 'oxitet' => [1500, 2.6], 'florfen' => [1000, 6.5], 'tilmico' => [500, 9.8], 'meloxi' => [400, 3.1],
            'vitade' => [2000, 0.9], 'vacresp' => [150, 38], 'vacclos' => [150, 14], 'albenda' => [3800, 0.45], 'implante' => [120, 42]];
        foreach ($compras as $clave => [$cant, $costo]) {
            $inventario->entrada($this->ins[$clave], $cant, $costo, ['fecha' => $this->dia(100), 'proveedor' => 'Veterinaria El Corral',
                'lote' => 'L' . mt_rand(2600, 2699), 'fecha_caducidad' => $this->hoy->copy()->addMonths($clave === 'vacresp' ? 1 : 14)->toDateString(),
                'referencia' => 'Factura VEC-' . mt_rand(1000, 9999)]);
        }

        // Protocolo de recepción a cada animal el día de llegada
        $aplicar = function (Ganado $g, string $clave, string $tipo, float $cantidad, string $dosis, string $fecha, ?int $evento = null, ?int $prog = null, ?string $motivo = null) use ($inventario) {
            $ins = $this->ins[$clave];
            $retiro = (int) $ins->dias_retiro;
            $tr = TratamientoSanitario::create([
                'ganado_id' => $g->GanadoID, 'evento_enfermedad_id' => $evento, 'programacion_id' => $prog, 'insumo_id' => $ins->id, 'cantidad_insumo' => $cantidad,
                'tipo_evento' => $tipo, 'medicamento_producto' => $ins->nombre, 'dosis_aplicada' => $dosis, 'via_administracion' => $clave === 'albenda' ? 'Oral' : 'Subcutánea',
                'fecha_aplicacion' => $fecha, 'dias_retiro_rastro' => $retiro, 'fecha_fin_retiro' => Carbon::parse($fecha)->addDays($retiro)->toDateString(),
                'costo_medicamento_mxn' => round($cantidad * (float) $ins->fresh()->costo_promedio, 2), 'veterinario_responsable' => 'MVZ Carlos Ibarra',
                'diagnostico_motivo' => $motivo, 'user_id' => $this->admin?->id,
            ]);
            $inventario->salida($ins, $cantidad, ['fecha' => $fecha, 'origen_type' => 'TratamientoSanitario', 'origen_id' => $tr->id,
                'referencia' => "Tratamiento #{$tr->id} · {$g->areteID}"], permitirNegativo: true);
        };

        foreach (Ganado::orderBy('GanadoID')->get() as $g) {
            $llegada = Carbon::parse($g->fechaCompra)->toDateString();
            $aplicar($g, 'vacresp', 'Recepcion', 1, '1 dosis (2 ml)', $llegada, motivo: 'Protocolo de recepción');
            $aplicar($g, 'ivermec', 'Desparasitacion', round((float) $g->pesoActual / 50, 1), round((float) $g->pesoActual / 50, 1) . ' ml', $llegada, motivo: 'Protocolo de recepción');
            $aplicar($g, 'vitade', 'Vitamina', 5, '5 ml', $llegada, motivo: 'Protocolo de recepción');
        }

        // Calendario: 10 programaciones (aplicadas, pendientes y vencidas)
        $progs = [
            ['Revacunación respiratoria', 'Vacunacion', 'C-04', 'vacresp', 30, 'aplicada'], ['Bacterina clostridial', 'Vacunacion', 'C-05', 'vacclos', 25, 'aplicada'],
            ['Implante de finalización', 'Implante', 'C-08', 'implante', 20, 'aplicada'], ['Desparasitación interna', 'Desparasitacion', 'C-06', 'albenda', 10, 'aplicada'],
            ['Bacterina clostridial', 'Vacunacion', 'C-07', 'vacclos', 3, 'pendiente'], ['Revacunación respiratoria', 'Vacunacion', 'C-01', 'vacresp', 1, 'pendiente'],
            ['Implante de finalización', 'Implante', 'C-09', 'implante', -2, 'pendiente'], ['Desparasitación interna', 'Desparasitacion', 'C-02', 'albenda', -5, 'pendiente'],
            ['Vitaminas pre-embarque', 'Vitamina', 'C-10', 'vitade', -9, 'pendiente'], ['Bacterina clostridial', 'Vacunacion', 'C-04', 'vacclos', -15, 'pendiente'],
        ];
        foreach ($progs as [$titulo, $tipo, $codigo, $clave, $hace, $estado]) {
            $corral = $this->corrales[$codigo];
            $fecha = $this->dia($hace);
            $prog = ProgramacionSanitaria::create(['titulo' => $titulo, 'tipo_evento' => $tipo, 'corral_lote_id' => $corral->id, 'insumo_id' => $this->ins[$clave]->id,
                'fecha_programada' => $fecha, 'dosis' => $clave === 'albenda' ? '1 ml / 10 kg' : '1 dosis', 'via_administracion' => $clave === 'albenda' ? 'Oral' : 'Subcutánea',
                'estado' => 'pendiente', 'user_id' => $this->admin?->id]);
            if ($estado === 'aplicada') {
                $animales = Ganado::activo()->where('corral_lote_id', $corral->id)->get();
                foreach ($animales as $g) {
                    $cant = $clave === 'albenda' ? round((float) $g->ultimoPeso / 10, 1) : 1;
                    $aplicar($g, $clave, $tipo, $cant, $clave === 'albenda' ? "{$cant} ml" : '1 dosis', $fecha, prog: $prog->id, motivo: $titulo);
                }
                $prog->update(['estado' => 'aplicada', 'aplicada_at' => Carbon::parse($fecha)->setTime(9, 0), 'cabezas_aplicadas' => $animales->count()]);
            }
        }

        // 10 casos de enfermedad (los dos últimos terminan en muerte en la sección de bajas)
        $casos = [
            ['Complejo Respiratorio Bovino (Neumonía)', 'moderada', 'florfen', 'recuperado', 8], ['Fiebre de embarque', 'leve', 'oxitet', 'recuperado', 6],
            ['Complejo Respiratorio Bovino (Neumonía)', 'grave', 'tilmico', 'cronico', 0], ['Cojera / Pododermatitis', 'leve', 'oxitet', 'recuperado', 7],
            ['Queratoconjuntivitis (Ojo rosado)', 'leve', 'oxitet', 'recuperado', 5], ['Acidosis ruminal', 'moderada', 'meloxi', 'recuperado', 4],
            ['Diarrea / Enteritis', 'leve', 'oxitet', 'en_tratamiento', 0], ['Complejo Respiratorio Bovino (Neumonía)', 'moderada', 'florfen', 'en_tratamiento', 0],
            ['Complejo Respiratorio Bovino (Neumonía)', 'grave', 'tilmico', 'murio', 0], ['Timpanismo', 'grave', 'meloxi', 'murio', 0],
        ];
        $pacientes = Ganado::activo()->inRandomOrder(7)->take(9)->get();
        foreach ($casos as $i => [$diag, $sev, $clave, $resultado, $diasAlta]) {
            $g = $i === 9 ? $pacientes[0] : $pacientes[$i % 9]; // el primero recae
            $hace = min((int) Carbon::parse($g->fechaCompra)->diffInDays($this->hoy) - 2, [20, 18, 30, 25, 14, 9, 3, 2, 12, 6][$i]);
            $inicio = $this->dia(max(1, $hace));
            $evento = EventoEnfermedad::create([
                'ganado_id' => $g->GanadoID, 'fecha_inicio' => $inicio, 'diagnostico' => $diag, 'severidad' => $sev, 'sintomas' => 'Detectado en recorrido de corrales',
                'en_enfermeria' => $sev !== 'leve', 'es_recaida' => $i === 9, 'resultado' => $resultado,
                'fecha_alta' => $diasAlta ? Carbon::parse($inicio)->addDays($diasAlta)->toDateString() : null, 'user_id' => $this->admin?->id,
            ]);
            $ml = ['florfen' => 13, 'oxitet' => 15, 'tilmico' => 10, 'meloxi' => 6][$clave];
            $aplicar($g, $clave, $clave === 'meloxi' ? 'Tratamiento_Clinico' : 'Antibiotico', $ml, "{$ml} ml", $inicio, evento: $evento->id, motivo: $diag);
            if ($sev !== 'leve') {
                MovimientoCorral::mover($g->fresh(), $this->corrales['C-03'], 'Enviado a enfermería', $inicio);
                if (in_array($resultado, ['recuperado'], true)) {
                    MovimientoCorral::mover($g->fresh(), CorralLote::find(MovimientoCorral::where('ganado_id', $g->GanadoID)->where('corral_destino_id', '!=', $this->corrales['C-03']->id)->latest('id')->value('corral_destino_id')),
                        'Alta de enfermería', Carbon::parse($inicio)->addDays($diasAlta)->toDateString());
                }
            }
        }
    }

    private function bajas(): void
    {
        $servicio = app(MortalidadService::class);
        foreach (EventoEnfermedad::where('resultado', 'murio')->get() as $evento) {
            $servicio->registrarBaja($evento->ganado_id, Carbon::parse($evento->fecha_inicio)->addDays(2)->toDateString(),
                str_contains($evento->diagnostico, 'Timpanismo') ? 'Timpanismo agudo (Meteorismo)' : 'Neumonía / Complejo Respiratorio Bovino',
                'Necropsia: lesiones compatibles con el diagnóstico', 'Caso registrado en enfermería', $this->admin?->id, 'Muerte');
        }
        $resto = Ganado::activo()->whereNotIn('corral_lote_id', [$this->corrales['C-08']->id, $this->corrales['C-09']->id, $this->corrales['C-10']->id])->inRandomOrder(3)->take(3)->get();
        $servicio->registrarBaja($resto[0]->GanadoID, $this->dia(16), 'Traumatismo / Fractura severa', null, 'Fractura de miembro posterior; se sacrificó en rastro local', $this->admin?->id, 'Sacrificio', 9500);
        $servicio->registrarBaja($resto[1]->GanadoID, $this->dia(33), 'Robo', null, 'Faltante en conteo; denuncia presentada ante Fiscalía', $this->admin?->id, 'Robo');
        $servicio->registrarBaja($resto[2]->GanadoID, $this->dia(8), 'Descarte por bajo desempeño', null, 'Vendido como desecho', $this->admin?->id, 'Descarte', 14200);
    }

    // ───────────────────────── Ventas ─────────────────────────

    private function ventas(): void
    {
        $finales = Ganado::activo()->whereIn('corral_lote_id', [$this->corrales['C-08']->id, $this->corrales['C-09']->id, $this->corrales['C-10']->id])
            ->orderByDesc('ultimoPeso')->get()->values();
        $clientes = Cliente::orderBy('id')->get();
        $servicio = app(VentaService::class);
        $cobranza = app(CobranzaService::class);

        // [cliente, cabezas, hace días, condición, precio $/kg, abonos (% del total)]
        $plan = [
            [0, 3, 40, 'credito', 55.5, [0.4]], [1, 3, 26, 'credito', 56.0, [1.0]], [3, 2, 22, 'credito', 56.8, []], [4, 2, 19, 'contado', 57.5, []],
            [5, 3, 15, 'credito', 56.2, [0.5]], [7, 2, 12, 'contado', 57.0, []], [8, 1, 9, 'contado', 58.0, []], [2, 2, 6, 'credito', 56.5, []],
            [9, 2, 4, 'credito', 55.8, []], [6, 2, 2, 'contado', 57.2, []],
        ];
        $pos = 0;
        foreach ($plan as $n => [$ci, $cabezas, $hace, $condicion, $precio, $abonos]) {
            $cliente = $clientes[$ci];
            $lote = $finales->slice($pos, $cabezas);
            $pos += $cabezas;
            if ($lote->isEmpty()) {
                break;
            }
            $venta = $servicio->registrarVenta([
                'cliente_id' => $cliente->id, 'fecha_venta' => $this->dia($hace), 'modalidad_pago' => 'por_kilo', 'condicion_pago' => $condicion,
                'forma_pago' => $condicion === 'contado' ? 'Transferencia' : null, 'dias_credito' => $condicion === 'credito' ? max(15, $cliente->dias_credito) : 0,
                'destino' => $n % 2 ? 'Rastro TIF Hermosillo' : 'Planta de sacrificio Santa Ana',
            ], $lote->map(fn ($g) => ['ganado_id' => $g->GanadoID, 'peso_venta' => round((float) $g->ultimoPeso + $this->azar(5, 25), 1), 'precio_unitario' => $precio])->values()->all(), $this->admin?->id);

            foreach ($abonos as $k => $pct) {
                $venta->refresh();
                $cobranza->registrarAbono($venta, ['fecha' => $this->dia(max(0, $hace - 7 - $k * 5)), 'monto' => round((float) $venta->total_venta * $pct, 2),
                    'forma_pago' => 'Transferencia', 'referencia' => 'SPEI ' . mt_rand(100000, 999999)]);
            }

            // Evaluación de canal del primer animal de cada venta (10 en total)
            $det = $venta->detalles()->first();
            $rend = $this->azar(58.5, 62.5);
            $caliente = round((float) $det->peso_venta * $rend / 100, 1);
            $fria = round($caliente * 0.98, 1);
            EvaluacionCanal::create([
                'ganado_id' => $det->ganado_id, 'arete_snapshot' => $det->arete_snapshot, 'folio_rastro_canal' => 'CAN-26-' . str_pad((string) ($n + 1), 4, '0', STR_PAD_LEFT),
                'fecha_faena' => $this->dia(max(0, $hace - 1)), 'planta_o_rastro' => 'Rastro TIF Hermosillo', 'peso_vivo_bascula_kg' => $det->peso_venta,
                'peso_canal_caliente_kg' => $caliente, 'rendimiento_canal_caliente_pct' => $rend, 'peso_canal_fria_kg' => $fria, 'merma_enfriamiento_pct' => 2,
                'rendimiento_canal_fria_pct' => round($fria / (float) $det->peso_venta * 100, 2),
                'marmoleo' => ['Moderado', 'Pequeno', 'Abundante', 'Ligero'][$n % 4], 'espesor_grasa_dorsal_mm' => $this->azar(5, 11),
                'area_ojo_costilla_cm2' => $this->azar(72, 92), 'color_carne' => $n === 7 ? 'Rojo_Oscuro_DFD' : 'Rojo_Cereza_Brillante',
                'color_grasa' => 'Blanco_Cremoso', 'ph_24h' => $n === 7 ? 6.1 : $this->azar(5.45, 5.7),
                'clasificacion_mexicana' => ['Selecta', 'Suprema', 'Selecta', 'Estandar'][$n % 4], 'precio_canal_por_kg_mxn' => 98,
                'valor_total_canal_mxn' => round($fria * 98, 2), 'destino_comercial' => 'Mercado nacional', 'user_id' => $this->admin?->id,
            ]);
        }
    }

    // ───────────────────────── Finanzas y otros ─────────────────────────

    private function gastos(): void
    {
        $gastos = [
            [88, 'Mano de obra', 'Nómina quincenal (6 trabajadores)', 38500, 'Transferencia', null], [73, 'Mano de obra', 'Nómina quincenal (6 trabajadores)', 38500, 'Transferencia', null],
            [58, 'Mano de obra', 'Nómina quincenal (6 trabajadores)', 38500, 'Transferencia', null], [43, 'Mano de obra', 'Nómina quincenal (6 trabajadores)', 39200, 'Transferencia', null],
            [28, 'Mano de obra', 'Nómina quincenal (6 trabajadores)', 39200, 'Transferencia', null], [13, 'Mano de obra', 'Nómina quincenal (6 trabajadores)', 39200, 'Transferencia', null],
            [60, 'Electricidad', 'Recibo CFE bimestral (bombeo y molino)', 21480, 'Transferencia', null], [30, 'Combustible', 'Diésel tractor y mezcladora', 16850, 'Tarjeta', null],
            [22, 'Mantenimiento', 'Reparación de comederos corral C-08', 7400, 'Efectivo', 'C-08'], [18, 'Honorarios veterinarios', 'Visita MVZ y necropsias', 6500, 'Transferencia', null],
            [10, 'Renta de terreno', 'Renta mensual anexo La Victoria', 15000, 'Transferencia', null], [5, 'Herramientas y equipo', 'Bebederos automáticos (2)', 12900, 'Crédito', 'C-04'],
        ];
        foreach ($gastos as $i => [$hace, $cat, $concepto, $monto, $forma, $corral]) {
            $g = GastoOperativo::create(['fecha' => $this->dia($hace), 'categoria' => $cat, 'concepto' => $concepto, 'monto' => $monto, 'forma_pago' => $forma,
                'proveedor' => $cat === 'Electricidad' ? 'CFE' : null, 'folio_cfdi' => $i % 3 === 0 ? strtoupper(substr(md5((string) $i), 0, 8)) . '-SIM' : null,
                'corral_lote_id' => $corral ? $this->corrales[$corral]->id : null, 'estado' => 'vigente', 'user_id' => $this->admin?->id]);
            if ($i === 7) {
                // Un gasto capturado dos veces que se anuló (queda en la bitácora)
                GastoOperativo::create($g->only(['fecha', 'categoria', 'concepto', 'monto', 'forma_pago', 'user_id']) + ['estado' => 'anulado', 'motivo_anulacion' => 'Capturado dos veces']);
            }
        }
    }

    private function liquidaciones(): void
    {
        $servicio = app(LiquidacionService::class);
        foreach (FacturaGanado::orderBy('fechaFactura')->take(6)->get() as $f) {
            $servicio->liquidarFactura($f);
        }
    }

    private function usuarios(): void
    {
        $clave = self::CLAVE_USUARIOS;
        foreach ([['dueno', 'Don Arturo Valenzuela'], ['gerente', 'Lic. Patricia Moreno'], ['supervisor', 'Ing. Ramón Encinas'],
            ['operario', 'Juan Carlos Romero'], ['vendedor', 'Sofía Durazo'], ['comprador', 'Miguel Ángel Ruiz']] as [$rol, $nombre]) {
            $u = User::firstOrCreate(['email' => "demo.{$rol}@simulacion.local"], ['name' => $nombre, 'password' => Hash::make($clave),
                'must_change_password' => false, 'status' => User::ESTADO_ACTIVO, 'last_division' => 'ganaderia']);
            $u->syncRoles([$rol]);
            $u->givePermissionTo('planta.ganaderia');
        }
        $this->command?->warn("  Usuarios de prueba: demo.<rol>@simulacion.local · clave: {$clave}  (roles: dueno, gerente, supervisor, operario, vendedor, comprador)");
    }

    private function preguntas(): void
    {
        $operario = User::where('email', 'demo.operario@simulacion.local')->first();
        $vendedor = User::where('email', 'demo.vendedor@simulacion.local')->first();
        if (! $operario || ! $vendedor) {
            return;
        }
        PreguntaAyuda::create(['user_id' => $operario->id, 'planta' => 'ganaderia', 'ruta' => 'nutricion.planeacion', 'pantalla' => 'Plan de alimentación',
            'pregunta' => '¿Por qué el corral C-10 dice que necesita más alimento que el C-08 si tienen casi los mismos animales?', 'estado' => 'respondida',
            'respuesta' => 'Porque al C-10 se le subió el consumo a 3.0 % del peso vivo por indicación del nutriólogo (el C-08 usa 2.8 %). Lo puedes ver en «Dieta y consumo» del corral.',
            'respondida_por' => $this->admin?->id, 'respondida_at' => now()->subDay()]);
        PreguntaAyuda::create(['user_id' => $operario->id, 'planta' => 'ganaderia', 'ruta' => 'raciones.formula', 'pantalla' => 'Receta de la dieta (BOM)',
            'pregunta' => 'Si la melaza la compro en tambos de 200 litros, ¿cómo la pongo en la receta?', 'estado' => 'pendiente']);
        PreguntaAyuda::create(['user_id' => $vendedor->id, 'planta' => 'ganaderia', 'ruta' => 'cobranza.index', 'pantalla' => 'Cobranza',
            'pregunta' => 'La venta a Empacadora del Desierto aparece vencida, ¿cómo registro que pagó la mitad hoy?', 'estado' => 'pendiente']);
    }
}
