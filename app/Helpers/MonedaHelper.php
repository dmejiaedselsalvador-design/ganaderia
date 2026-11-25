<?php

if (!function_exists('formatoPesos')) {
    /**
     * Da formato a una cantidad monetaria con estándar de moneda mexicana:
     * Ejemplo: $ 12,500.00 MXN
     */
    function formatoPesos($cantidad): string {
        $valor = (float) ($cantidad ?? 0);
        return '$ ' . number_format($valor, 2, '.', ',') . ' MXN';
    }
}

if (!function_exists('formatoPesosCorto')) {
    /**
     * Formato compacto para tarjetas de indicadores: $ 1.25 M, $ 845.3 k, $ 950
     */
    function formatoPesosCorto($cantidad): string {
        $valor = (float) ($cantidad ?? 0);
        $abs = abs($valor);
        $signo = $valor < 0 ? '-' : '';

        if ($abs >= 1_000_000) {
            return $signo . '$ ' . number_format($abs / 1_000_000, 2) . ' M';
        }
        if ($abs >= 10_000) {
            return $signo . '$ ' . number_format($abs / 1_000, 1) . ' k';
        }

        return $signo . '$ ' . number_format($abs, 0);
    }
}
