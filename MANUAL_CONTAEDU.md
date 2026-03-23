# ContaEdu — Manual del Sistema

> Plataforma contable educativa para estudiantes de administración y contabilidad colombiana.
> Versión actual: Fases 1–6 completadas (ciclo contable completo + facturación electrónica simulada).

---

## ¿Qué es ContaEdu?

ContaEdu es un simulador de software contable pensado para que los estudiantes aprendan haciendo, sin miedo a cometer errores con dinero real. Cada estudiante tiene su **propia empresa virtual** completamente aislada de las demás, donde puede registrar clientes, proveedores, productos, facturas, compras, pagos y generar reportes financieros exactamente como lo haría en un software real como Siigo, World Office o Alegra.

El docente, por su parte, puede supervisar todas las empresas del grupo desde un solo panel, navegar dentro de cualquier empresa en modo de solo lectura (auditoría) y calificar el trabajo de cada estudiante.

---

## Roles del sistema

| Quién | Cómo entra | Qué puede hacer |
|---|---|---|
| **Superadmin** | `/login` con correo electrónico | Crea instituciones y docentes |
| **Docente** | `/login` con correo electrónico | Crea grupos, empresa para cada estudiante, audita y califica |
| **Estudiante** | `/estudiante/login` con cédula | Opera su propia empresa contable |

---

## Módulo 1 — Configuración de la empresa

Lo primero que debe hacer un estudiante al entrar a su empresa es completar la información básica. Aquí se registran datos que aparecerán en facturas, reportes y documentos electrónicos.

**¿Qué se configura?**
- NIT de la empresa (con dígito de verificación)
- Razón social
- Régimen fiscal: **Simplificado**, **Común** o **Gran Contribuyente**
- Dirección, teléfono y correo electrónico
- Logo corporativo (opcional)
- Prefijo y resolución DIAN (para facturas tradicionales)

**Contexto colombiano:**
El régimen fiscal determina si la empresa es responsable de IVA. Las empresas del régimen simplificado no cobran IVA ni presentan declaración de IVA, mientras que las del régimen común sí lo hacen. Esta distinción afecta directamente la contabilidad y la facturación.

---

## Módulo 2 — Plan de Cuentas (PUC)

Cuando se crea una empresa en ContaEdu, el sistema siembra automáticamente el **Plan Único de Cuentas (PUC) colombiano**, que es el estándar definido por el Decreto 2650 de 1993 (adaptado para pymes educativas).

**Estructura del PUC:**

```
Clase 1 — Activo         (lo que tiene la empresa)
  11 Disponible          → Caja (1105), Bancos (1110)
  12 Inversiones
  13 Deudores            → Cuentas por cobrar clientes (1305)
  14 Inventarios         → Mercancías (1435)
  15 Propiedades, planta y equipo

Clase 2 — Pasivo         (lo que debe la empresa)
  21 Obligaciones financieras
  22 Proveedores         → Nacionales (2205)
  23 Cuentas por pagar
  24 Impuestos           → IVA por pagar (2408)

Clase 3 — Patrimonio     (lo que le pertenece a los dueños)
  31 Capital social
  33 Reservas
  36 Resultados          → Utilidad del ejercicio (3610)

Clase 4 — Ingresos       (lo que gana la empresa)
  41 Operacionales       → Ventas (4135)

Clase 5 — Gastos         (lo que gasta la empresa)
  51 Administración
  52 Ventas

Clase 6 — Costos de venta
  61 Costo de ventas     → Mercancías vendidas (6135)
```

**¿Puedo agregar mis propias cuentas?**
Sí. Los estudiantes pueden crear subcuentas auxiliares (nivel 3 y 4) bajo cualquier cuenta existente. Por ejemplo, bajo la cuenta 1110 Bancos se puede crear 111005 Bancolombia y 111010 Davivienda. Esto es exactamente lo que hacen los contadores en la práctica.

---

## Módulo 3 — Terceros (Clientes y Proveedores)

Los terceros son todas las personas naturales o jurídicas con las que la empresa tiene relaciones comerciales. En Colombia, la clasificación más importante es si el tercero es **cliente**, **proveedor** o **ambos**.

**Tipos de documento soportados:**
- CC — Cédula de Ciudadanía (personas naturales)
- NIT — Número de Identificación Tributaria (empresas)
- CE — Cédula de Extranjería
- Pasaporte

**Información que se registra:**
- Tipo y número de documento
- Nombre o razón social
- Tipo: cliente, proveedor o ambos
- Régimen fiscal (simplificado o común) — importante para calcular retenciones
- Dirección, teléfono, correo electrónico

**¿Por qué importa el régimen del tercero?**
En Colombia, cuando se compra a un proveedor del régimen común, generalmente se le aplica retención en la fuente. Cuando el proveedor es del régimen simplificado, las reglas cambian. ContaEdu registra esta información para que los estudiantes aprendan a identificarla.

---

## Módulo 4 — Productos e Inventario

Cada producto tiene una ficha con su precio de venta, precio de costo y las cuentas contables a las que impacta cuando se vende o compra.

**Campos clave:**
- Código y nombre del producto
- Unidad de medida: und, kg, lt, m, caja, par, otro
- Precio de venta y precio de costo
- **Tarifa de IVA:** 0%, 5% o 19% — según la naturaleza del bien o servicio
- Cuenta de inventario (ej: 1435 Mercancías)
- Cuenta de ingresos (ej: 4135 Ventas)
- Cuenta de costo de ventas (ej: 6135 Costo de ventas)

**¿Por qué se vinculan cuentas al producto?**
Porque cuando se vende un producto, el sistema genera automáticamente el asiento contable correcto usando las cuentas configuradas. El estudiante aprende que **vender no es solo emitir una factura**, sino que detrás hay un movimiento contable preciso.

**Tarifas de IVA en Colombia:**
- **19%** — tarifa general (la mayoría de bienes y servicios)
- **5%** — tarifa diferencial (algunos alimentos, medicamentos, etc.)
- **0%** — exento o excluido (alimentos de la canasta familiar, servicios médicos, libros)

---

## Módulo 5 — Facturación y Ventas

Este módulo cubre todo el ciclo de ventas: desde crear la factura hasta recibir el pago del cliente.

### 5.1 Facturas de venta

Una factura tiene tres estados posibles:

```
BORRADOR → EMITIDA → ANULADA
```

- **Borrador**: se puede editar libremente. No genera ningún movimiento contable.
- **Emitida**: la factura queda firme. En ese momento el sistema genera automáticamente el asiento contable de doble partida.
- **Anulada**: se revierte el asiento contable.

**Asiento automático al confirmar una venta:**
```
Débito  1305 Cuentas por cobrar     $total con IVA
Crédito 4135 Ingresos por ventas    $subtotal sin IVA
Crédito 2408 IVA por pagar         $valor del IVA
Débito  6135 Costo de ventas        $costo del producto
Crédito 1435 Inventario             $costo del producto
```

El sistema verifica que débitos = créditos antes de guardar. Si no cuadran, la operación se revierte completa. Así funciona la contabilidad de doble partida.

### 5.2 Notas crédito

Una nota crédito se emite cuando se necesita anular parcialmente una factura (devolución, descuento posterior). Reduce la cartera del cliente y los ingresos registrados.

### 5.3 Recibos de caja

Cuando el cliente paga, se registra un recibo de caja que salda la cuenta por cobrar:
```
Débito  1105 Caja (o 1110 Bancos)   $monto recibido
Crédito 1305 Cuentas por cobrar     $monto recibido
```

---

## Módulo 6 — Compras

El ciclo de compras sigue una lógica similar al de ventas pero desde la perspectiva del comprador.

### 6.1 Órdenes de compra

Una orden de compra es el documento que la empresa envía al proveedor solicitando mercancía. Estados: Pendiente → Parcial → Recibida → Cancelada.

### 6.2 Facturas de compra

Cuando llega la mercancía con su factura, se registra la factura de compra. Al confirmarla, el sistema genera:
```
Débito  1435 Inventario             $subtotal
Débito  2408 IVA descontable        $IVA de la compra
Crédito 2205 Proveedores nacionales $total de la factura
```

### 6.3 Pagos a proveedores

Cuando se le paga al proveedor:
```
Débito  2205 Proveedores nacionales $monto pagado
Crédito 1105 Caja (o 1110 Bancos)  $monto pagado
```

---

## Módulo 7 — Contabilidad y Reportes

### 7.1 Libro Diario

Muestra todos los asientos contables en orden cronológico, con referencia al documento que los originó (factura de venta #001, pago a proveedor, etc.). Es el registro primario de toda actividad contable.

### 7.2 Libro Mayor por cuenta

Permite seleccionar una cuenta del PUC y ver todos sus movimientos con saldo acumulado. Por ejemplo: ver todos los movimientos de la cuenta 1305 (Cuentas por cobrar) para saber cuánto se adeuda en total.

### 7.3 Balance de comprobación

Lista todas las cuentas activas con:
- Saldo inicial del período
- Total débitos del período
- Total créditos del período
- Saldo final

**Regla de oro**: la suma de todos los débitos debe ser igual a la suma de todos los créditos. Si no cuadra, hay un error contable.

### 7.4 Estado de Resultados

```
Ingresos operacionales (clase 4)
- Costo de ventas       (clase 6)
- Gastos operacionales  (clase 5)
= Utilidad (o pérdida) del ejercicio
```

### 7.5 Balance General

```
ACTIVOS (clase 1)  =  PASIVOS (clase 2)  +  PATRIMONIO (clase 3)
```

Esta ecuación debe cuadrar siempre. Si no cuadra, hay un error en los asientos.

### 7.6 Cartera por cobrar (Aging)

Muestra las facturas de venta pendientes de pago, agrupadas por días de vencimiento (0-30, 31-60, 61-90, más de 90 días). En la práctica contable colombiana esto se llama **análisis de cartera** y es fundamental para la gestión del flujo de caja.

### 7.7 Cuentas por pagar

Lo mismo pero desde la perspectiva de lo que la empresa debe a sus proveedores.

### 7.8 Exportación a PDF

Todos los reportes se pueden exportar a PDF con el logo y datos de la empresa. Esto simula los informes que un contador presenta a la gerencia o a la DIAN.

---

## Módulo 8 — Facturación Electrónica Simulada

Este es el módulo más avanzado y el que más se acerca a la realidad del mercado colombiano actual. Desde 2020, la **DIAN obliga a la mayoría de empresas** a emitir facturas electrónicas en formato UBL 2.1 con validación previa.

### ¿Qué es la facturación electrónica?

En Colombia, una factura electrónica no es simplemente un PDF. Es un **documento XML** con estructura UBL 2.1 que debe:
1. Generarse con un código único (CUFE) calculado con SHA-384
2. Enviarse a la DIAN **antes** de entregársela al cliente
3. La DIAN la valida y devuelve un ApplicationResponse (autorización)
4. Solo después de esa validación, la factura es legalmente válida

ContaEdu **simula todo este proceso internamente** sin conectarse a la DIAN real, lo que permite a los estudiantes vivir la experiencia completa sin riesgos.

### 8.1 Resolución de autorización

Antes de emitir la primera factura electrónica, la empresa debe registrar una **resolución de autorización DIAN**. En la vida real, la DIAN autoriza un prefijo (ej: "SEDU") y un rango de números (ej: del 1 al 1000) por un período de tiempo.

ContaEdu crea automáticamente la primera resolución (SEDU, rango 1-1000), pero el estudiante puede crear nuevas resoluciones y ver cómo al activar una nueva, la anterior queda desactivada.

**Campos de una resolución:**
- Número de resolución DIAN (simulado: 18760000001)
- Prefijo (ej: SEDU, FE, FV)
- Rango autorizado: desde / hasta
- Fechas de vigencia
- Clave técnica (UUID generado automáticamente — en la DIAN real es un código secreto)

### 8.2 El CUFE (Código Único de Factura Electrónica)

El CUFE es la huella digital de cada factura electrónica. Se calcula así:

```
CUFE = SHA384( NumFac + FecFac + HorFac + ValFac + "01" + ValIVA +
               "04" + ValINC + "03" + ValICA + ValTot +
               NitOFE + NumAdq + ClaveTec + TipoAmb )
```

El resultado es una cadena de **96 caracteres hexadecimales**, única para cada factura. Cualquier modificación al documento cambia el CUFE, lo que hace imposible falsificar facturas sin que se detecte.

### 8.3 El ciclo de vida de una factura electrónica

```
BORRADOR
   ↓ (se hace clic en "Emitir")
GENERADA → XML creado + CUFE calculado
   ↓ (enviada al simulador DIAN)
VALIDADA ✓   o   RECHAZADA ✗
   ↓ (si fue validada)
Se puede ANULAR (con nota crédito electrónica)
```

**Estado RECHAZADA:** el simulador puede rechazar la factura por errores técnicos (CUFE inválido, NIT emisor = NIT receptor, etc.). El estudiante puede corregir los datos y reenviar.

**Probabilidades del simulador (ambiente educativo):**
- 92% → Factura validada exitosamente
- 5% → Aprobada con advertencia (rango de numeración casi agotado)
- 3% → Error transitorio (se puede reenviar)
- Rechazo inmediato → si el NIT del emisor es igual al del receptor, o si el CUFE no tiene 96 caracteres

### 8.4 El XML UBL 2.1

ContaEdu genera el XML completo que exige la DIAN, con todas las secciones requeridas:

```xml
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">

  <!-- Datos de la resolución DIAN -->
  <cac:OrderReference>
    <cbc:ID>SEDU-0001</cbc:ID>
  </cac:OrderReference>

  <!-- Datos del emisor (empresa del estudiante) -->
  <cac:AccountingSupplierParty>...</cac:AccountingSupplierParty>

  <!-- Datos del receptor (cliente) -->
  <cac:AccountingCustomerParty>...</cac:AccountingCustomerParty>

  <!-- Líneas de detalle con impuestos -->
  <cac:InvoiceLine>...</cac:InvoiceLine>

  <!-- Totales por tipo de impuesto -->
  <cac:TaxTotal>...</cac:TaxTotal>

  <!-- Total legal de la factura -->
  <cac:LegalMonetaryTotal>...</cac:LegalMonetaryTotal>

</Invoice>
```

El estudiante puede ver el XML completo desde el detalle de la factura. Esto ayuda a entender por qué la facturación electrónica requiere software especializado.

### 8.5 Eventos del receptor (RADIAN)

Una vez que la factura está validada, el cliente puede registrar eventos sobre ella. En Colombia esto hace parte del sistema **RADIAN**, que permite usar la factura electrónica como título valor negociable (endosable, descontable en bancos).

Los eventos disponibles deben registrarse en orden:

| Código | Evento | ¿Qué significa? |
|---|---|---|
| 030 | Acuse de recibo | El cliente confirma que recibió la factura |
| 032 | Recibo del bien o servicio | El cliente confirma que recibió la mercancía |
| 033 | Aceptación expresa | El cliente acepta formalmente la deuda. No puede reclamar después. |
| 031 | Reclamo | El cliente rechaza total o parcialmente la factura. Solo válido antes de la aceptación expresa. |

**Regla importante:** el evento 031 (reclamo) solo puede registrarse dentro de los 5 días siguientes a la emisión, y no después de una aceptación expresa (033).

### 8.6 Nota crédito electrónica

Para anular una factura electrónica validada, se debe emitir una **nota crédito electrónica** (no se puede simplemente borrar). La nota crédito tiene su propio código CUDE (calculado igual que el CUFE pero incluyendo el CUFE de la factura original).

Códigos de concepto disponibles:
- **01** — Devolución parcial de bienes
- **02** — Anulación de factura
- **03** — Rebaja total aplicada
- **04** — Descuento total aplicado
- **05** — Rescisión: nulidad por falta de requisitos
- **06** — Otros

---

## Módulo 9 — Panel del Docente

### 9.1 Gestión de empresas

El docente puede crear empresas para cada estudiante de dos maneras:

**Creación individual:** ingresa la cédula del estudiante, su nombre completo, el nombre de la empresa y el NIT. El sistema crea automáticamente el schema de base de datos, ejecuta las migraciones, siembra el PUC y la resolución DIAN por defecto.

**Creación masiva:** el docente descarga una plantilla CSV, la llena con los datos de todos los estudiantes del grupo y la sube al sistema. ContaEdu procesa el archivo fila por fila y muestra un resumen de éxitos y errores.

### 9.2 Modo Auditoría

Desde el panel del docente, se puede hacer clic en "Auditar empresa" junto al nombre de cualquier estudiante. El sistema entonces:

1. Inicializa el tenant del estudiante en la sesión del docente
2. Activa un banner rojo visible en la parte superior de todas las páginas: **"Modo auditoría — empresa de [nombre] — Solo lectura"**
3. Oculta todos los botones de guardar, confirmar y anular
4. Permite navegar libremente todos los módulos del estudiante

Para salir del modo auditoría, el docente hace clic en "Salir de auditoría" y regresa a su panel normal.

### 9.3 Panel Comparativo

Vista de tabla con todas las empresas del grupo y sus métricas en tiempo real:
- Número de facturas emitidas y total facturado
- Número de compras registradas
- Balance de caja estimado
- Si el balance general cuadra (activos = pasivos + patrimonio)
- Última actividad

Esto permite al docente identificar rápidamente qué estudiantes están avanzando y cuáles necesitan ayuda.

### 9.4 Rúbrica de Calificación

El docente puede asignar una nota de 1.0 a 5.0 por cada módulo evaluado:

| Módulo | Descripción |
|---|---|
| Maestros contables | ¿Configuró correctamente clientes, proveedores y productos? |
| Facturación y cobro | ¿Registró ventas, notas crédito y recibos de caja? |
| Compras y pagos | ¿Registró compras y pagos a proveedores? |
| Cierre contable | ¿Los reportes cuadran (BS = P+E, débitos = créditos)? |

La nota final se calcula como promedio ponderado. El sistema guarda el historial de notas con el nombre del docente que calificó.

---

## Flujo de trabajo recomendado para el estudiante

Este es el orden sugerido para completar el ciclo contable completo:

```
1. Configurar la empresa
   └─ Ingresar NIT, razón social, régimen, dirección

2. Revisar el Plan de Cuentas
   └─ Entender la estructura del PUC y crear subcuentas si se necesitan

3. Registrar terceros
   └─ Crear al menos 3 clientes y 3 proveedores

4. Registrar productos
   └─ Crear al menos 5 productos con sus cuentas contables

5. Registrar compras
   └─ Orden de compra → Factura de compra → Pago al proveedor

6. Registrar ventas
   └─ Factura de venta → Recibo de caja (cobro)

7. Verificar los reportes
   └─ Balance de comprobación → Estado de Resultados → Balance General

8. Facturación Electrónica (opcional, para nivel avanzado)
   └─ Resolver → Crear factura electrónica → Emitir → Ver XML → Registrar eventos
```

---

## Lo que ContaEdu simula (y lo que es diferente en la vida real)

| Aspecto | En ContaEdu | En la realidad |
|---|---|---|
| **DIAN** | Simulador interno (sin conexión externa) | Software certificado con certificado digital |
| **CUFE** | Calculado con SHA-384 real | Idéntico — mismo algoritmo |
| **XML UBL 2.1** | Generado con la estructura oficial | Debe ir firmado con firma digital XAdES-EPES |
| **Numeración** | Controlada por la resolución registrada | Asignada y validada por la DIAN |
| **Retenciones** | Informativas (campo total_retenciones) | Calculadas automáticamente según tablas DIAN |
| **Ambiente** | Siempre 02 (Pruebas) | 01 (Producción) para documentos legales |
| **RADIAN** | Eventos simulados internamente | Transmisión real a la DIAN |
| **Base de datos** | Schema PostgreSQL por estudiante | Base de datos o schema por empresa real |

---

## Qué falta (mejoras propuestas para el contexto colombiano)

A continuación se describen funcionalidades que están parcialmente implementadas o que podrían agregarse para hacer el simulador aún más completo:

### Retenciones (alta prioridad educativa)

Las retenciones son un tema central del sistema tributario colombiano que todo contador debe dominar:

- **Retención en la fuente** (RteFte): porcentaje que el comprador retiene al proveedor sobre el valor de la compra. Varía según el concepto del pago (3.5% compra de bienes, 6% servicios en general, 11% honorarios, etc.).
- **Reteiva**: retención del 15% del IVA, aplicada cuando el comprador es retenedor de IVA (generalmente grandes contribuyentes).
- **Reteica**: retención del impuesto de industria y comercio, varía por municipio y actividad económica.

El sistema tiene el campo `total_retenciones` en las facturas electrónicas, pero no tiene la lógica para calcularlas automáticamente ni el módulo de certificados de retención.

### Notas Débito

La tabla existe en la base de datos pero no tiene interfaz de usuario. Una nota débito se emite cuando la empresa le cobra un ajuste adicional a un cliente (intereses de mora, gastos adicionales no facturados inicialmente).

### ICA por municipio

El Impuesto de Industria y Comercio varía según el municipio donde se desarrolla la actividad económica. Bogotá, Medellín, Cali y cada municipio tienen sus propias tarifas. Sería valioso que el sistema incluya una tabla de tarifas ICA por municipio y código DANE.

### Libro auxiliar de IVA

Los responsables de IVA deben presentar declaración bimestral o cuatrimestral. Un reporte de "IVA generado vs IVA descontable" del período ayudaría a los estudiantes a entender cómo se prepara la declaración de IVA (Formulario 300 de la DIAN).

### Cuentas bancarias y conciliación

Registrar los extractos bancarios y conciliarlos contra el libro de bancos (cuenta 1110) es una tarea cotidiana en cualquier empresa. Esto enseña a los estudiantes a identificar transacciones en tránsito, cheques no cobrados y errores.

### Códigos CIIU

El Código Industrial Internacional Uniforme es obligatorio en la facturación electrónica. Sería útil incluir un selector de código CIIU en la configuración de la empresa.

### Activos fijos y depreciación

Las empresas tienen equipos, muebles y vehículos que se deprecian con el tiempo. Un módulo básico de activos fijos (costo histórico, vida útil, depreciación mensual por el método de línea recta) agregaría un concepto contable importante al ciclo educativo.

### Calendario tributario

Una vista con las fechas límite de las principales obligaciones tributarias (IVA, renta, ICA, retención en la fuente) ayudaría a los estudiantes a entender que la contabilidad tiene plazos legales críticos.

---

## Preguntas frecuentes

**¿Los datos de mi empresa son reales o simulados?**
Todo es simulado. La empresa existe solo dentro de ContaEdu y no genera ningún efecto legal, tributario ni comercial real. Puedes cometer errores sin consecuencias.

**¿Qué pasa si cometo un error en una factura ya confirmada?**
Una factura emitida no se puede editar. Debes anularla (lo que genera un asiento de reverso) y crear una nueva. Así funciona en la realidad: en Colombia, las facturas son documentos legales que no se modifican.

**¿Por qué mi factura electrónica fue rechazada por el simulador?**
Las causas más comunes son: NIT del emisor igual al NIT del receptor (no puedes facturarte a ti mismo), CUFE con longitud incorrecta, o resolución vencida. Revisa los datos y reenvía.

**¿El docente puede ver todas mis transacciones?**
Sí. En modo auditoría, el docente tiene acceso de solo lectura a toda la información de tu empresa: facturas, compras, pagos, asientos contables y reportes. Esto es parte del proceso educativo.

**¿Cuántas facturas puedo emitir con una resolución?**
La resolución por defecto tiene un rango del 1 al 1.000. Cuando se acerque al límite, el sistema te avisará. Puedes crear una nueva resolución cuando lo necesites.

---

## Glosario de términos contables colombianos

| Término | Definición |
|---|---|
| **PUC** | Plan Único de Cuentas. Catálogo estándar de cuentas contables en Colombia (Decreto 2650/1993). |
| **NIT** | Número de Identificación Tributaria. Código de identificación de personas jurídicas ante la DIAN. El dígito de verificación se calcula por el algoritmo módulo 11. |
| **DIAN** | Dirección de Impuestos y Aduanas Nacionales. Entidad que regula los impuestos y la facturación en Colombia. |
| **IVA** | Impuesto al Valor Agregado. En Colombia las tarifas son 0%, 5% y 19%. |
| **ICA** | Impuesto de Industria y Comercio. Impuesto municipal que grava la actividad económica. |
| **INC** | Impuesto Nacional al Consumo. Aplica a restaurantes, telefonía, vehículos. |
| **Retención en la fuente** | Mecanismo de recaudo anticipado de impuestos. El pagador retiene un porcentaje al cobrador. |
| **Régimen simplificado** | Contribuyentes de bajos ingresos que no son responsables de IVA. |
| **Régimen común** | Contribuyentes responsables de IVA que deben declarar periódicamente. |
| **CUFE** | Código Único de Factura Electrónica. Hash SHA-384 de 96 caracteres que identifica unívocamente cada FE. |
| **CUDE** | Código Único de Documento Electrónico. Equivalente del CUFE para notas crédito/débito. |
| **UBL 2.1** | Universal Business Language versión 2.1. Estándar XML para documentos electrónicos. |
| **RADIAN** | Sistema de gestión de facturas electrónicas como títulos valor negociables. |
| **Partida doble** | Principio contable: todo movimiento tiene un débito y un crédito igual. Débitos = Créditos. |
| **Asiento de cierre** | Asiento que traslada los saldos de ingresos y gastos a la cuenta de resultados al final del período. |
| **Cartera** | Conjunto de facturas pendientes de cobro a clientes. |
| **CxP** | Cuentas por pagar. Deudas con proveedores. |
| **Aging** | Análisis de cartera por antigüedad de la deuda (0-30, 31-60, 61-90, +90 días). |

---

*ContaEdu — Desarrollado para la formación contable colombiana.*
*Todos los documentos generados en esta plataforma son exclusivamente educativos y no tienen validez legal.*
