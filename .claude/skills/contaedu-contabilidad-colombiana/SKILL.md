---
name: contaedu-contabilidad-colombiana
description: >-
  Normativa contable y tributaria colombiana aplicada a ContaEdu.
  Activar SIEMPRE que se trabaje con facturación, impuestos, retenciones,
  factura electrónica, reportes financieros, activos fijos, conciliación
  o cualquier lógica contable/tributaria. Evita errores de normativa colombiana.
---

# ContaEdu — Contabilidad y Tributaria Colombiana

## Estado actual de la plataforma

### ✅ Implementado y funcional

**Facturación de venta**
- Ciclo completo: borrador → emitida → anulada
- Estados: `InvoiceStatus` (borrador, emitida, anulada)
- Tipos de documento: facturas, notas crédito, notas débito, recibos de caja
- Tarifas IVA: `TaxRate` (Exento 0%, Reducido 5%, General 19%)
- Asientos automáticos: DR 1305 / CR 4xxx / CR 2408

**Factura electrónica DIAN (simulador)**
- CUFE: SHA-384 según Resolución DIAN 000042/2020
- CUDE: para notas crédito (incluye CUFE de factura origen)
- XML UBL 2.1: estructura completa sin firma digital XAdES-EPES
- Ambiente de pruebas (código 02)
- Resoluciones de numeración con rango y vigencia

**Compras**
- Ciclo: orden de compra → factura de proveedor → pago
- Retenciones en compras: fuente, IVA, ICA
- Factura directa sin OC (cada línea con su cuenta de gasto)
- `RetencionService`: calcula RteFte, Reteiva, Reteica

**Retenciones — conceptos implementados** (`ConceptoRetencion`)
```
ComprasGenerales  3.5%  base mínima 27 UVT ($1.128.000)
Servicios         4.0%  base mínima 4 UVT  ($128.000)
Honorarios        11.0% sin base mínima
Comisiones        11.0% sin base mínima
Arrendamientos    3.5%  base mínima 27 UVT ($853.000)
Transporte        3.5%  base mínima 4 UVT  ($128.000)
```

**Reteiva:** 15% del IVA (régimen simplificado → gran contribuyente)
**Reteica:** porcentaje variable por municipio, el usuario lo ingresa

**Reportes financieros**
- Libro diario
- Libro mayor por cuenta
- Balance de comprobación
- Estado de resultados (ingresos - costos - gastos)
- Balance general (activos = pasivos + patrimonio)
- Cartera por cobrar con aging
- Cuentas por pagar
- Libro auxiliar de IVA (2408, 2367, 2368)

**Activos fijos**
- Depreciación línea recta: `(costo - valor_residual) / vida_útil_meses`
- Vidas útiles normativas NIC 16 NIIF pymes:
  - Equipo de cómputo: 36 meses
  - Maquinaria: 120 meses
  - Vehículos: 60 meses
  - Edificios: 240 meses
- Asiento: DR 5160 / CR 1592

**Conciliación bancaria**
- Compara saldo libro (1110) vs extracto bancario
- Partidas conciliatorias: depósitos en tránsito, cheques en circulación
- Estados: borrador → conciliado

**Calendario tributario**
- Fechas vencimiento por últimos 2 dígitos NIT (calendario DIAN 2025)
- Obligaciones: RteFte mensual, IVA (bimestral/cuatrimestral/anual), renta, ICA, exógena
- Regímenes: simplificado, responsable IVA, gran contribuyente

**Banco simulado**
- GMF 4x1000 en retiros, transferencias salida, pagos proveedor
- ACH entre bancos distintos (Bancolombia $4.200, Davivienda $3.800, Banco Bogotá $4.500)
- Cuotas de manejo por banco y tipo de cuenta

---

### ⚠️ Implementado parcialmente — requiere atención

**Retenciones sufridas (vendedor en interempresarial)**
- Las 3 retenciones van a `1355` genérico
- Debería usar subcuentas específicas:
  - `135515` Retención en la fuente sufrida
  - `135517` IVA retenido sufrido
  - `135518` ICA retenido sufrido

**Reteiva en compras**
- Implementada al 15% del IVA
- Normativa correcta 2024: 50% del IVA (gran contribuyente compra a responsable)
- El 15% aplica cuando se compra a no responsable de IVA
- ⚠️ Revisar y corregir la tarifa según el caso

**Libro auxiliar de IVA**
- Incluye 2408, 2367, 2368 pero no muestra el IVA por período bimestral
- Falta el cálculo del IVA neto a pagar (generado - descontable - reteiva)

**Estado de resultados**
- Agrupa por tipo (ingreso/costo/gasto) pero no por clase PUC
- Falta desglose: ingresos operacionales vs no operacionales

**Balance general**
- Funcional pero falta verificación del cuadre patrimonio
- La utilidad del ejercicio (3605) no se actualiza automáticamente

---

### ❌ No implementado — falta según normativa colombiana

**Nómina electrónica**
- Documento soporte de nómina electrónica (DIAN)
- Liquidación de prestaciones sociales: prima, cesantías, intereses cesantías, vacaciones
- Aportes parafiscales: SENA 2%, ICBF 3%, Caja de compensación 4%
- Seguridad social: salud 8.5% empleador, pensión 12% empleador, ARL variable
- Retención en la fuente sobre salarios (Art. 383 E.T. — ya existe el concepto pero no los asientos)

**Cierre contable**
- No existe el proceso de cierre de período
- Debe cerrar cuentas 4 (ingresos), 5 (gastos), 6 (costos) contra 3605 (utilidad)
- Asiento de cierre: DR 4xxx / CR 5xxx → diferencia a 3605

**Declaración de renta**
- Solo existe el calendario, no el cálculo
- Renta líquida, deducciones, renta presuntiva

**INC (Impuesto Nacional al Consumo)**
- No implementado
- Aplica en restaurantes, bares, telefonía: 8%

**Descuentos comerciales y financieros**
- Descuento en factura vs descuento por pronto pago
- Asientos diferentes: descuento en factura reduce la base, descuento financiero va a 4210

**Anticipos de clientes**
- No existe la cuenta 2805 Anticipos recibidos de clientes
- Cuando un cliente paga antes de recibir el servicio/producto

**Ajuste por diferencia en cambio**
- No implementado (moneda extranjera)

---

## Reglas de normativa que Claude Code debe respetar

### IVA colombiano
```
Tarifa general: 19% (Ley 1819/2016)
Tarifa reducida: 5% (bienes de la canasta familiar ampliada)
Excluidos: no causan IVA (alimentos básicos, medicamentos, libros)
Exentos: causan IVA pero a tarifa 0% (exportaciones, algunos servicios)

IVA descontable: el pagado en compras se resta del IVA generado en ventas
IVA neto a pagar = IVA generado (CR 2408) - IVA descontable (DR 2408)
```

### Retención en la fuente — reglas clave
```
Solo retiene quien es AGENTE RETENEDOR:
- Personas jurídicas
- Personas naturales con ingresos > 92.000 UVT el año anterior
- Entidades del Estado

NO retiene:
- Régimen simplificado (no responsable de IVA)
- Personas naturales sin la obligación

Base = subtotal SIN IVA
Se retiene EN EL MOMENTO DEL PAGO O ABONO EN CUENTA (lo que ocurra primero)
```

### Reteiva — tarifas correctas 2024
```
75% del IVA: gran contribuyente compra a responsable de IVA
50% del IVA: responsable de IVA designado compra a responsable
15% del IVA: cualquier agente retenedor compra a no responsable (simplificado)
```
> ⚠️ ContaEdu usa 15% — correcto solo para compras a régimen simplificado

### Factura electrónica — requisitos DIAN
```
Obligatorio desde 2019 para grandes contribuyentes
Obligatorio desde 2022 para la mayoría de contribuyentes

Elementos obligatorios del XML UBL 2.1:
- CUFE (SHA-384)
- Fecha y hora de emisión
- NIT emisor con DV
- Documento adquirente
- Descripción de bienes/servicios
- Valores unitarios y totales
- IVA desglosado por tarifa
- Forma de pago (contado/crédito)
- Medio de pago

El simulador de ContaEdu usa ambiente 02 (pruebas)
NO incluye firma XAdES-EPES (solo educativo)
```

### Ciclo contable completo
```
1. Apertura: capital inicial (DR 1110 / CR 3105)
2. Operaciones: facturas, compras, pagos durante el período
3. Ajustes: depreciación, causaciones, provisiones
4. Conciliación bancaria
5. Cierre: cerrar cuentas de resultado → utilidad a 3605
6. Estados financieros: balance general + estado de resultados
```

### Estructura del estado de resultados (PUC colombiano)
```
(+) Ingresos operacionales          clase 4
(-) Costos de ventas                clase 6
(=) Utilidad bruta
(-) Gastos operacionales            clase 5
(=) Utilidad operacional
(+/-) Ingresos/gastos no operacionales
(=) Utilidad antes de impuestos
(-) Impuesto de renta (33% o tarifa vigente)
(=) Utilidad neta del ejercicio     → 3605
```

### Estructura del balance general (PUC colombiano)
```
ACTIVO
  Activo corriente: 11 (disponible), 12 (inversiones CP), 13 (deudores), 14 (inventarios)
  Activo no corriente: 15 (PP&E), 16 (intangibles), 17 (diferidos)

PASIVO
  Pasivo corriente: 21 (obligaciones financieras CP), 22 (proveedores), 23 (cuentas por pagar), 24 (impuestos)
  Pasivo no corriente: 25 (obligaciones laborales), 26-29

PATRIMONIO
  31 (capital), 32 (superávit), 33 (reservas), 36 (resultados del ejercicio)

Ecuación: ACTIVO = PASIVO + PATRIMONIO
```

---

## Enums disponibles en ContaEdu

```php
TaxRate::Exento   // 0%
TaxRate::Reducido // 5%
TaxRate::General  // 19%

InvoiceStatus::Borrador | Emitida | Anulada
PurchaseInvoiceStatus::Borrador | Recibida | Pagada | Anulada
PurchaseOrderStatus::Borrador | Enviada | Recibida | Anulada
PaymentStatus::Pendiente | Aplicado | Anulado
ReceiptStatus::Borrador | Aplicado | Anulado

ConceptoRetencion::ComprasGenerales | Servicios | Honorarios | Comisiones | Arrendamientos | Transporte

FixedAssetCategory // categorías de activos fijos
FixedAssetStatus::Activo | Dado_De_Baja | Vendido

ThirdType::Cliente | Proveedor | Empleado | Ambos

TipoOperacionEnum // tipos de operación FE
TipoImpuestoEnum  // tipos de impuesto FE
TipoDocumentoEnum // tipos de documento FE
EstadoFacturaEnum // estados de FE
```

---

## Servicios contables — cuándo usar cada uno

```php
AccountingService::generateSaleEntry()        // Factura de venta confirmada
AccountingService::generateSaleReversal()     // Anulación de factura
AccountingService::generateReceiptEntry()     // Recibo de caja
AccountingService::generateCreditNoteEntry()  // Nota crédito aplicada
AccountingService::generateDebitNoteEntry()   // Nota débito emitida
AccountingService::generatePurchaseEntry()    // Factura compra con OC
AccountingService::generateDirectPurchaseEntry() // Factura compra directa
AccountingService::generatePaymentEntry()     // Pago a proveedor
AccountingService::generateDepreciationEntry() // Depreciación mensual

RetencionService::calcular()  // Calcula RteFte + Reteiva + Reteica

ReportService::libroDiario()          // Todos los asientos del período
ReportService::libroMayor()           // Movimientos de una cuenta con saldo
ReportService::balanceComprobacion()  // Todas las cuentas con saldos
ReportService::estadoResultados()     // P&G del período
ReportService::balanceGeneral()       // Balance a una fecha
ReportService::carteraPorCobrar()     // Facturas pendientes con aging
ReportService::cuentasPorPagar()      // Compras pendientes de pago
ReportService::libroIva()             // Movimientos de IVA del período

FixedAssetService::runMonthlyDepreciation()   // Depreciación de todos los activos
BankReconciliationService::create()           // Nueva conciliación
CalendarioTributarioService::generar()        // Calendario obligaciones tributarias
IntercompanyService::accept()                 // Aceptar negocio interempresarial
BankService::calcularGmf()                    // GMF 4x1000
BankService::costoAch()                       // Comisión ACH entre bancos
```

---

## Lo que NO debe hacer Claude Code sin consultar

1. **Cambiar tarifas de impuestos** sin verificar la norma vigente
2. **Modificar el cálculo del CUFE** — es un estándar DIAN exacto
3. **Crear cuentas PUC nuevas** que no existan en el seeder
4. **Cambiar el orden de los asientos** — débitos siempre antes que créditos por convención
5. **Asumir que toda compra genera retención** — verificar siempre el umbral mínimo
6. **Cerrar cuentas de resultado automáticamente** — el cierre es un proceso explícito
