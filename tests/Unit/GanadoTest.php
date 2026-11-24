<?php

namespace Tests\Unit;

use App\Models\Ganado\Ganado;
use PHPUnit\Framework\TestCase;

class GanadoTest extends TestCase
{
    public function test_categoria_asigna_sexo_automaticamente(): void
    {
        $macho = new Ganado(['categoria' => 'Torete']);
        $this->assertEquals('Macho', $macho->sexo);

        $hembra = new Ganado(['categoria' => 'Becerra']);
        $this->assertEquals('Hembra', $hembra->sexo);

        $vaca = new Ganado(['categoria' => 'Vaca']);
        $this->assertEquals('Hembra', $vaca->sexo);

        $toro = new Ganado(['categoria' => 'Toro']);
        $this->assertEquals('Macho', $toro->sexo);
    }

    public function test_precio_ganado_total_calculo(): void
    {
        $ganado = new Ganado([
            'ultimoPeso'   => 185.50,
            'precioCompra' => 95.00,
        ]);

        $this->assertEquals(17622.50, $ganado->precioGanadoTotal);
    }

    public function test_primary_key_es_ganado_id(): void
    {
        $ganado = new Ganado();
        $this->assertEquals('GanadoID', $ganado->getKeyName());
    }

    public function test_peso_objetivo_y_alerta_listo_para_venta(): void
    {
        // Un torete con peso objetivo por defecto de 380 kg
        $toreteEnDesarrollo = new Ganado([
            'categoria'  => 'Torete',
            'ultimoPeso' => 320.00,
            'status'     => Ganado::STATUS_ACTIVO,
        ]);
        $this->assertEquals(380.00, $toreteEnDesarrollo->peso_objetivo_calculado);
        $this->assertFalse($toreteEnDesarrollo->esta_listo_venta);

        // Un torete que ya alcanzó 395 kg
        $toreteListo = new Ganado([
            'categoria'  => 'Torete',
            'ultimoPeso' => 395.00,
            'status'     => Ganado::STATUS_ACTIVO,
        ]);
        $this->assertTrue($toreteListo->esta_listo_venta);

        // Si ya fue vendido o está muerto, no debe figurar como listo para venta
        $toreteVendido = new Ganado([
            'categoria'  => 'Torete',
            'ultimoPeso' => 410.00,
            'status'     => Ganado::STATUS_VENDIDO,
        ]);
        $this->assertFalse($toreteVendido->esta_listo_venta);
    }

    public function test_ganancia_total_de_peso(): void
    {
        $ganado = new Ganado([
            'pesoActual' => 200.00, // peso de ingreso
            'ultimoPeso' => 320.50, // peso en última báscula
        ]);

        $this->assertEquals(120.50, $ganado->ganancia_total);
    }

    public function test_bloqueo_operaciones_en_animal_muerto_o_vendido(): void
    {
        $activo = new Ganado(['status' => Ganado::STATUS_ACTIVO]);
        $this->assertTrue($activo->puedeOperar());

        $muerto = new Ganado(['status' => Ganado::STATUS_MUERTO]);
        $this->assertFalse($muerto->puedeOperar());
        $this->assertTrue($muerto->isMuerto());

        $vendido = new Ganado(['status' => Ganado::STATUS_VENDIDO]);
        $this->assertFalse($vendido->puedeOperar());
        $this->assertTrue($vendido->isVendido());
    }

    public function test_formato_moneda_pesos_mexicanos(): void
    {
        $this->assertEquals('$ 12,500.00 MXN', formatoPesos(12500));
        $this->assertEquals('$ 45.50 MXN', formatoPesos(45.5));
        $this->assertEquals('$ 0.00 MXN', formatoPesos(0));
        $this->assertEquals('$ 0.00 MXN', formatoPesos(null));
    }
}
