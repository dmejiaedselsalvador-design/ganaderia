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
     * Formato para tarjetas de indicadores: cantidad completa con separador de miles y sin centavos.
     * Ej. $ 18,000 · $ 1,290,450 · -$ 226,600 (a petición del cliente: nada de «k» ni «M»).
     */
    function formatoPesosCorto($cantidad): string {
        $valor = round((float) ($cantidad ?? 0));
        $signo = $valor < 0 ? '-' : '';

        return $signo . '$ ' . number_format(abs($valor), 0, '.', ',');
    }
}
