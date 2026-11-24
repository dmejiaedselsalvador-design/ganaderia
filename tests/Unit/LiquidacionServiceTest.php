<?php

namespace Tests\Unit;

use App\Models\Ganado\Adelanto;
use App\Models\Ganado\FacturaGanado;
use App\Models\Ganado\Ganado;
use App\Models\Ganado\ProveedorGanado;
use App\Services\LiquidacionService;
use PHPUnit\Framework\TestCase;

class LiquidacionServiceTest extends TestCase
{
    public function test_calcular_resumen_factura_con_deuda_proveedor(): void
    {
        $service = new LiquidacionService();

        $proveedor = new ProveedorGanado([
            'nombreProveedor' => 'Rancho Test',
            'nombreContacto'  => 'Don Juan',
        ]);
        $proveedor->id = 1;

        $adelanto1 = new Adelanto([
            'dinero' => 50000.00,
            'status' => Adelanto::STATUS_ENTREGADO,
        ]);
        $adelanto1->id = 10;

        $proveedor->setRelation('adelantos', collect([$adelanto1]));

        $factura = new FacturaGanado([
            'proveedorID'   => 1,
            'numeroFactura' => 'FAC-001',
            'estado'        => FacturaGanado::ESTADO_PENDIENTE,
        ]);
        $factura->id = 100;
        $factura->setRelation('proveedor', $proveedor);

        $ganado1 = new Ganado([
            'facturaID'    => 100,
            'ultimoPeso'   => 200,
            'precioCompra' => 100,
            'categoria'    => 'Becerro',
        ]);
        // precioGanadoTotal = 200 * 100 = 20,000

        $factura->setRelation('animales', collect([$ganado1]));

        $resumen = $service->calcularResumenFactura($factura);

        // Total adelantos: 50,000
        // Total ganado: 20,000
        // Saldo final = 50,000 - 20,000 = 30,000 (el proveedor debe dinero al rancho)
        $this->assertEquals(50000.00, $resumen['totalAdelantos']);
        $this->assertEquals(20000.00, $resumen['totalGanado']);
        $this->assertEquals(30000.00, $resumen['saldoFinal']);
        $this->assertTrue($resumen['esDeudaProveedor']);
        $this->assertEquals(30000.00, $resumen['montoAbsoluto']);
        $this->assertCount(1, $resumen['animalesPorCategoria']['Becerro']);
    }

    public function test_calcular_resumen_factura_con_saldo_a_favor_proveedor(): void
    {
        $service = new LiquidacionService();

        $proveedor = new ProveedorGanado(['nombreProveedor' => 'Rancho 2']);
        $proveedor->id = 2;
        $proveedor->setRelation('adelantos', collect());

        $factura = new FacturaGanado(['numeroFactura' => 'FAC-002']);
        $factura->id = 200;
        $factura->setRelation('proveedor', $proveedor);

        $ganado = new Ganado([
            'facturaID'    => 200,
            'ultimoPeso'   => 350,
            'precioCompra' => 80,
            'categoria'    => 'Toro',
        ]);
        // 350 * 80 = 28,000

        $factura->setRelation('animales', collect([$ganado]));

        $resumen = $service->calcularResumenFactura($factura);

        $this->assertEquals(0.0, $resumen['totalAdelantos']);
        $this->assertEquals(28000.0, $resumen['totalGanado']);
        $this->assertEquals(-28000.0, $resumen['saldoFinal']);
        $this->assertFalse($resumen['esDeudaProveedor']); // Rancho le debe al proveedor
        $this->assertEquals(28000.0, $resumen['montoAbsoluto']);
    }
}
