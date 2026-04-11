---
name: contaedu-asientos-puc
description: >-
  Mapa completo de asientos contables PUC colombiano para ContaEdu.
  Activar cuando se trabaje con AccountingService, IntercompanyService,
  asientos de facturas/compras/pagos/banco, o cualquier lógica contable.
  Evita que Claude Code improvise cuentas incorrectas.
---

# ContaEdu — Asientos PUC Colombiano

## Cuentas PUC más usadas en ContaEdu

```
ACTIVO
1105        Caja (naturaleza: débito)
1110        Bancos (naturaleza: débito)
1305        Clientes — CxC (naturaleza: débito)
1355        Anticipo de impuestos y contribuciones (naturaleza: débito)
  135515    Retención en la fuente sufrida
  135517    IVA retenido sufrido
  135518    ICA retenido sufrido
1435        Mercancías no fabricadas por la empresa (naturaleza: débito)
1445        Semovientes — inventario (naturaleza: débito)
1504        Terrenos PP&E (naturaleza: débito)
1516        Equipo de cómputo (naturaleza: débito)
1592        Depreciación acumulada (naturaleza: crédito — cuenta correctora)
1576        Semovientes PP&E activos fijos biológicos (naturaleza: débito)

PASIVO
2205        Proveedores nacionales — CxP (naturaleza: crédito)
2335        Costos y gastos por pagar (naturaleza: crédito)
2365        Retención en la fuente practicada (naturaleza: crédito)
2367        Retención IVA practicada — Reteiva (naturaleza: crédito)
2368        Retención ICA practicada (naturaleza: crédito)
2372        Retención ICA practicada (alternativa) (naturaleza: crédito)
2408        IVA por pagar / IVA descontable (naturaleza: crédito)
  240810    IVA descontable (subcuenta débito dentro de 2408)

PATRIMONIO
3105        Capital suscrito y pagado (naturaleza: crédito)
3605        Utilidad del ejercicio (naturaleza: crédito)

INGRESOS (naturaleza: crédito — se cierran al final del período)
4105        Agricultura, ganadería, caza y silvicultura
4110        Pesca
4115        Explotación de minas y canteras
4135        Comercio al por mayor y al por menor
4195        Diversas actividades de servicios

GASTOS (naturaleza: débito — se cierran al final del período)
5105        Gastos de personal — nómina
5115        Honorarios
5160        Gasto depreciación
5195        Gastos generales / diversos
5305        Gastos financieros
  530520    GMF 4x1000

COSTOS DE VENTA (naturaleza: débito)
6135        Costo de ventas — mercancías
```

---

## Mapa de asientos por operación

### 1. Factura de venta (AccountingService::generateSaleEntry)

```
DR 1305  Clientes                total (subtotal + IVA)
CR 4xxx  Ingresos                subtotal  ← cuenta según actividad CIIU
CR 2408  IVA por pagar           iva       ← solo si IVA > 0

Si hay costo registrado en el producto:
DR 6135  Costo de ventas         costo
CR 1435  Mercancías              costo
```

### 2. Nota crédito (AccountingService::generateCreditNoteEntry)

```
DR 4135  Ingresos                subtotal  ← reversa el ingreso
DR 2408  IVA por pagar           iva       ← reversa el IVA
CR 1305  Clientes                total     ← reduce la cartera
```

### 3. Nota débito (AccountingService::generateDebitNoteEntry)

```
DR 1305  Clientes                total     ← aumenta cartera
CR 4135  Ingresos                subtotal
CR 2408  IVA por pagar           iva
```

### 4. Recibo de caja (AccountingService::generateReceiptEntry)

```
DR 1105  Caja                    total     ← o 1110 si es banco
CR 1305  Clientes                total     ← cancela cartera
```

### 5. Factura de compra con OC (AccountingService::generatePurchaseEntry)

```
DR 1435  Mercancías (inventario) subtotal
DR 2408  IVA descontable         iva
CR 2205  Proveedores             neto_a_pagar  ← bruto - retenciones
CR 2365  Retención fuente        retefte       ← si aplica
CR 2367  Retención IVA           reteiva       ← si aplica
CR 2368  Retención ICA           reteica       ← si aplica
```

### 6. Factura de compra directa (AccountingService::generateDirectPurchaseEntry)

```
DR xxx   Cuenta gasto/activo     subtotal_línea  ← según cuenta_gasto_codigo
DR 240810 IVA descontable        iva_total
CR 2205  Proveedores             neto_a_pagar
CR 2365  Retención fuente        retefte
CR 2367  Retención IVA           reteiva
CR 2368  Retención ICA           reteica
```

### 7. Pago a proveedor (AccountingService::generatePaymentEntry)

```
DR 2205  Proveedores             total     ← cancela CxP
CR 1110  Bancos                  total     ← si paga por banco
  (o CR 1105 Caja si paga en efectivo)

Si paga por banco, también:
DR 530520 GMF 4x1000             gmf
CR 1110  Bancos                  gmf
```

### 8. Depreciación mensual (AccountingService::generateDepreciationEntry)

```
DR 5160  Gasto depreciación      cuota_mensual
CR 1592  Depreciación acumulada  cuota_mensual
```

Fórmula: `cuota = (costo - valor_residual) / vida_util_meses`
Vidas útiles normativas colombianas (NIC 16 NIIF pymes):
- Equipos de cómputo: 36 meses (3 años)
- Maquinaria: 120 meses (10 años)
- Vehículos: 60 meses (5 años)
- Edificios: 240 meses (20 años)

### 9. Capital inicial (AutoMigrateTenant::seedCapitalInicial)

```
DR 1110  Bancos                  100_000_000
CR 3105  Capital suscrito        100_000_000
```

---

## Asientos del mercado interempresarial (IntercompanyService)

### Al aceptar un negocio — empresa VENDEDORA

```
DR 1305  Clientes                neto_a_cobrar  ← bruto - retenciones sufridas
DR 1355  Anticipo impuestos      retencion_fuente_sufrida
DR 1355  Anticipo impuestos      retencion_iva_sufrida
DR 1355  Anticipo impuestos      retencion_ica_sufrida
CR 4xxx  Ingresos                subtotal  ← cuenta_ingreso_codigo del ítem
CR 2408  IVA por pagar           iva
```

> ⚠️ Pendiente mejorar: las tres retenciones sufridas deberían ir a
> subcuentas específicas (135515, 135517, 135518) en lugar de 1355 genérico.

### Al aceptar un negocio — empresa COMPRADORA

```
DR xxx   Cuenta gasto/activo     subtotal  ← gasto_code_comprador elegido
DR 240810 IVA descontable        iva
CR 2205  Proveedores             neto_a_pagar
CR 2365  Retención fuente        retencion_fuente
CR 2367  Retención IVA           retencion_iva
CR 2372  Retención ICA           retencion_ica  ← o 2365 si no existe 2372
```

### Pago bancario del negocio — empresa COMPRADORA

```
DR 2205  Proveedores             total_factura
DR 530520 GMF 4x1000            gmf
DR 5305  Gastos financieros      comision_ach   ← si bancos distintos
CR 1110  Bancos                  total + gmf + comision_ach
```

### Cobro bancario del negocio — empresa VENDEDORA

```
DR 1110  Bancos                  neto_cobrado
CR 1305  Clientes                neto_cobrado
```

---

## GMF 4x1000

Aplica en: `retiro`, `transferencia_salida`, `cheque`, `pago_proveedor`
NO aplica en: consignaciones, transferencias entrantes, cobros de clientes

```php
// BankService::calcularGmf()
$gmf = round($valor * 0.004);
```

---

## Comisión ACH

Solo aplica cuando comprador y vendedor tienen bancos distintos:

```
Bancolombia origen:  $4.200
Davivienda origen:   $3.800
Banco Bogotá origen: $4.500
Mismo banco:         $0
```

---

## Reglas de cuadre

- Siempre: `sum(débitos) === sum(créditos)` — tolerancia 0.01
- `AccountingService::createEntry()` valida esto y lanza `AccountingImbalanceException`
- Nunca crear `JournalLine` sin pasar por `createEntry()`

---

## Cómo buscar el ID de una cuenta

```php
// Siempre así — nunca hardcodear IDs
private function accountId(string $code): ?int
{
    return Account::where('code', $code)->value('id');
}

// Con fallback
$vatDescontable = $this->accountId('240810') ?? $this->accountId('2408');
```

---

## Cuenta de ingreso según actividad CIIU

Los productos tienen `cuenta_ingreso_codigo` que mapea al PUC:

```
4105 → Agricultura, ganadería, caza y silvicultura
4110 → Pesca
4115 → Explotación de minas y canteras
4120 → Industria manufacturera
4135 → Comercio al por mayor y al por menor
4155 → Hoteles y restaurantes
4175 → Transporte y comunicaciones
4195 → Diversas actividades de servicios
```

Si el producto no tiene cuenta asignada → usar `4135` como fallback.
