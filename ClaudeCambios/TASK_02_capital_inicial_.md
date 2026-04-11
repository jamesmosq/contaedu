# TASK 02 — Capital Inicial $100M + Panel de Saldo en Negocios

> ⚠️ Prerequisito: TASK 01 completado y verificado.
> ⚠️ Este TASK tiene dos partes independientes. La Parte A no depende de Negocios. La Parte B extiende el módulo Negocios.

---

## PARTE A — Capital Inicial Automático de $100M

### Objetivo
Al crear una empresa estudiantil, el sistema registra automáticamente un capital inicial de $100.000.000 COP con su asiento contable. Esto garantiza que el estudiante tenga un balance real desde el primer día.

### Asiento contable a registrar

```
DÉBITO:  1110 Bancos                      $100.000.000
CRÉDITO: 3105 Capital suscrito y pagado   $100.000.000
Concepto: "Capital inicial de constitución"
Fecha: fecha de creación del tenant
```

### Dónde implementar — sin romper nada

ContaEdu ya tiene un proceso de creación de tenants. Hay que identificar el punto exacto donde el tenant ya existe, las migraciones ya corrieron y el PUC ya fue sembrado por `PucSeeder`. Recién ahí se registra el asiento.

**Verificar primero:**
1. ¿Existe un Observer en el modelo Tenant? → agregar lógica ahí
2. ¿Existe un Job que corre después de crear el tenant? → agregar al final
3. ¿Existe un Seeder de inicialización? → agregar el asiento ahí

**Reglas críticas para no romper nada:**
- El asiento solo se crea si `journal_entries` del tenant está vacía — nunca en tenants existentes
- Usar `DB::transaction()` — si falla el asiento, no afecta la creación del tenant
- Las cuentas `1110` y `3105` deben existir (ya están en PucSeeder) — verificar antes de insertar
- No modificar la lógica de creación del tenant — solo agregar al final del proceso

```php
// Pseudocódigo seguro
tenancy()->initialize($tenant);

$yaTieneAsientos = DB::table('journal_entries')->exists();
if (!$yaTieneAsientos) {
    $cuenta1110 = DB::table('accounts')->where('code', '1110')->first();
    $cuenta3105 = DB::table('accounts')->where('code', '3105')->first();

    if ($cuenta1110 && $cuenta3105) {
        DB::transaction(function () use ($cuenta1110, $cuenta3105) {
            // Crear asiento de constitución
        });
    }
}
```

### Verificación Parte A
1. Crear un tenant/empresa estudiantil nuevo
2. Ir a Plan de cuentas → `1110 Bancos` muestra saldo $100.000.000
3. Ir a Reportes → Libro diario muestra el asiento de constitución
4. Balance: Activo = Pasivo + Patrimonio desde el día 1
5. Crear un segundo tenant → el asiento NO se duplica
6. Tenants existentes → NO se les crea ningún asiento nuevo

---

## PARTE B — Panel de Saldo en Módulo Negocios

### Objetivo
Mostrar un panel visual en el módulo Negocios con el saldo disponible de la empresa en tiempo real. El saldo refleja el saldo real de la cuenta `1110 Bancos` del PUC — no es un campo separado, es una lectura directa de la contabilidad.

### Principio fundamental — no duplicar datos
El saldo NO se guarda en una tabla aparte. Se calcula en tiempo real desde `journal_entry_lines` (o como se llamen las líneas de asiento) sumando débitos menos créditos de la cuenta `1110`. Esto garantiza que siempre esté en sincronía con la contabilidad real.

```php
// Cálculo del saldo disponible
$saldo = DB::table('journal_entry_lines')
    ->join('accounts', 'accounts.id', '=', 'journal_entry_lines.account_id')
    ->where('accounts.code', '1110')
    ->selectRaw('SUM(debit) - SUM(credit) as saldo')
    ->value('saldo') ?? 0;
```

### Panel visual en Negocios

Agregar un panel informativo en la parte superior del módulo Negocios, **antes** de las pestañas (Mi portafolio, Nueva oferta, Enviadas, Recibidas, Historial):

```
┌─────────────────────────────────────────────────────────────────┐
│  PANEL DE MI EMPRESA EN EL MERCADO                              │
├──────────────────┬──────────────────┬───────────────────────────┤
│ 💰 Saldo en caja │ 📤 Por cobrar    │ 📥 Por pagar              │
│ $87.500.000      │ $15.000.000      │ $5.000.000                │
│ (cuenta 1110)    │ (cuenta 1305)    │ (cuenta 2205)             │
└──────────────────┴──────────────────┴───────────────────────────┘
```

- **Saldo en caja**: suma de `1110 Bancos` (débitos - créditos)
- **Por cobrar**: suma de `1305 Clientes` (débitos - créditos)
- **Por pagar**: suma de `2205 Proveedores` (créditos - débitos)

Los tres valores se leen directamente del PUC — son datos reales, no simulados.

### Cuándo se actualiza
- Cada vez que el estudiante entra al módulo Negocios
- Después de aceptar/rechazar una oferta (Livewire refresh automático)
- No requiere polling ni WebSockets — carga al montar el componente

---

## PARTE C — Distinción Producto vs Servicio en el Portafolio

> ⚠️ Esta parte coordina con TASK_03 (Mi portafolio). Leer junto a ese documento.

### Problema a resolver
Si un estudiante vende productos físicos desde su portafolio, el inventario debe disminuir. Si vende servicios, no hay inventario que tocar.

### Regla clara

| Tipo en portafolio | Tiene stock | Al vender |
|-------------------|-------------|-----------|
| Producto | ✅ Sí | Disminuye stock + asiento de costo |
| Servicio | ❌ No | Solo asiento de ingreso |

### Impacto en el asiento contable al aceptar una oferta

**Si es Producto:**
```
VENTA:
  Db 1305 Clientes            $xxx
  Cr 4135 Ingresos            $xxx

COSTO (automático):
  Db 6135 Costo de ventas     $xxx
  Cr 1435 Inventario          $xxx
```

**Si es Servicio:**
```
VENTA:
  Db 1305 Clientes            $xxx
  Cr 4160 Ingresos servicios  $xxx
  (No hay asiento de costo)
```

### Campo `stock` en portafolio_items (TASK_03)
En la tabla `portafolio_items` que se creará en TASK_03, el campo `stock` solo aplica cuando `tipo = 'producto'`:

```php
// En la migración de portafolio_items (TASK_03)
$table->integer('stock')->nullable(); // null = servicio (sin límite)
$table->integer('stock_minimo')->nullable()->default(0);
```

### Validación antes de aceptar una oferta
Al aceptar una oferta que incluye productos:
1. Verificar que el vendedor tiene stock suficiente
2. Si no tiene stock → rechazar automáticamente con mensaje claro
3. Si tiene stock → proceder con contabilización y descontar stock

```php
// En ProcessIntercompanyInvoiceJob
foreach ($invoice->items as $item) {
    if ($item->portafolioItem->tipo === 'producto') {
        if ($item->portafolioItem->stock < $item->cantidad) {
            throw new \Exception("Stock insuficiente para {$item->portafolioItem->nombre}");
        }
    }
}
```

---

## Resumen de lo que toca y lo que NO toca esta tarea

### ✅ Toca (con cuidado):
- El proceso de creación de tenants (solo al final, con verificación)
- La tabla `journal_entries` del tenant (solo si está vacía)
- El componente Livewire del módulo Negocios (panel informativo)
- La tabla `portafolio_items` de TASK_03 (campo stock)

### ❌ NO toca:
- La lógica de creación del tenant en sí
- El PucSeeder (ya funciona)
- Las facturas de venta existentes
- Las facturas de compra (TASK_06)
- El módulo de Productos del ciclo contable
- Los reportes existentes
- Tenants que ya existen en producción

---

## Orden de implementación recomendado

```
1. Implementar Parte A (capital inicial) → verificar en DB
2. Implementar Parte B (panel de saldo) → verificar visualmente
3. Coordinar Parte C con TASK_03 al implementar el portafolio
```

---

## Verificación completa

**Parte A:**
1. Crear empresa nueva → ver asiento en libro diario
2. Cuenta 1110 → saldo $100.000.000
3. Balance cuadrado: Activo = Pasivo + Patrimonio
4. Tenant existente → sin cambios

**Parte B:**
1. Ir a Negocios → ver panel con saldo $100.000.000
2. Aceptar una oferta de venta → saldo sube en el panel
3. Aceptar una oferta de compra → saldo baja en el panel
4. Los valores del panel coinciden con los reportes contables

**Parte C (cuando se implemente TASK_03):**
1. Portafolio con producto → tiene campo stock
2. Portafolio con servicio → no tiene stock
3. Vender producto → stock disminuye, asiento de costo generado
4. Vender servicio → solo asiento de ingreso
5. Stock insuficiente → oferta no se puede aceptar con mensaje claro
