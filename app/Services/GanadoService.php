<?php

namespace App\Services;

use App\Models\Ganado\Ganado;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GanadoService
{
    /**
     * Registra un lote de animales asociados a una factura de compra.
     *
     * @param int $facturaId
     * @param string|null $fechaCompra
     * @param array<int, array<string, mixed>> $animalesData
     * @return Collection<int, Ganado>
     */
    public function registrarLote(int $facturaId, ?string $fechaCompra, array $animalesData): Collection
    {
        return DB::transaction(function () use ($facturaId, $fechaCompra, $animalesData) {
            $registrados = collect();

            foreach ($animalesData as $item) {
                $peso = (float) ($item['weight'] ?? $item['peso'] ?? 0);
                $precio = (float) ($item['unit_price'] ?? $item['precio'] ?? 0);

                $ganado = Ganado::create([
                    'facturaID'    => $facturaId,
                    'areteID'      => $item['tag_number'] ?? $item['arete'] ?? $item['areteID'],
                    'raza'         => $item['breed'] ?? $item['raza'] ?? null,
                    'categoria'    => $item['category'] ?? $item['categoria'],
                    'pesoActual'   => $peso,
                    'ultimoPeso'   => $peso,
                    'precioCompra' => $precio,
                    'fechaCompra'  => $fechaCompra ?: now()->toDateString(),
                    'peso_origen'  => isset($item['peso_origen']) && $item['peso_origen'] !== '' ? (float) $item['peso_origen'] : null,
                    'arete_siniiga' => $item['arete_siniiga'] ?? null,
                    'fierro'       => $item['fierro'] ?? null,
                    'status'       => Ganado::STATUS_ACTIVO,
                    'notas'        => $item['observation'] ?? $item['notas'] ?? null,
                ]);

                $registrados->push($ganado);
            }

            // Reparte flete/comisión/trámites de la guía entre todos sus animales
            if ($factura = \App\Models\Ganado\FacturaGanado::find($facturaId)) {
                app(\App\Services\Ganaderia\CostosCompraService::class)->prorratear($factura);
            }

            return $registrados;
        });
    }

    /**
     * Busca un animal por arete o por ID para la vista de perfil operativo.
     */
    public function buscarParaPerfil(?string $arete, ?int $ganadoId = null): ?Ganado
    {
        $query = Ganado::with(['factura.proveedor']);

        if (!empty($ganadoId)) {
            return $query->find($ganadoId);
        }

        if (!empty($arete)) {
            return $query->where('areteID', 'LIKE', "%{$arete}%")->first();
        }

        return null;
    }
}
