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
