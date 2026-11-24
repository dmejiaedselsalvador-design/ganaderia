<?php

namespace App\Services;

use App\Models\Ganado\Ganado;
use App\Models\Venta\DetalleVentaGanado;
use App\Models\Venta\VentaGanado;
use Exception;
use Illuminate\Support\Facades\DB;

class VentaService
{
    /**
     * Registra una venta completa de ganado (individual o por lote) de forma atómica:
     * - Valida que todos los animales estén vivos y en estatus 'Activo'.
     * - Genera folio correlativo oficial.
     * - Calcula subtotales por kilo o precio fijo según la modalidad.
     * - Registra el detalle por arete.
     * - Cambia el estatus de los animales a 'Vendido', descontándolos del inventario activo.
     */
    public function registrarVenta(array $datosVenta, array $animalesVenta, ?int $userId = null): VentaGanado
    {
        if (empty($animalesVenta)) {
            throw new Exception("Debes seleccionar al menos un animal activo para procesar la venta.");
        }

        $animalIds = array_column($animalesVenta, 'ganado_id');
        $animales = Ganado::whereIn('GanadoID', $animalIds)->get()->keyBy('GanadoID');

        // Validar que todos los animales existan y estén activos
        foreach ($animalIds as $id) {
            if (!isset($animales[$id])) {
                throw new Exception("Uno de los animales seleccionados (ID #{$id}) no existe.");
            }

            $animal = $animales[$id];
            if (!$animal->puedeOperar()) {
                throw new Exception("El animal #{$animal->areteID} no puede ser vendido porque se encuentra en estatus '{$animal->status}'.");
            }
        }

        return DB::transaction(function () use ($datosVenta, $animalesVenta, $animales, $userId) {
            $modalidad = $datosVenta['modalidad_pago'] ?? VentaGanado::MODALIDAD_POR_KILO;
            $subtotalVenta = 0.0;
            $pesoTotalKg = 0.0;
            $detallesPrecalculados = [];

            foreach ($animalesVenta as $item) {
                $ganado = $animales[$item['ganado_id']];
                $pesoVenta = (float) ($item['peso_venta'] ?? $ganado->ultimoPeso);
                $precioUnitario = (float) ($item['precio_unitario'] ?? 0);

                if ($pesoVenta <= 0) {
                    throw new Exception("El peso de venta del arete #{$ganado->areteID} debe ser mayor a cero.");
                }

                if ($precioUnitario < 0) {
                    throw new Exception("El precio unitario no puede ser negativo.");
                }

                // Cálculo de subtotal por animal
                if ($modalidad === VentaGanado::MODALIDAD_POR_KILO) {
                    $subtotalAnimal = round($pesoVenta * $precioUnitario, 2);
                } else {
                    // Precio fijo por cabeza
                    $subtotalAnimal = round($precioUnitario, 2);
                }

                $subtotalVenta += $subtotalAnimal;
                $pesoTotalKg += $pesoVenta;

                $detallesPrecalculados[] = [
                    'ganado'          => $ganado,
                    'peso_venta'      => $pesoVenta,
                    'precio_unitario' => $precioUnitario,
                    'subtotal'        => $subtotalAnimal,
                ];
            }

            $descuento = (float) ($datosVenta['descuento'] ?? 0);
            $totalVenta = max(0.0, round($subtotalVenta - $descuento, 2));

            // 1. Crear cabecera de la venta
            $venta = VentaGanado::create([
                'folio_venta'            => VentaGanado::generarNuevoFolio(),
                'cliente_nombre'         => $datosVenta['cliente_nombre'],
                'cliente_telefono'       => $datosVenta['cliente_telefono'] ?? null,
                'cliente_identificacion' => $datosVenta['cliente_identificacion'] ?? null,
                'destino'                => $datosVenta['destino'] ?? null,
                'fecha_venta'            => $datosVenta['fecha_venta'] ?? now()->toDateString(),
                'modalidad_pago'         => $modalidad,
                'total_cabezas'          => count($detallesPrecalculados),
                'peso_total_kg'          => round($pesoTotalKg, 2),
                'subtotal'               => $subtotalVenta,
                'descuento'              => $descuento,
                'total_venta'            => $totalVenta,
                'estado'                 => VentaGanado::ESTADO_COMPLETADA,
                'notas'                  => $datosVenta['notas'] ?? null,
                'user_id'                => $userId,
            ]);

            // 2. Crear los detalles y cambiar estatus a 'Vendido'
            foreach ($detallesPrecalculados as $linea) {
                $ganado = $linea['ganado'];

                DetalleVentaGanado::create([
                    'venta_id'           => $venta->id,
                    'ganado_id'          => $ganado->GanadoID,
                    'arete_snapshot'     => $ganado->areteID,
                    'categoria_snapshot' => $ganado->categoria,
                    'raza_snapshot'      => $ganado->raza,
                    'peso_venta'         => $linea['peso_venta'],
                    'precio_unitario'    => $linea['precio_unitario'],
                    'subtotal'           => $linea['subtotal'],
                ]);

                // Actualizar último peso registrado en venta y cambiar estatus a Vendido
                $ganado->ultimoPeso = $linea['peso_venta'];
                $ganado->status = Ganado::STATUS_VENDIDO;
                $ganado->save();
            }

            return $venta;
        });
    }
}
