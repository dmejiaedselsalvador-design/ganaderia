<?php

namespace Tests\Unit;

use App\Models\Venta\VentaGanado;
use PHPUnit\Framework\TestCase;

class VentaTest extends TestCase
{
    public function test_modalidades_venta_constantes(): void
    {
        $this->assertEquals('por_kilo', VentaGanado::MODALIDAD_POR_KILO);
        $this->assertEquals('precio_fijo', VentaGanado::MODALIDAD_PRECIO_FIJO);
    }

    public function test_calculo_subtotal_por_kilo(): void
    {
        $pesoKg = 450.00;
        $precioKg = 55.00;
        $subtotal = round($pesoKg * $precioKg, 2);

        $this->assertEquals(24750.00, $subtotal);
        $this->assertEquals('$ 24,750.00 MXN', formatoPesos($subtotal));
    }
}
