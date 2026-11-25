<?php

namespace App\Support;

/**
 * Textos del botón de Ayuda (?) por pantalla.
 * Clave = nombre de la ruta (se permite comodín al final, p. ej. 'usuarios.*').
 *
 * Cada pantalla: titulo, que (para qué sirve), pasos (cómo se usa),
 * numeros (de dónde salen los datos) y consejos. Todo es opcional excepto titulo y que.
 * Para agregar o corregir una explicación basta con editar este archivo.
 */
class Ayuda
{
    public const PANTALLAS = [
        // ───────── General ─────────
        'welcome' => [
            'titulo' => 'Dashboard de Ganadería',
            'que'    => 'Resumen del negocio en una sola vista: cuántos animales hay, cuánto pesan, cuánto valen hoy y qué requiere atención.',
            'numeros' => [
                'Valor de mercado = kg actuales del hato × precio de mercado vigente (Configuración → Precios de mercado).',
                'Utilidad proyectada = valor de mercado − costo acumulado (compra + alimento + medicinas + gastos).',
                'GDP promedio = ganancia diaria de peso calculada con los pesajes registrados.',
            ],
            'consejos' => ['Las alertas de arriba son las tareas del día: stock bajo, pesajes vencidos, cobros vencidos y tratamientos pendientes.'],
        ],
        'campo.index' => [
            'titulo' => 'Modo campo',
            'que'    => 'Pantalla grande para usar en celular o en la báscula: se busca el animal por arete y se registra rápido.',
            'pasos'  => [
                'Escanea el arete con el lector o escríbelo y presiona Buscar.',
                'Captura el peso o el movimiento y guarda.',
                'Para muchos pesajes a la vez usa «Importar CSV» (columnas: arete, peso, fecha).',
            ],
        ],

        // ───────── Compras ─────────
        'proveedores.index' => [
            'titulo' => 'Proveedores',
            'que'    => 'Catálogo de ranchos y personas a quienes se les compra ganado.',
            'consejos' => ['También puedes crear un proveedor desde la guía de compra con el botón «+ Nuevo proveedor», sin salir de la captura.'],
        ],
        'proveedores.facturas.crear' => [
            'titulo' => 'Nueva guía de compra',
            'que'    => 'Registra la compra de un lote de ganado: proveedor, fecha y número de factura o guía.',
            'pasos'  => [
                'Elige el proveedor. Si no existe, usa «+ Nuevo proveedor» y queda seleccionado.',
                'Captura fecha y número de factura.',
                'Después agrega los animales y, en «Costos», el flete, la comisión y los trámites.',
            ],
        ],
        'proveedores.facturas.index' => [
            'titulo' => 'Guías de compra',
            'que'    => 'Todas las compras de ganado con su estado de pago.',
        ],
        'compras.costos.edit' => [
            'titulo' => 'Costos de la guía',
            'que'    => 'Gastos de la compra que se suman al costo de cada animal.',
            'numeros' => [
                'Flete, comisión y trámites se reparten entre los animales según su peso (el animal más pesado absorbe más).',
                'Destare = (peso en origen − peso a la llegada) ÷ peso en origen.',
            ],
        ],
        'proveedores.deudores.lista' => [
            'titulo' => 'Saldos con proveedores',
            'que'    => 'Lo que se le debe a cada proveedor y los anticipos entregados.',
        ],
        'adelantos.*' => [
            'titulo' => 'Anticipos a proveedores',
            'que'    => 'Dinero entregado por adelantado que después se descuenta al liquidar la guía.',
        ],

        // ───────── Hato y corrales ─────────
        'compras.ganado.index' => [
            'titulo' => 'Inventario de ganado',
            'que'    => 'Lista de todos los animales con su arete, corral, peso y estado.',
            'consejos' => ['Abre la ficha de un animal para ver su historial completo y su rentabilidad.'],
        ],
        'compras.ganado.perfil' => [
            'titulo' => 'Perfil del animal',
            'que'    => 'Datos, pesajes y movimientos de un animal.',
        ],
        'ganado.ficha' => [
            'titulo' => 'Ficha del animal',
            'que'    => 'Todo sobre un animal: datos de identificación, fotos, documentos, historial de corrales, pesajes, tratamientos y su costo real.',
            'pasos'  => [
                'Para cambiarlo de corral usa «Mover a corral». Si el corral no existe, «+ Crear corral».',
                'Sube fotos o documentos (REEMO, certificados) en sus secciones.',
            ],
            'numeros' => ['El costo incluye la compra, su parte del flete, el alimento de los días que estuvo en cada corral, sus medicinas y su parte de los gastos.'],
        ],
        'ganado.bajas.index' => [
            'titulo' => 'Bajas',
            'que'    => 'Animales que salieron por muerte, sacrificio, robo, extravío o descarte. Alimenta el indicador de mortalidad.',
        ],
        'corrales.index' => [
            'titulo' => 'Corrales',
            'que'    => 'Lotes de engorda con su fase, ocupación y metas.',
        ],
        'corrales.show' => [
            'titulo' => 'Detalle del corral',
            'que'    => 'Animales del corral, sus indicadores y los botones para asignar ganado y servir alimento.',
            'consejos' => ['El botón «Dieta y consumo» abre el plan de alimentación de este corral: cuánto deben comer según su peso.'],
        ],

        // ───────── Alimentación ─────────
        'nutricion.index' => [
            'titulo' => 'Dietas y servidos',
            'que'    => 'Catálogo de dietas (raciones) y registro diario del alimento que se sirve a cada corral.',
            'pasos'  => [
                '«Servir alimento»: elige el corral; el sistema propone su dieta asignada y los kg sugeridos según el peso de los animales.',
                'Corrige los kg si serviste otra cantidad y anota el rechazo (sobrante) si lo hubo.',
                'Al guardar se descuentan de bodega los ingredientes de la receta.',
            ],
            'numeros' => [
                'Costo del servido = kg consumidos × costo por kg de la dieta.',
                'Conversión (FCR) = kg de alimento ÷ kg de carne ganada.',
            ],
        ],
        'raciones.formula' => [
            'titulo' => 'Receta de la dieta (BOM)',
            'que'    => 'Liga la dieta con los artículos de bodega. Con ella el sistema sabe cuánto descontar de cada ingrediente en cada servido.',
            'pasos'  => [
                '«Por cantidades»: captura como en la libreta, por ejemplo 2 kg de maíz, 2 litros de agua y 2 libras de sal. El sistema pasa todo a kg y calcula el %.',
                '«Por porcentaje»: si ya tienes la fórmula en %, captúrala directo; el total debe dar 100 %.',
                'Si el ingrediente no existe, «Crear ingrediente» lo da de alta y lo agrega a la receta.',
                'En «Consumo por peso vivo» define el rango de % del peso que comen los animales con esta dieta.',
            ],
            'numeros' => [
                'Costo por kg = suma de (costo promedio de cada ingrediente × su %).',
                'La materia seca de la dieta se calcula con la materia seca de cada ingrediente.',
                'Conversiones: 1 lb = 0.4536 kg; 1 litro = los kg por litro del insumo (agua = 1 kg).',
            ],
            'consejos' => ['El agua de pozo se da de alta como insumo marcado «No llevar existencia»: entra a la receta pero no se descuenta de bodega.'],
        ],
        'nutricion.planeacion' => [
            'titulo' => 'Plan de alimentación',
            'que'    => 'Cuánto alimento necesita cada corral según lo que pesan sus animales y si la bodega alcanza.',
            'numeros' => [
                'Peso de cada animal: su último pesaje. Si tiene más de 21 días se proyecta con su ganancia diaria (marcado EST).',
                'kg de materia seca por cabeza = peso × % de peso vivo (por ejemplo, 400 kg × 2.8 % = 11.2 kg).',
                'kg a servir = materia seca ÷ % de materia seca de la dieta (11.2 ÷ 0.80 = 14 kg).',
                'Bodega: verde alcanza, amarillo alcanza con menos de 10 % de margen, rojo falta.',
            ],
            'consejos' => ['Si «Servido real» es muy distinto a lo calculado, revisa el % del corral o pesa a los animales.'],
        ],
        'nutricion.plan.corral' => [
            'titulo' => 'Dieta y consumo del corral',
            'que'    => 'Dieta asignada al corral, cuánto debe comer cada animal y el botón para registrar el servido del día.',
            'pasos'  => [
                '1. Dieta: elige la dieta y desde qué fecha. La anterior queda en el historial.',
                '2. Consumo: prueba otro % de peso vivo con «Probar» y guárdalo si conviene. Fuera de rango pide justificación.',
                '3. «Registrar servido de hoy» abre el registro ya lleno.',
            ],
            'numeros' => ['La tabla de abajo muestra el peso usado para cada animal y cuántos kg le tocan.'],
        ],
        'inventario.index' => [
            'titulo' => 'Inventario de insumos (bodega)',
            'que'    => 'Existencias de alimento, medicamento y otros insumos con su costo.',
            'numeros' => [
                'Costo promedio ponderado: cada entrada recalcula el costo unitario con lo que había y lo que llegó.',
                'Kg por unidad: ton = 1000 kg; para sacos indica los kg que trae cada uno.',
            ],
            'consejos' => ['Si algo quedó en negativo, registra la entrada que faltó o haz un ajuste por conteo físico.'],
        ],
        'inventario.show' => [
            'titulo' => 'Kardex del insumo',
            'que'    => 'Todos los movimientos de un insumo: entradas, salidas por servidos o tratamientos y ajustes.',
        ],

        // ───────── Sanidad ─────────
        'sanidad.index' => [
            'titulo' => 'Sanidad',
            'que'    => 'Tratamientos aplicados a los animales y control de días de retiro antes del rastro.',
            'pasos'  => [
                'Elige el animal y el medicamento del inventario. Si no existe, «+ Nuevo medicamento».',
                'Captura la cantidad: se descuenta de bodega y se calcula el costo.',
            ],
            'numeros' => ['Días de retiro: el animal no debe venderse a rastro antes de que pasen.'],
        ],
        'sanidad.calendario' => [
            'titulo' => 'Calendario sanitario',
            'que'    => 'Vacunas y desparasitaciones programadas por corral, con repetición automática.',
        ],
        'sanidad.enfermedades' => [
            'titulo' => 'Casos de enfermedad',
            'que'    => 'Seguimiento de animales enfermos, recuperación y recaídas. Alimenta el indicador de morbilidad.',
        ],

        // ───────── Comercial ─────────
        'ventas.index' => [
            'titulo' => 'Ventas',
            'que'    => 'Ventas de ganado con su condición de pago y saldo.',
        ],
        'ventas.create' => [
            'titulo' => 'Nueva venta',
            'que'    => 'Registra la venta de uno o varios animales.',
            'pasos'  => [
                'Elige el cliente. Si no existe, «+ Registrar cliente nuevo aquí mismo».',
                'Contado: queda pagada. Crédito: genera saldo con vencimiento según los días de crédito.',
            ],
            'consejos' => ['No se permite vender a crédito por encima del límite del cliente.'],
        ],
        'clientes.*' => [
            'titulo' => 'Clientes',
            'que'    => 'Compradores con sus datos fiscales (RFC, régimen, CP), límite y días de crédito.',
        ],
        'cobranza.index' => [
            'titulo' => 'Cobranza',
            'que'    => 'Saldos pendientes de clientes ordenados por antigüedad y registro de abonos.',
        ],

        // ───────── Finanzas ─────────
        'gastos.*' => [
            'titulo' => 'Gastos operativos',
            'que'    => 'Nómina, diésel, reparaciones y demás gastos de la operación.',
            'numeros' => ['Asignado a un corral se carga a ese lote; si no, se reparte por cabeza-día entre todo el hato.'],
            'consejos' => ['Los gastos no se borran: se anulan para que quede registro.'],
        ],
        'reportes.*' => [
            'titulo' => 'Reportes y rentabilidad',
            'que'    => 'Costo, utilidad y metas por animal y por lote. Se exportan a Excel y PDF.',
            'numeros' => ['Utilidad = venta (o valor de mercado) − compra − alimento − medicinas − gastos asignados.'],
        ],
        'configuracion.index' => [
            'titulo' => 'Configuración',
            'que'    => 'Precios de mercado, ubicaciones y metas por lote que usan los cálculos del sistema.',
        ],
        'simulador.index' => [
            'titulo' => 'Simulador de engorda',
            'que'    => 'Calcula cuánto podrías ganar con una compra antes de hacerla.',
        ],
        'calidad.carne.index' => [
            'titulo' => 'Calidad de carne',
            'que'    => 'Evaluaciones de canal: rendimiento, grasa, marmoleo y pH.',
        ],

        'correcciones.*' => [
            'titulo' => 'Corrección de registros',
            'que'    => 'Corrige o anula datos mal capturados: pesajes, servidos de alimento, tratamientos, bajas, ventas y datos de compra. Solo Súper Administrador, Dueño, Gerente o quien tenga el permiso.',
            'pasos'  => [
                'Busca el animal por arete, o filtra los servidos por corral y fechas.',
                'Presiona «Corregir», cambia el dato y escribe el motivo. Para quitar un registro usa «Anular».',
            ],
            'numeros' => [
                'Servidos y tratamientos: lo que salió de bodega se devuelve y se vuelve a descontar con el dato correcto.',
                'Pesajes y compra: se recalcula la ganancia diaria y el costo del animal.',
                'Venta anulada: los animales regresan al hato y se cancelan sus abonos.',
            ],
            'consejos' => ['Nada se borra sin rastro: abajo queda el historial con el valor anterior, el nuevo, quién y por qué.'],
        ],

        // ───────── Administración ─────────
        'usuarios.*' => [
            'titulo' => 'Usuarios',
            'que'    => 'Alta, edición y baja de usuarios, su rol y las plantas a las que entran.',
            'consejos' => ['Al crear un usuario se genera una clave temporal que debe cambiar al entrar por primera vez.'],
        ],
        'roles.*' => [
            'titulo' => 'Roles y permisos',
            'que'    => 'Qué puede ver y hacer cada rol.',
        ],
        'solicitudes.index' => [
            'titulo' => 'Solicitudes de clave',
            'que'    => 'Usuarios que olvidaron su clave. Aprueba para generar una clave temporal, deniega o bloquea.',
        ],
        'ayuda.index' => [
            'titulo' => 'Preguntas de usuarios',
            'que'    => 'Dudas enviadas desde el botón de Ayuda de cada pantalla, con la captura de lo que estaban viendo.',
        ],
        'auditoria.index' => [
            'titulo' => 'Bitácora de auditoría',
            'que'    => 'Quién creó, cambió o anuló cada registro y cuándo.',
        ],
    ];

    public const GENERAL = [
        'titulo' => 'Ayuda',
        'que'    => 'Si algo de esta pantalla no queda claro, escribe tu pregunta abajo. Se envía con una captura de lo que estás viendo.',
    ];

    /** Explicación para una ruta (coincidencia exacta o con comodín). */
    public static function para(?string $ruta): array
    {
        if ($ruta) {
            if (isset(self::PANTALLAS[$ruta])) {
                return self::PANTALLAS[$ruta];
            }
            foreach (self::PANTALLAS as $patron => $info) {
                if (str_ends_with($patron, '*') && str_starts_with($ruta, rtrim($patron, '*'))) {
                    return $info;
                }
            }
        }

        return self::GENERAL;
    }
}
