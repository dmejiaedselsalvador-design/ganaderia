<?php

if (!function_exists('formatoPesos')) {
    function formatoPesos($cantidad) {
        return '$ ' . number_format($cantidad, 2, '.', ',') . ' MXN';
    //  return number_format($cantidad, 2, '.', ',') . ' MXN';
    }
}
