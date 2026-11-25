<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/** Exportación CSV compatible con Excel (UTF-8 con BOM). */
class Csv
{
    public static function descargar(string $nombre, array $encabezados, iterable $filas): StreamedResponse
    {
        $nombre = str_ends_with($nombre, '.csv') ? $nombre : $nombre . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($encabezados, $filas) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $encabezados, ',', '"', '\\');
            foreach ($filas as $fila) {
                fputcsv($out, array_map(fn ($v) => is_float($v) ? round($v, 2) : $v, (array) $fila), ',', '"', '\\');
            }
            fclose($out);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
