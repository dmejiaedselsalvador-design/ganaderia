<?php

namespace App\Support;

/**
 * Definición de los formularios rápidos ("+ Nuevo") que permiten crear un catálogo
 * sin salir de la pantalla donde se está capturando.
 */
class Atajos
{
    /** tipo => [titulo, permiso, campos[nombre => [etiqueta, tipo, requerido, opciones]]] */
    public const TIPOS = [
        'proveedor' => [
            'titulo'  => 'Nuevo proveedor',
            'permiso' => 'ganaderia.proveedores',
            'campos'  => [
                'nombreProveedor' => ['Nombre del proveedor o rancho', 'text', true],
                'nombreContacto'  => ['Contacto', 'text', false],
                'telefono'        => ['Teléfono', 'text', false],
                'lugar'           => ['Lugar (municipio)', 'text', false],
            ],
        ],
        'cliente' => [
            'titulo'  => 'Nuevo cliente',
            'permiso' => 'ganaderia.clientes',
            'campos'  => [
                'nombre'       => ['Nombre', 'text', true],
                'rfc'          => ['RFC', 'text', false],
                'telefono'     => ['Teléfono', 'text', false],
                'dias_credito' => ['Días de crédito (0 = contado)', 'number', false],
            ],
        ],
        'insumo' => [
            'titulo'  => 'Nuevo insumo',
            'permiso' => 'ganaderia.inventario',
            'campos'  => [
                'nombre'           => ['Nombre', 'text', true],
                'tipo'             => ['Tipo', 'select', true, ['alimento' => 'Alimento / ingrediente', 'medicamento' => 'Medicamento', 'otro' => 'Otro']],
                'unidad'           => ['Unidad en que se compra', 'select', true, ['kg' => 'kg', 'ton' => 'ton', 'lb' => 'lb', 'l' => 'litros', 'ml' => 'ml', 'dosis' => 'dosis', 'pieza' => 'pieza', 'saco' => 'saco', 'frasco' => 'frasco']],
                'controla_existencia' => ['¿Se lleva existencia en bodega?', 'select', false, ['1' => 'Sí, se descuenta al usarlo', '0' => 'No (ej. agua de pozo)']],
                'materia_seca_pct' => ['% materia seca (alimentos)', 'number', false],
                'dias_retiro'      => ['Días de retiro (medicamentos)', 'number', false],
            ],
        ],
        'corral' => [
            'titulo'  => 'Nuevo corral',
            'permiso' => 'ganaderia.corrales',
            'campos'  => [
                'codigo'           => ['Código', 'text', true],
                'nombre'           => ['Nombre', 'text', true],
                'fase_engorda'     => ['Fase', 'select', true, ['Recepcion' => 'Recepción', 'Crecimiento' => 'Crecimiento', 'Finalizacion' => 'Finalización']],
                'capacidad_maxima' => ['Capacidad (cabezas)', 'number', true],
            ],
        ],
    ];
}
