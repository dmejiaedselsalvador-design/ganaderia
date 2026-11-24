<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class SimuladorEngordaController extends Controller
{
    /**
     * Calculadora y Simulador Financiero de Rentabilidad de Engorda en Pesos Mexicanos (MXN).
     */
    public function index(Request $request): View
    {
        // Parámetros por defecto según el mercado mexicano de engorda
        $numCabezas       = max(1, (int) ($request->input('num_cabezas', 30)));
        $pesoInicialKg    = max(100.0, (float) ($request->input('peso_inicial', 220.0)));
        $precioCompraKg   = max(10.0, (float) ($request->input('precio_compra_kg', 58.0))); // $/kg MXN
        $pesoObjetivoKg   = max($pesoInicialKg + 10.0, (float) ($request->input('peso_objetivo', 490.0)));
        $gdpEsperadaKg    = max(0.2, (float) ($request->input('gdp_esperada', 1.55))); // kg/día
        $costoAlimentoDia = max(5.0, (float) ($request->input('costo_alimento_dia', 48.0))); // $/cabeza/día en MXN
        $costoSanitario   = max(0.0, (float) ($request->input('costo_sanitario', 450.0))); // $/cabeza en MXN
        $fleteYOtros      = max(0.0, (float) ($request->input('flete_otros', 300.0))); // $/cabeza en MXN
        $precioVentaKg    = max(10.0, (float) ($request->input('precio_venta_kg', 64.0))); // $/kg en pie MXN

        // Cálculos Zootécnicos y Financieros
        $kilosAGanar = round($pesoObjetivoKg - $pesoInicialKg, 2);
        $diasEnEngorda = max(1, (int) ceil($kilosAGanar / $gdpEsperadaKg));

        $costoCompraAnimalMXN   = round($pesoInicialKg * $precioCompraKg, 2);
        $costoTotalAlimentoMXN = round($diasEnEngorda * $costoAlimentoDia, 2);
        $costoTotalEngordaMXN  = round($costoCompraAnimalMXN + $costoTotalAlimentoMXN + $costoSanitario + $fleteYOtros, 2);

        $costoPorKiloGanadoMXN = $kilosAGanar > 0
            ? round(($costoTotalAlimentoMXN + $costoSanitario) / $kilosAGanar, 2)
            : 0;

        $puntoEquilibrioVentaKgMXN = $pesoObjetivoKg > 0
            ? round($costoTotalEngordaMXN / $pesoObjetivoKg, 2)
            : 0;

        $ingresoVentaCabezaMXN = round($pesoObjetivoKg * $precioVentaKg, 2);
        $utilidadNetaCabezaMXN = round($ingresoVentaCabezaMXN - $costoTotalEngordaMXN, 2);

        $roiPct = $costoTotalEngordaMXN > 0
            ? round(($utilidadNetaCabezaMXN / $costoTotalEngordaMXN) * 100, 2)
            : 0;

        // Totales del lote completo
        $inversionLoteMXN = round($costoTotalEngordaMXN * $numCabezas, 2);
        $ingresoLoteMXN   = round($ingresoVentaCabezaMXN * $numCabezas, 2);
        $utilidadLoteMXN  = round($utilidadNetaCabezaMXN * $numCabezas, 2);

        return view('simulador.index', compact(
            'numCabezas',
            'pesoInicialKg',
            'precioCompraKg',
            'pesoObjetivoKg',
            'gdpEsperadaKg',
            'costoAlimentoDia',
            'costoSanitario',
            'fleteYOtros',
            'precioVentaKg',
            'kilosAGanar',
            'diasEnEngorda',
            'costoCompraAnimalMXN',
            'costoTotalAlimentoMXN',
            'costoTotalEngordaMXN',
            'costoPorKiloGanadoMXN',
            'puntoEquilibrioVentaKgMXN',
            'ingresoVentaCabezaMXN',
            'utilidadNetaCabezaMXN',
            'roiPct',
            'inversionLoteMXN',
            'ingresoLoteMXN',
            'utilidadLoteMXN'
        ));
    }
}
