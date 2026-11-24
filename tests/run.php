<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

echo "Running updated unit test suite...\n\n";

$tests = [
    new \Tests\Unit\GanadoTest('test_categoria_asigna_sexo_automaticamente'),
    new \Tests\Unit\GanadoTest('test_precio_ganado_total_calculo'),
    new \Tests\Unit\GanadoTest('test_primary_key_es_ganado_id'),
    new \Tests\Unit\GanadoTest('test_peso_objetivo_y_alerta_listo_para_venta'),
    new \Tests\Unit\GanadoTest('test_ganancia_total_de_peso'),
    new \Tests\Unit\GanadoTest('test_bloqueo_operaciones_en_animal_muerto_o_vendido'),
    new \Tests\Unit\GanadoTest('test_formato_moneda_pesos_mexicanos'),
    new \Tests\Unit\VentaTest('test_modalidades_venta_constantes'),
    new \Tests\Unit\VentaTest('test_calculo_subtotal_por_kilo'),
    new \Tests\Unit\LiquidacionServiceTest('test_calcular_resumen_factura_con_deuda_proveedor'),
    new \Tests\Unit\LiquidacionServiceTest('test_calcular_resumen_factura_con_saldo_a_favor_proveedor'),
];

$passed = 0;
$failed = 0;

foreach ($tests as $test) {
    $testName = get_class($test) . '::' . $test->name();
    try {
        $test->runBare();
        echo " [PASS] $testName\n";
        $passed++;
    } catch (\Throwable $e) {
        echo " [FAIL] $testName\n";
        echo "        " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\nSummary: $passed passed, $failed failed.\n";
exit($failed > 0 ? 1 : 0);
