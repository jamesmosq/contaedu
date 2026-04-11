# TASK 10 — Cuenta Destino del Vendedor en Negocios

> ⚠️ Prerequisito: TASK 08 completado y verificado.
> ⚠️ Este TASK es un ajuste sobre el flujo de compra en Negocios que ya existe.
>     No crea tablas nuevas — solo ajusta la UI y la lógica del pedido existente.

---

## Contexto

El flujo de compra en Negocios ya funciona. El comprador puede:
- Ver el portafolio del vendedor
- Seleccionar productos/servicios
- Elegir su forma de pago (a crédito o desde sus cuentas bancarias)

Lo que falta: **mostrar a qué cuenta bancaria del vendedor va el dinero** y permitirle al vendedor elegir cuál de sus cuentas recibe el pago cuando tiene dos.

---

## Problema actual

El comprador selecciona su cuenta de pago pero no sabe a dónde va el dinero:

```
FORMA DE PAGO
[ A crédito        ] [ Davivienda ***8352  ] [ Banco de Bogotá ***7755 ]
  Queda en 2205        $49.176.355              $50.000.000
  Proveedores

← El comprador no sabe a qué cuenta del vendedor llega el dinero
```

---

## Solución

### Parte 1 — El vendedor configura su cuenta receptora en Mi Portafolio

Cuando el vendedor publica su portafolio, define cuál de sus cuentas recibe los pagos:

```
Mi portafolio — Configuración de cobros
─────────────────────────────────────────────────────
¿En qué cuenta deseas recibir los pagos de tus ventas?

[ 🔴 Davivienda Corriente ***8352   — $49.176.355  ]  ← seleccionada
[ 🟢 Banco de Bogotá Corriente ***7755 — $50.000.000 ]

Esta cuenta se mostrará a los compradores al hacer un pedido.
```

Esta preferencia se guarda en la tabla `portafolio_settings` o como campo en `bank_accounts`:

```php
// En bank_accounts agregar:
$table->boolean('recibe_pagos_negocios')->default(false);
```

Si el vendedor solo tiene una cuenta → se usa automáticamente, sin configuración.
Si tiene dos → debe elegir cuál recibe los pagos de Negocios.

---

### Parte 2 — El comprador ve la cuenta destino al hacer el pedido

En la pantalla de confirmación del pedido, agregar la sección **"Cuenta destino del vendedor"** entre los ítems del pedido y la forma de pago:

```
Tu pedido
─────────────────────────────────────────
Pescado    1    $300.000    0%    $300.000
─────────────────────────────────────────
Subtotal:                        $300.000
Total del pedido:                $300.000

Cuenta destino del vendedor:
🔴 Davivienda ***8352 (TechNova SAS)

FORMA DE PAGO
[ A crédito           ] [ Davivienda ***8352  ] [ Banco de Bogotá ***7755 ]
  Queda en 2205           $49.176.355              $50.000.000
  Proveedores
```

**Lógica visual:**
- Si comprador y vendedor tienen el mismo banco → resaltar con badge verde "Mismo banco — sin costo ACH"
- Si son bancos diferentes → mostrar badge amarillo "Banco diferente — aplica comisión ACH + GMF"

```
Cuenta destino del vendedor:
🔴 Davivienda ***8352 (TechNova SAS)

FORMA DE PAGO
[ A crédito ] [ 🔴 Davivienda ***8352 ✅ Mismo banco ] [ 🟢 Banco de Bogotá ⚠️ ACH $4.500 + GMF ]
               $49.176.355 — Sin costo ACH              $50.000.000 — Costo adicional $54.500
```

Esto enseña al comprador a tomar decisiones financieras inteligentes — elegir pagar desde el mismo banco del vendedor para evitar costos.

---

### Parte 3 — Ajuste en la lógica de contabilización

Cuando el comprador confirma el pedido pagando desde una cuenta bancaria, el sistema ya sabe:
- Cuenta origen: la que seleccionó el comprador
- Cuenta destino: la cuenta receptora del vendedor

El Job de contabilización (`ProcessIntercompanyInvoiceJob`) debe registrar en `bank_transactions`:

**En la empresa del comprador:**
```php
BankTransaction::create([
    'bank_account_id' => $cuentaOrigenComprador->id,
    'tipo' => 'transferencia_salida',
    'valor' => $invoice->total,
    'banco_destino' => $cuentaDestinoVendedor->bank,
    'cuenta_destino' => $cuentaDestinoVendedor->account_number,
    'descripcion' => "Pago pedido #{$invoice->consecutive} - {$vendedor->nombre}",
    'gmf' => round($invoice->total * 0.004),
    'comision' => $comisionACH, // $0 mismo banco, $3.800-$4.500 otro banco
    'saldo_despues' => $nuevoSaldoComprador,
    'fecha_transaccion' => now(),
]);
```

**En la empresa del vendedor:**
```php
BankTransaction::create([
    'bank_account_id' => $cuentaDestinoVendedor->id,
    'tipo' => 'transferencia_entrada',
    'valor' => $invoice->total,
    'banco_destino' => $cuentaOrigenComprador->bank,
    'cuenta_destino' => $cuentaOrigenComprador->account_number,
    'descripcion' => "Cobro pedido #{$invoice->consecutive} - {$comprador->nombre}",
    'gmf' => 0, // la entrada no genera GMF
    'comision' => 0,
    'saldo_despues' => $nuevoSaldoVendedor,
    'fecha_transaccion' => now(),
]);
```

---

### Parte 4 — Validación antes de confirmar el pedido

Antes de procesar, el sistema valida:

```
1. ¿El vendedor tiene cuenta bancaria activa para recibir?
   → Si no tiene → mostrar: "Este vendedor no puede recibir pagos bancarios aún.
     Solo puedes hacer la compra a crédito (cuenta 2205 Proveedores)."

2. ¿El comprador tiene saldo suficiente en la cuenta seleccionada?
   → Si no → mostrar: "Saldo insuficiente. Selecciona otra cuenta o elige pago a crédito."

3. ¿La transferencia supera el límite diario del banco?
   → Solo Banco de Bogotá: límite $25.000.000 por día
   → Si supera → mostrar: "Este pago supera el límite diario de Banco de Bogotá ($25.000.000).
     Selecciona otra cuenta."
```

---

## Resumen de cambios en el código existente

| Archivo | Cambio |
|---------|--------|
| `bank_accounts` migration | Agregar campo `recibe_pagos_negocios` boolean |
| `PortafolioComponent` o similar | Agregar selector de cuenta receptora |
| `ComprarComponent` o similar | Mostrar cuenta destino del vendedor + badges de costo |
| `ProcessIntercompanyInvoiceJob` | Registrar `bank_transactions` en ambas empresas |
| Vista de pedido | Sección "Cuenta destino del vendedor" |

---

## Lo que NO se toca
- Tablas existentes de pedidos/ofertas
- Lógica de asientos contables existente — solo se agrega el registro bancario
- Módulo de portafolio completo — solo se agrega el selector de cuenta receptora
- El flujo "A crédito" — sigue funcionando exactamente igual

---

## Verificación

1. Vendedor con una cuenta → se usa automáticamente como cuenta receptora
2. Vendedor con dos cuentas → puede elegir cuál recibe pagos en Mi portafolio
3. Comprador ve la cuenta destino del vendedor en el formulario del pedido
4. Comprador y vendedor en el mismo banco → badge verde "Sin costo ACH"
5. Bancos diferentes → badge amarillo con el costo exacto de la comisión
6. Comprador elige pagar desde mismo banco del vendedor → sin comisión ACH en extracto
7. Comprador elige pagar desde otro banco → comisión ACH + GMF en su extracto
8. Vendedor recibe en su extracto la transferencia correctamente
9. Saldo insuficiente → bloqueo con mensaje claro
10. Límite diario Banco de Bogotá → validación al superar $25.000.000
