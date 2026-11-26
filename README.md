# 🐂 Sistema de Gestión Ganadera — Grupo Taurino

Sistema web para administrar una **engorda de ganado** (Hermosillo, Sonora · pesos MXN): compra, corrales, pesajes, alimentación, bodega, sanidad, ventas, cobranza, gastos y rentabilidad. Desde el mismo acceso se entra también a la planta de **Construcción**.

Este documento sirve como **manual para explicar el sistema al cliente**: qué hace cada pantalla, de dónde salen los números y qué contestar a las dudas más comunes.

---

## Índice
1. [Acceso, plantas y usuarios](#1-acceso-plantas-y-usuarios)
2. [Recorrido del día a día](#2-recorrido-del-día-a-día)
3. [Módulos y pantallas](#3-módulos-y-pantallas)
4. [Plan de alimentación por peso (cómo se calcula)](#4-plan-de-alimentación-por-peso-cómo-se-calcula)
5. [Atajos de captura («+ Nuevo»)](#5-atajos-de-captura--nuevo)
6. [Preguntas frecuentes del cliente](#6-preguntas-frecuentes-del-cliente)
   - [Botón de Ayuda y preguntas con captura](#botón-de-ayuda--y-preguntas-con-captura)
7. [Glosario](#7-glosario)
8. [Instalación y despliegue (técnico)](#8-instalación-y-despliegue-técnico)

---

## 1. Acceso, plantas y usuarios

**Inicio de sesión**
- «Recordarme» mantiene la sesión abierta en ese equipo.
- **Primera vez:** el administrador entrega una **clave temporal**. Al entrar, el sistema obliga a cambiarla. Así, nadie más conoce la clave del usuario.
- **¿Olvidaste tu clave?** No se usa correo. El usuario envía una *solicitud* y el Súper Administrador la ve en *Administración → Solicitudes de clave*. Ahí la **aprueba** (genera una clave temporal que se muestra una sola vez), la **rechaza** o **bloquea** al usuario para que ya no pueda pedir cambios. Solo se aceptan solicitudes de usuarios activos.

**Plantas**
- Si el usuario tiene acceso a una sola planta, entra directo.
- Si tiene acceso a varias, elige Ganadería o Construcción. El sistema recuerda la última que usó.
- En el menú del usuario están «Cambiar de planta» y «Cerrar sesión».

**Roles**

| Rol | Qué puede hacer |
|---|---|
| Súper Administrador | Todo. |
| Dueño | Todo, excepto eliminar al Súper Administrador. |
| Gerente | Operación completa y reportes. |
| Supervisor | Operación de corrales, alimentación y sanidad. |
| Operario | Captura de campo: pesajes, servidos y aplicaciones. |
| Vendedor | Ventas, clientes y cobranza. |
| Comprador | Compras, proveedores y guías. |

Los permisos se ajustan por rol en *Administración → Roles*. Los usuarios se crean, editan y dan de baja en *Administración → Usuarios*. Todo cambio queda en la **Bitácora de auditoría** (quién hizo qué y cuándo).

**Apariencia**
- Hay tres temas: Claro, Oscuro (el predeterminado) y Metal. Se cambian con los botones de la barra superior.
- El menú lateral se puede **contraer** con el botón ««», tiene buscador y agrupa las opciones en secciones.

---

## 2. Recorrido del día a día

```
Compra (guía)  →  Alta de animales  →  Corral  →  Pesajes
                                         │
                          Dieta asignada + Servido diario  →  Descuento de bodega
                                         │
                       Sanidad (tratamientos)  →  Venta  →  Cobranza
                                         │
                     Gastos operativos  →  Rentabilidad por animal y por lote
```

1. **Comprador:** registra la *guía de compra* (proveedor, factura, flete, comisión, destare) y da de alta los animales con su arete.
2. **Operario:** asigna los animales a un corral, los pesa y registra el **alimento servido** cada día.
3. **Supervisor:** revisa el **Plan de alimentación**, el semáforo de bodega y el calendario sanitario.
4. **Vendedor:** registra la venta, de contado o a crédito, y los abonos.
5. **Gerente o dueño:** revisa el Dashboard, los reportes y la rentabilidad.

---

## 3. Módulos y pantallas

> Los nombres del menú están en el lenguaje del rancho: *Ganado y corrales*, *Raciones y comida servida*, *Bodega (alimento y medicina)*, *Salud del ganado*, *Cobranza (lo que nos deben)*, *Números del rancho*, *Gastos del rancho*, *Corregir capturas*.

### Dashboard
- Indicadores del ganado: cabezas, peso promedio, GDP, mortalidad y valor de mercado.
- Utilidad proyectada.
- Alertas de stock bajo, pesajes vencidos, cobros vencidos y tratamientos pendientes.

### Modo campo
- Pantalla grande pensada para celular o báscula.
- Se escanea o se escribe el arete y se capturan pesajes y movimientos rápido.
- También permite importar pesajes desde CSV.

### Compras y proveedores
- **Proveedores:** catálogo con nombre, contacto, teléfono y lugar.
- **Guías de compra:** encabezado de la compra y sus animales.
- **Costos de la guía:** flete, comisión y trámites. Se **reparten por peso** entre los animales para obtener su costo real.
- **Liquidación y adelantos:** control de lo que se debe a cada proveedor.

### Ganado y corrales
- **Ganado:** lista y *ficha del animal*. La ficha incluye fotos, documentos, historial de corrales, pesajes, tratamientos y rentabilidad individual.
- **Corrales:** fase (Recepción, Crecimiento o Finalización), capacidad, metas (GDP, $/kg, conversión, mortalidad y presupuesto) y animales.
  - Botón **«Dieta y consumo»**: abre el plan de alimentación del corral.
- **Bajas:** muerte, sacrificio, robo, extravío o descarte.

### Alimentación
- **Dietas y servidos:** catálogo de dietas y registro diario de alimento servido a cada corral.
- **Receta (BOM) de la dieta:** liga la dieta con los artículos de bodega. Se captura de dos formas:
  - **Por cantidades**, como en la libreta. Ejemplo: «Engorda ternero» = 2 kg de maíz + 2 litros de agua + 2 libras de sal. Todo se pasa a kg (1 lb = 0.4536 kg; 1 litro = los kg por litro del insumo) y el sistema calcula el %. La receta «rinde» la suma, en este caso 4.9 kg por bache.
  - **Por porcentaje**: la suma debe dar 100 %.
  - El agua de pozo y otros insumos que no se compran se dan de alta con «No llevar existencia»: entran en la receta pero no se descuentan ni marcan faltante.
  - Cada servido **descuenta de bodega** los kg de cada ingrediente.
  - Incluye una calculadora de **bache** (cuánto mezclar de cada ingrediente) y los **parámetros de consumo por peso vivo**.
- **Plan de alimentación:** consumo calculado por peso de los animales y cruce con la bodega. Ver la [sección 4](#4-plan-de-alimentación-por-peso-cómo-se-calcula).

### Inventario de insumos (bodega)
- Tipos de insumo: alimentos, medicamentos y otros.
- Movimientos: entradas, salidas, ajustes y conteo físico.
- **Costo promedio ponderado:** cada entrada recalcula el costo unitario.
- Lotes, caducidad y stock mínimo, con alertas.
- **% materia seca:** solo para alimentos.
- **Kg por unidad:** ton = 1000 y kg = 1 se calculan solos; un saco se configura con los kg que trae (por ejemplo, 40).

### Sanidad
- Tratamientos individuales o **aplicación masiva al corral**. Esta última descuenta el medicamento de bodega y aplica los **días de retiro** antes del rastro.
- **Calendario sanitario:** vacunas y desparasitaciones programadas, con repeticiones.
- **Casos de enfermedad:** seguimiento, morbilidad y recaídas.

### Ventas y cobranza
- **Clientes:** datos fiscales del SAT (RFC, régimen y CP), límite y días de crédito.
- **Venta de contado:** queda pagada en automático.
- **Venta a crédito:** genera saldo y vencimiento. No se permite pasar el límite de crédito del cliente.
- **Cobranza:** abonos, antigüedad de saldos y estado de cuenta en PDF o Excel.

### Finanzas y reportes
- **Gastos operativos:** nómina, diésel, reparaciones, etc.
  - Si el gasto se asigna a un corral, se carga a ese lote. Si no, se **reparte por cabeza-día** entre todo el ganado.
  - Los gastos no se borran: se **anulan**, para que quede rastro.
- **Rentabilidad:** por animal y por lote. Suma compra, alimento (según los días que el animal estuvo en cada corral), medicinas, gastos del corral y gastos generales, y lo compara contra la venta o el valor de mercado.
- **Reportes:** se exportan a Excel (CSV) y PDF.

---

## 4. Plan de alimentación por peso (cómo se calcula)

**Idea central:** el alimento ya no se calcula a ojo. Se calcula con lo que **pesan los animales** de cada corral.

### Paso 1 — Peso de cada animal
| Situación | Peso que se usa |
|---|---|
| Pesado en los últimos 21 días | Su último pesaje. |
| Último pesaje con más de 21 días | Peso **proyectado**: último peso + (GDP × días transcurridos). Se marca con la etiqueta **EST** (estimado). |
| Nunca se ha pesado | Su peso de compra. |

- **GDP para proyectar:** el promedio de ganancia diaria del animal. Si no tiene, se usa la meta del corral. Nunca más de 3 kg/día.
- **Kilos de ganado del corral:** la suma de los pesos de sus animales.

### Paso 2 — Cuánto come (por % de peso vivo)
Un bovino en engorda consume entre **2 % y 3.2 % de su peso vivo** al día en **materia seca** (MS).

| Fase | Mínimo | Máximo | Sugerido |
|---|---|---|---|
| Recepción | 1.8 % | 2.5 % | 2.2 % |
| Crecimiento | 2.3 % | 2.9 % | 2.6 % |
| Finalización | 2.5 % | 3.2 % | 2.8 % |
| Mantenimiento | 1.8 % | 2.3 % | 2.0 % |

- Cada dieta tiene su rango, editable en su receta.
- Cada corral puede usar un % propio. Si queda **fuera del rango**, el sistema pide una **justificación** (por ejemplo, «Indicación del nutriólogo»).

### Paso 3 — De materia seca a lo que se sirve
El alimento lleva agua. Por eso los kg que se sirven son más que los kg de materia seca:

```
kg MS por cabeza      = peso × % PV
kg a servir por cabeza = kg MS ÷ (% MS de la dieta ÷ 100)
kg del corral por día  = suma de todas las cabezas
```

**Ejemplo para explicarle al cliente:**
- Un novillo de **400 kg** en finalización, al **2.8 %**, come **11.2 kg de materia seca** al día.
- Si la dieta tiene **80 % de materia seca**, hay que servirle **14 kg** de dieta.
- Un corral con 50 novillos así necesita **700 kg/día**, es decir, **4,900 kg por semana**.

La **% de materia seca de la dieta** se calcula sola con los ingredientes de la receta (si todos tienen su % MS capturado). Si falta alguno, se usa la que tiene la dieta.

### Paso 4 — Explosión contra bodega (BOM)
- Los kg del periodo (por ejemplo, 7 días) se reparten entre los ingredientes según la receta. Ejemplo: si la receta lleva 70 % de maíz, el maíz necesario es 70 % del total.
- Luego se comparan con lo que hay en bodega:

| Semáforo | Significado |
|---|---|
| 🟢 OK | Alcanza con más del 10 % de margen. |
| 🟡 Justo | Alcanza, pero con menos del 10 % de margen. Conviene pedir ya. |
| 🔴 Faltante | No alcanza. Se muestran los kg faltantes y su costo estimado de compra. |

- **Cobertura:** cuántos días alcanza lo que hay en bodega.

### Paso 5 — Servir y descontar
- En el plan del corral, el botón **«Registrar servido de hoy»** abre el registro ya lleno: corral, dieta asignada y kg sugeridos. El operario solo corrige si sirvió otra cantidad.
- Al guardar, se descuentan de bodega los ingredientes según la receta.
- Si algún ingrediente queda negativo, aparece un aviso para registrar su entrada.
- La columna **«Servido real»** compara lo que realmente se sirvió (promedio de 7 días) contra lo calculado. Sirve para detectar desperdicio o falta de alimento.

### Dieta asignada con historial
- Cada corral tiene **una dieta vigente**.
- Al cambiarla, la anterior se cierra con fecha y queda en el **historial**. Así se sabe qué comió cada lote y cuándo.

---

## 5. Atajos de captura («+ Nuevo»)

Para que el operador no se pierda, cuando falta un catálogo se puede **crear ahí mismo**, sin salir del formulario. Al guardar, el nuevo registro queda **seleccionado**.

| Pantalla | Atajo |
|---|---|
| Guía de compra | + Nuevo proveedor |
| Nueva venta | + Registrar cliente nuevo |
| Receta de la dieta | Crear ingrediente |
| Sanidad (tratamiento) | + Nuevo medicamento |
| Ficha del animal (mover) y Gastos | + Crear corral |
| Plan del corral | + Crear dieta → captura su receta → **regresa al plan** |

Cada atajo respeta los permisos: si el usuario no tiene permiso para ese catálogo, el botón no aparece.

---

## 6. Preguntas frecuentes del cliente

**¿Por qué el sistema dice que el corral necesita X kg si yo le sirvo otra cantidad?**
Porque lo calcula con el peso de los animales y el % de peso vivo de la dieta. Si el ganado no se pesa seguido, el peso es *estimado* y puede variar. Compare con la columna «Servido real». Si la diferencia es constante, ajuste el % del corral.

**¿Qué significa EST en el peso de un animal?**
Que su último pesaje tiene más de 21 días y el sistema está **proyectando** su peso con su ganancia diaria. Pesarlo actualiza el cálculo.

**¿Por qué la bodega quedó en negativo?**
Se registró un servido sin haber dado de alta la entrada del ingrediente, o el conteo físico no coincide. Registre la entrada o haga un ajuste por conteo físico.

**¿Cómo se calcula el costo de cada animal?**
Precio de compra + su parte del flete y la comisión (repartidos por peso) + el alimento de los días que estuvo en cada corral + medicinas aplicadas + su parte de los gastos del corral + su parte de los gastos generales (por cabeza-día).

**Se capturó mal un pesaje, un servido o una venta, ¿cómo lo corrijo?**
En *Finanzas → Corrección de registros* (o con el botón «Corregir» del expediente del animal).
- **Quién puede:** el Súper Administrador, el Dueño, el Gerente, o el rol al que se le dé el permiso «Corregir y anular registros» en *Roles*.
- **Qué se corrige o anula:**
  - pesajes
  - servidos de alimento
  - tratamientos
  - bajas (se revierten)
  - ventas (se anulan)
  - datos de compra del animal
- **Motivo obligatorio:** cada corrección lo pide, y queda el valor anterior, el nuevo, quién y cuándo.
- **Ajustes automáticos:**
  - la bodega devuelve y vuelve a descontar;
  - se recalculan la ganancia diaria y el costo;
  - al anular una venta, los animales regresan a los corrales y se cancelan sus abonos.

**¿Por qué no puedo borrar un gasto o un abono?**
Por control interno: se **anulan** y queda registro de quién lo hizo. Todo aparece en la Bitácora de auditoría.

**¿Por qué no me deja vender a crédito a un cliente?**
La venta rebasa su límite de crédito, o el cliente tiene saldos vencidos.

**El ingrediente lo compro en toneladas pero la receta está en kg, ¿hay problema?**
No. El sistema convierte solo: 1 ton = 1000 kg. Para sacos, ponga los kg por saco en el insumo.

**Cambié la dieta de un corral, ¿se pierde lo anterior?**
No. La anterior queda en el historial con sus fechas.

**Un usuario olvidó su clave, ¿qué hago?**
El usuario pide el cambio desde «¿Olvidaste tu clave?». El Súper Administrador lo aprueba y le entrega la clave temporal. Al entrar, el usuario debe cambiarla.

### Botón de Ayuda (?) y preguntas con captura
En la barra superior de **todas las pantallas** hay un botón **?**. Al abrirlo muestra:
- **Qué hace esta pantalla**, cómo se usa, de dónde salen los números y consejos.
- **«¿No te quedó claro?»:** el usuario escribe su duda y se envía con una **captura de lo que está viendo**. El panel de ayuda no sale en la imagen; el usuario puede quitar la captura o volver a tomarla.
- **Mis preguntas:** sus últimas preguntas con la respuesta. Cuando llega una respuesta nueva, el botón **?** muestra un punto verde.

**Quién responde**
- El Súper Administrador, el Dueño y el Gerente (permiso «Responder las preguntas…», configurable en Roles).
- Ven un número naranja en el botón **?** y la opción *Preguntas de usuarios* en el menú del usuario.
- En esa bandeja ven la captura (con clic se amplía), la pantalla de origen con un enlace para abrirla, la pregunta y un cuadro para responder o cerrar la pregunta.

**Privacidad**
- Las capturas se guardan en `storage/app/private/ayuda`, fuera de la carpeta pública.
- Solo las ven quien hizo la pregunta y quien responde.

**Editar los textos de ayuda:** están en `app/Support/Ayuda.php`, uno por pantalla (por nombre de ruta). Se pueden corregir sin tocar las vistas.

---

## 7. Glosario

| Término | Significado |
|---|---|
| **GDP** | Ganancia diaria de peso (kg/día). |
| **% PV** | Porcentaje del peso vivo que el animal come al día. |
| **MS** | Materia seca: el alimento sin agua. |
| **TCO** | «Tal como se ofrece»: el alimento con su humedad, como se sirve en el comedero. |
| **Kilos de ganado** | Suma de los pesos de los animales de un corral. |
| **BOM / Receta** | Lista de ingredientes de una dieta con su porcentaje. |
| **Explosión de materiales** | Convertir kg de dieta en kg de cada ingrediente. |
| **Conversión (FCR)** | kg de alimento por kg de carne ganada. |
| **Destare** | Diferencia entre el peso en origen y el peso a la llegada. |
| **Días de retiro** | Días que deben pasar entre un medicamento y el envío a rastro. |
| **Cabeza-día** | Un animal durante un día. Se usa para repartir gastos generales. |

---

## 8. Instalación y despliegue (técnico)

Requisitos: PHP 8.4+, Composer, Node 18+ y MySQL/MariaDB (o SQLite para pruebas).

**Primera instalación**
```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env && php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=RolesYPermisosSeeder --force
php artisan db:seed --class=AdminUserSeeder --force   # usa ADMIN_NAME / ADMIN_EMAIL del .env
npm ci && npm run build        # o subir public/build ya compilado
php artisan storage:link
php artisan optimize
```

**Cada actualización**
```bash
php artisan migrate --force
php artisan db:seed --class=RolesYPermisosSeeder --force   # agrega permisos nuevos a los roles
php artisan optimize:clear
```
> Si aparece «Route [...] not defined» después de actualizar, es la caché de rutas: corre `php artisan optimize:clear`.

**Datos de prueba (simulación)**
- *Administración → Datos de prueba* (solo Súper Administrador): **Cargar datos de prueba** crea unos 3 meses de operación:
  - 10 proveedores y 10 guías con 100 animales;
  - corrales, dietas con receta, bodega y 45 días de servidos;
  - sanidad, ventas, cobranza, gastos;
  - un usuario por rol: `demo.<rol>@simulacion.local`, clave `Simulacion2026*`.
- Mientras estén cargados, una franja amarilla avisa en todas las pantallas.
- **Eliminar datos de prueba** (hay que escribir ELIMINAR) borra exactamente lo que creó la simulación y deja el sistema limpio. Queda constancia en la bitácora.
- Solo se puede cargar si no hay ganado registrado, para no mezclarse con datos reales.
- Desde consola: `php artisan db:seed --class=SimulacionSeeder`.

**Pruebas**
```bash
php artisan test
```

**Arquitectura**
- Laravel 12, Spatie Permission, Alpine.js, Tailwind, Chart.js y dompdf.
- Rutas por módulo en `routes/`. Los módulos profesionales están en `routes/ganaderia_pro.php`.
- Servicios de negocio en `app/Services/Ganaderia/`:
  - `BiomasaService` — peso real o estimado por animal y biomasa por corral.
  - `ConsumoRecomendadoService` — % PV, kg MS/TCO y plan por corral.
  - `ExplosionMaterialesService` — receta → ingredientes → cruce con bodega.
  - `InventarioService` — kardex y costo promedio.
  - `RentabilidadService` — reparto de costos y utilidad.
- Componentes de interfaz: `resources/views/components/ui/*` (incluye `atajo`).
- Configuración de los atajos: `app/Support/Atajos.php`. Regreso seguro entre pantallas: `app/Support/Volver.php`.
