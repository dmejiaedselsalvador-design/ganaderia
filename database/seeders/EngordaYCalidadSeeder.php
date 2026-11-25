<?php

namespace Database\Seeders;

use App\Models\Calidad\EvaluacionCanal;
use App\Models\Engorda\ConsumoAlimento;
use App\Models\Engorda\CorralLote;
use App\Models\Engorda\RacionAlimento;
use App\Models\Ganado\Ganado;
use App\Models\Ganado\PesajeGanado;
use App\Models\Sanidad\TratamientoSanitario;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EngordaYCalidadSeeder extends Seeder
{
    public function run(): void
    {
        // Datos de prueba: bloqueados en producción
        if (app()->isProduction()) {
            $this->command?->error(static::class . ' está bloqueado en producción.');

            return;
        }

        $admin = User::first();
        $adminId = $admin?->id ?? 1;

        // 1. DIETAS TÉCNICAS DE ENGORDA
        $racionRecepcion = RacionAlimento::firstOrCreate(
            ['codigo' => 'RAC-REC-01'],
            [
                'nombre'                     => 'Dieta Fase 1: Recepción y Adaptación (Fibra Larga 35%)',
                'fase'                       => 'Recepcion',
                'costo_por_kg'               => 6.50, // $ MXN/kg
                'materia_seca_pct'           => 88.00,
                'proteina_cruda_pct'         => 14.50,
                'energia_metabolizable_mcal' => 2.65,
                'descripcion_ingredientes'   => 'Heno de avena/alfalfa (35%), maíz rolado (40%), pasta de soya (15%), melaza (6%), premezcla mineral y buffer (4%).',
                'activo'                     => true,
            ]
        );

        $racionCrecimiento = RacionAlimento::firstOrCreate(
            ['codigo' => 'RAC-CRE-02'],
            [
                'nombre'                     => 'Dieta Fase 2: Crecimiento y Transición (55% Grano)',
                'fase'                       => 'Crecimiento',
                'costo_por_kg'               => 7.20, // $ MXN/kg
                'materia_seca_pct'           => 89.00,
                'proteina_cruda_pct'         => 13.50,
                'energia_metabolizable_mcal' => 2.85,
                'descripcion_ingredientes'   => 'Maíz quebrado (55%), heno forrajero (20%), grano de destilería DDGS (15%), melaza (6%), sales minerales y urea (4%).',
                'activo'                     => true,
            ]
        );

        $racionFinalizacion = RacionAlimento::firstOrCreate(
            ['codigo' => 'RAC-FIN-03'],
            [
                'nombre'                     => 'Dieta Fase 3: Finalización Alta Energía (80% Grano/Melaza)',
                'fase'                       => 'Finalizacion',
                'costo_por_kg'               => 7.85, // $ MXN/kg
                'materia_seca_pct'           => 90.00,
                'proteina_cruda_pct'         => 12.80,
                'energia_metabolizable_mcal' => 3.10,
                'descripcion_ingredientes'   => 'Maíz amarillo quebrado (70%), DDGS (12%), pollinaza pasteurizada (8%), melaza de caña (6%), grasa de sobrepaso y minerales (4%).',
                'activo'                     => true,
            ]
        );

        // 2. CORRALES Y LOTES DE ENGORDA (FEEDLOT PENS)
        $corral1 = CorralLote::firstOrCreate(
            ['codigo' => 'COR-01'],
            [
                'nombre'                  => 'Corral 1 - Novillos Cruzados Charolais x Cebú',
                'fase_engorda'            => 'Finalizacion',
                'capacidad_maxima'        => 30,
                'peso_promedio_objetivo'  => 490.00,
                'fecha_inicio_lote'       => Carbon::now()->subDays(85),
                'fecha_proyectada_salida' => Carbon::now()->addDays(15),
                'estado'                  => 'Activo',
                'observaciones'           => 'Lote pesado con excelente engrasamiento dorsal y marmoleo avanzado.',
            ]
        );

        $corral2 = CorralLote::firstOrCreate(
            ['codigo' => 'COR-02'],
            [
                'nombre'                  => 'Corral 2 - Toretes Angus x Beefmaster',
                'fase_engorda'            => 'Crecimiento',
                'capacidad_maxima'        => 35,
                'peso_promedio_objetivo'  => 480.00,
                'fecha_inicio_lote'       => Carbon::now()->subDays(45),
                'fecha_proyectada_salida' => Carbon::now()->addDays(55),
                'estado'                  => 'Activo',
                'observaciones'           => 'Cruzas cárnicas con alta velocidad de crecimiento (GDP meta >1.6 kg/d).',
            ]
        );

        $corral3 = CorralLote::firstOrCreate(
            ['codigo' => 'COR-03'],
            [
                'nombre'                  => 'Corral 3 - Recepción y Cuarentena Norte',
                'fase_engorda'            => 'Recepcion',
                'capacidad_maxima'        => 25,
                'peso_promedio_objetivo'  => 460.00,
                'fecha_inicio_lote'       => Carbon::now()->subDays(10),
                'fecha_proyectada_salida' => Carbon::now()->addDays(90),
                'estado'                  => 'Activo',
                'observaciones'           => 'Lote recién ingresado bajo protocolo de vacunación y buffer ruminal.',
            ]
        );

        // 3. ANIMALES DE ENGORDA REPRESENTATIVOS
        $animalesSeed = [
            // Corral 1 - Finalización
            [
                'areteID'          => 'MX-2601-8841',
                'categoria'        => 'Torete',
                'sexo'             => 'Macho',
                'raza'             => 'Charolais x Cebú',
                'corral_lote_id'   => $corral1->id,
                'lote_potrero'     => 'COR-01',
                'pesoActual'       => 260.00,
                'ultimoPeso'       => 492.00,
                'peso_objetivo'    => 490.00,
                'precioCompra'     => 58.50, // $/kg MXN
                'fechaCompra'      => Carbon::now()->subDays(85),
                'status'           => 'Activo',
                'condicion_corporal' => 7.5,
                'dias_en_engorda'  => 85,
            ],
            [
                'areteID'          => 'MX-2601-8842',
                'categoria'        => 'Torete',
                'sexo'             => 'Macho',
                'raza'             => 'Angus Negro x Cebú',
                'corral_lote_id'   => $corral1->id,
                'lote_potrero'     => 'COR-01',
                'pesoActual'       => 270.00,
                'ultimoPeso'       => 498.50,
                'peso_objetivo'    => 490.00,
                'precioCompra'     => 60.00,
                'fechaCompra'      => Carbon::now()->subDays(85),
                'status'           => 'Activo',
                'condicion_corporal' => 8.0,
                'dias_en_engorda'  => 85,
            ],
            [
                'areteID'          => 'MX-2601-8843',
                'categoria'        => 'Torete',
                'sexo'             => 'Macho',
                'raza'             => 'Brangus Rojo',
                'corral_lote_id'   => $corral1->id,
                'lote_potrero'     => 'COR-01',
                'pesoActual'       => 255.00,
                'ultimoPeso'       => 488.00,
                'peso_objetivo'    => 490.00,
                'precioCompra'     => 59.00,
                'fechaCompra'      => Carbon::now()->subDays(85),
                'status'           => 'Activo',
                'condicion_corporal' => 7.0,
                'dias_en_engorda'  => 85,
            ],
            // Corral 2 - Crecimiento
            [
                'areteID'          => 'MX-2602-9101',
                'categoria'        => 'Becerro',
                'sexo'             => 'Macho',
                'raza'             => 'Simmental x Brahman',
                'corral_lote_id'   => $corral2->id,
                'lote_potrero'     => 'COR-02',
                'pesoActual'       => 230.00,
                'ultimoPeso'       => 365.00,
                'peso_objetivo'    => 480.00,
                'precioCompra'     => 61.00,
                'fechaCompra'      => Carbon::now()->subDays(45),
                'status'           => 'Activo',
                'condicion_corporal' => 6.0,
                'dias_en_engorda'  => 45,
            ],
            [
                'areteID'          => 'MX-2602-9102',
                'categoria'        => 'Becerro',
                'sexo'             => 'Macho',
                'raza'             => 'Beefmaster Puro',
                'corral_lote_id'   => $corral2->id,
                'lote_potrero'     => 'COR-02',
                'pesoActual'       => 240.00,
                'ultimoPeso'       => 380.00,
                'peso_objetivo'    => 480.00,
                'precioCompra'     => 62.50,
                'fechaCompra'      => Carbon::now()->subDays(45),
                'status'           => 'Activo',
                'condicion_corporal' => 6.5,
                'dias_en_engorda'  => 45,
            ],
            // Corral 3 - Recepción
            [
                'areteID'          => 'MX-2603-9520',
                'categoria'        => 'Becerro',
                'sexo'             => 'Macho',
                'raza'             => 'Suizo Europeo x Cebú',
                'corral_lote_id'   => $corral3->id,
                'lote_potrero'     => 'COR-03',
                'pesoActual'       => 210.00,
                'ultimoPeso'       => 225.00,
                'peso_objetivo'    => 460.00,
                'precioCompra'     => 64.00,
                'fechaCompra'      => Carbon::now()->subDays(10),
                'status'           => 'Activo',
                'condicion_corporal' => 5.0,
                'dias_en_engorda'  => 10,
            ],
            [
                'areteID'          => 'MX-2603-9521',
                'categoria'        => 'Becerro',
                'sexo'             => 'Macho',
                'raza'             => 'Cruzado Comercial',
                'corral_lote_id'   => $corral3->id,
                'lote_potrero'     => 'COR-03',
                'pesoActual'       => 205.00,
                'ultimoPeso'       => 219.00,
                'peso_objetivo'    => 460.00,
                'precioCompra'     => 63.50,
                'fechaCompra'      => Carbon::now()->subDays(10),
                'status'           => 'Activo',
                'condicion_corporal' => 5.0,
                'dias_en_engorda'  => 10,
            ],
        ];

        foreach ($animalesSeed as $item) {
            $bovino = Ganado::firstOrCreate(
                ['areteID' => $item['areteID']],
                $item
            );

            // Crear pesajes históricos
            if ($bovino->pesajes()->count() === 0) {
                // Pesaje inicial
                PesajeGanado::create([
                    'ganado_id'          => $bovino->GanadoID,
                    'peso'               => $bovino->pesoActual,
                    'fecha_pesaje'       => $bovino->fechaCompra,
                    'peso_anterior'      => null,
                    'ganancia_peso'      => 0,
                    'dias_transcurridos' => 0,
                    'gdp'                => 0,
                    'bascula'            => 'Báscula Báscula Recepción',
                    'lote_corral'        => $bovino->lote_potrero,
                    'observaciones'      => 'Pesaje de ingreso tras viaje de proveedor.',
                    'user_id'            => $adminId,
                ]);

                // Pesaje intermedio si tiene más de 30 días
                if ($bovino->dias_en_engorda >= 40) {
                    $pesoIntermedio = round($bovino->pesoActual + (($bovino->ultimoPeso - $bovino->pesoActual) * 0.55), 2);
                    $diasIntermedios = (int) round($bovino->dias_en_engorda / 2);
                    $gdp1 = round(($pesoIntermedio - $bovino->pesoActual) / $diasIntermedios, 3);

                    PesajeGanado::create([
                        'ganado_id'          => $bovino->GanadoID,
                        'peso'               => $pesoIntermedio,
                        'fecha_pesaje'       => Carbon::parse($bovino->fechaCompra)->addDays($diasIntermedios),
                        'peso_anterior'      => $bovino->pesoActual,
                        'ganancia_peso'      => round($pesoIntermedio - $bovino->pesoActual, 2),
                        'dias_transcurridos' => $diasIntermedios,
                        'gdp'                => $gdp1,
                        'bascula'            => 'Báscula Corral',
                        'lote_corral'        => $bovino->lote_potrero,
                        'observaciones'      => 'Pesaje de control mensual de lote.',
                        'user_id'            => $adminId,
                    ]);
                }

                // Último pesaje reciente
                $diasFinal = (int) $bovino->dias_en_engorda;
                $gdpTotal = $diasFinal > 0 ? round(($bovino->ultimoPeso - $bovino->pesoActual) / $diasFinal, 3) : 1.5;

                PesajeGanado::create([
                    'ganado_id'          => $bovino->GanadoID,
                    'peso'               => $bovino->ultimoPeso,
                    'fecha_pesaje'       => Carbon::now()->subDays(2),
                    'peso_anterior'      => $bovino->pesoActual,
                    'ganancia_peso'      => round($bovino->ultimoPeso - $bovino->pesoActual, 2),
                    'dias_transcurridos' => $diasFinal,
                    'gdp'                => $gdpTotal,
                    'bascula'            => 'Báscula Digital Principal',
                    'lote_corral'        => $bovino->lote_potrero,
                    'observaciones'      => 'Pesaje de corte zootécnico.',
                    'user_id'            => $adminId,
                ]);
            }

            // Tratamientos Sanitarios aplicados
            if ($bovino->tratamientos()->count() === 0) {
                // Protocolo de recepción común
                TratamientoSanitario::create([
                    'ganado_id'               => $bovino->GanadoID,
                    'tipo_evento'             => 'Recepcion',
                    'medicamento_producto'    => 'Bovilis Vista Once SQ (Bacterina)',
                    'dosis_aplicada'          => '2 ml',
                    'via_administracion'      => 'Subcutánea',
                    'fecha_aplicacion'        => $bovino->fechaCompra,
                    'dias_retiro_rastro'      => 21,
                    'fecha_fin_retiro'        => Carbon::parse($bovino->fechaCompra)->addDays(21),
                    'costo_medicamento_mxn'   => 85.00,
                    'veterinario_responsable' => 'MVZ. Roberto Méndez',
                    'diagnostico_motivo'      => 'Inmunización contra neumonías y complejo respiratorio bovino (BRD).',
                    'user_id'                 => $adminId,
                ]);

                TratamientoSanitario::create([
                    'ganado_id'               => $bovino->GanadoID,
                    'tipo_evento'             => 'Desparasitacion',
                    'medicamento_producto'    => 'Dectomax (Doramectina al 1%)',
                    'dosis_aplicada'          => '5 ml',
                    'via_administracion'      => 'Subcutánea',
                    'fecha_aplicacion'        => $bovino->fechaCompra,
                    'dias_retiro_rastro'      => 35,
                    'fecha_fin_retiro'        => Carbon::parse($bovino->fechaCompra)->addDays(35),
                    'costo_medicamento_mxn'   => 120.00,
                    'veterinario_responsable' => 'MVZ. Roberto Méndez',
                    'diagnostico_motivo'      => 'Control de parásitos gastrointestinales y ectoparásitos.',
                    'user_id'                 => $adminId,
                ]);

                // Un animal con tratamiento reciente para activar alerta de retiro
                if ($bovino->areteID === 'MX-2603-9520') {
                    TratamientoSanitario::create([
                        'ganado_id'               => $bovino->GanadoID,
                        'tipo_evento'             => 'Antibiotico',
                        'medicamento_producto'    => 'Draxxin (Tulitromicina)',
                        'dosis_aplicada'          => '4.5 ml',
                        'via_administracion'      => 'Subcutánea',
                        'fecha_aplicacion'        => Carbon::now()->subDays(3),
                        'dias_retiro_rastro'      => 18,
                        'fecha_fin_retiro'        => Carbon::now()->addDays(15),
                        'costo_medicamento_mxn'   => 320.00,
                        'veterinario_responsable' => 'MVZ. Roberto Méndez',
                        'diagnostico_motivo'      => 'Tratamiento terapéutico por tos y fiebre de embarque.',
                        'user_id'                 => $adminId,
                    ]);
                }
            }
        }

        // 4. CONSUMOS DIARIOS DE ALIMENTO EN CORRALES (MXN)
        if (ConsumoAlimento::count() === 0) {
            // Corral 1 - Finalización
            ConsumoAlimento::create([
                'corral_lote_id'             => $corral1->id,
                'racion_id'                  => $racionFinalizacion->id,
                'fecha'                      => Carbon::now()->subDays(1),
                'kilos_servidos'             => 420.00,
                'kilos_rechazo'              => 12.00,
                'kilos_consumidos'           => 408.00,
                'costo_total_mxn'            => round(408.00 * 7.85, 2), // $ 3,202.80 MXN
                'cabezas_contadas'           => 3,
                'consumo_promedio_cabeza_kg' => 136.00,
                'observaciones'              => 'Excelente lectura de comederos, apetito vigoroso.',
                'user_id'                    => $adminId,
            ]);

            // Corral 2 - Crecimiento
            ConsumoAlimento::create([
                'corral_lote_id'             => $corral2->id,
                'racion_id'                  => $racionCrecimiento->id,
                'fecha'                      => Carbon::now()->subDays(1),
                'kilos_servidos'             => 220.00,
                'kilos_rechazo'              => 5.00,
                'kilos_consumidos'           => 215.00,
                'costo_total_mxn'            => round(215.00 * 7.20, 2), // $ 1,548.00 MXN
                'cabezas_contadas'           => 2,
                'consumo_promedio_cabeza_kg' => 107.50,
                'observaciones'              => 'Turno matutino completo.',
                'user_id'                    => $adminId,
            ]);

            // Corral 3 - Recepción
            ConsumoAlimento::create([
                'corral_lote_id'             => $corral3->id,
                'racion_id'                  => $racionRecepcion->id,
                'fecha'                      => Carbon::now()->subDays(1),
                'kilos_servidos'             => 140.00,
                'kilos_rechazo'              => 8.00,
                'kilos_consumidos'           => 132.00,
                'costo_total_mxn'            => round(132.00 * 6.50, 2), // $ 858.00 MXN
                'cabezas_contadas'           => 2,
                'consumo_promedio_cabeza_kg' => 66.00,
                'observaciones'              => 'Adaptación con fibra larga.',
                'user_id'                    => $adminId,
            ]);
        }

        // 5. EVALUACIONES DE CANAL DE PRUEBA (DRA. SALUD RUBIO / ENGORMIX)
        if (EvaluacionCanal::count() === 0) {
            EvaluacionCanal::create([
                'ganado_id'                      => null,
                'arete_snapshot'                 => 'MX-2598-1022',
                'folio_rastro_canal'             => 'CANAL-2026-0001',
                'fecha_faena'                    => Carbon::now()->subDays(12),
                'planta_o_rastro'                => 'Frigorífico Regional TIF #148',
                'peso_vivo_bascula_kg'           => 505.00,
                'peso_canal_caliente_kg'         => 312.00,
                'rendimiento_canal_caliente_pct' => 61.78, // 312 / 505
                'peso_canal_fria_kg'             => 305.76, // 2% merma
                'merma_enfriamiento_pct'         => 2.00,
                'rendimiento_canal_fria_pct'     => 60.55,
                'marmoleo'                       => 'Moderado', // Choice High
                'espesor_grasa_dorsal_mm'        => 7.50,
                'area_ojo_costilla_cm2'          => 82.50,
                'color_carne'                    => 'Rojo_Cereza_Brillante',
                'color_grasa'                    => 'Blanco_Cremoso',
                'ph_24h'                         => 5.52, // Óptimo Dra. Rubio
                'clasificacion_mexicana'         => 'Suprema',
                'precio_canal_por_kg_mxn'        => 118.00,
                'valor_total_canal_mxn'          => round(312.00 * 118.00, 2), // $ 36,816.00 MXN
                'destino_comercial'              => 'Cortes Finos y Restaurantes',
                'observaciones_tecnicas'         => 'Excelente descenso de pH por ayuno adecuado y manejo calmo pre-faena. Terneza garantizada.',
                'user_id'                        => $adminId,
            ]);

            EvaluacionCanal::create([
                'ganado_id'                      => null,
                'arete_snapshot'                 => 'MX-2598-1025',
                'folio_rastro_canal'             => 'CANAL-2026-0002',
                'fecha_faena'                    => Carbon::now()->subDays(15),
                'planta_o_rastro'                => 'Frigorífico Regional TIF #148',
                'peso_vivo_bascula_kg'           => 480.00,
                'peso_canal_caliente_kg'         => 288.50,
                'rendimiento_canal_caliente_pct' => 60.10,
                'peso_canal_fria_kg'             => 282.73,
                'merma_enfriamiento_pct'         => 2.00,
                'rendimiento_canal_fria_pct'     => 58.90,
                'marmoleo'                       => 'Ligero',
                'espesor_grasa_dorsal_mm'        => 6.00,
                'area_ojo_costilla_cm2'          => 76.00,
                'color_carne'                    => 'Rojo_Medio',
                'color_grasa'                    => 'Blanco_Cremoso',
                'ph_24h'                         => 5.65,
                'clasificacion_mexicana'         => 'Selecta',
                'precio_canal_por_kg_mxn'        => 112.00,
                'valor_total_canal_mxn'          => round(288.50 * 112.00, 2), // $ 32,312.00 MXN
                'destino_comercial'              => 'Carnicerías Selectas',
                'observaciones_tecnicas'         => 'Buen acabado, grasa dorsal firme.',
                'user_id'                        => $adminId,
            ]);
        }
    }
}
