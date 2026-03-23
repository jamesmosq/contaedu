# Plan de Implementación — Mejoras ContaEdu

> **Documento de trabajo para Claude Code.** Actualizar el estado de cada tarea al completarla.
> Última actualización: 2026-03-23

---

## Estado general

| # | Funcionalidad | Estado |
|---|---|---|
| 1 | Retenciones en compras (RteFte / Reteiva / Reteica) | ✅ Completado |
| 2 | Notas Débito en ventas | ✅ Completado |
| 3 | Libro Auxiliar de IVA | ✅ Completado |
| 4 | Códigos CIIU en configuración empresa | ✅ Completado |
| 5 | Calendario tributario colombiano | ✅ Completado |
| 6 | Activos fijos y depreciación (línea recta) | ✅ Completado |
| 7 | Conciliación bancaria | ✅ Completado |

---

## Funcionalidad 1 — Retenciones ✅

### Archivos creados/modificados
- `app/Enums/ConceptoRetencion.php` — enum con porcentajes y bases mínimas UVT 2024
- `app/Services/RetencionService.php` — cálculo de RteFte / Reteiva / Reteica
- `app/Models/Tenant/PurchaseInvoice.php` — fillable + cast + método `tieneRetenciones()`
- `app/Services/AccountingService.php` — `generatePurchaseEntry()` actualizado con cuentas 2365/2367/2368
- `app/Services/PurchaseService.php` — `confirmInvoice()` acepta `?array $retenciones`
- `app/Livewire/Tenant/Compras/Index.php` — modal de retenciones con cálculo en vivo
- `resources/views/livewire/tenant/compras/index.blade.php` — modal UI
- `database/seeders/PucSeeder.php` — añadidas cuentas 2365, 2367, 2368
- `database/migrations/tenant/2025_02_01_000001_create_retencion_configs_table.php`
- `database/migrations/tenant/2025_02_01_000002_add_retenciones_to_purchase_invoices.php`
- `database/migrations/tenant/2025_02_01_000003_seed_retencion_accounts.php`

### Lógica de asiento de compra con retenciones
```
DR 1435 Inventario        subtotal
DR 2408 IVA descontable   tax_amount
  CR 2205 Proveedores       total_neto (bruto - retenciones)
  CR 2365 RteFte            retefte_valor (si > 0)
  CR 2367 Reteiva           reteiva_valor (si > 0)
  CR 2368 Reteica           reteica_valor (si > 0)
```

---

## Funcionalidad 2 — Notas Débito ✅

### Archivos creados/modificados
- `app/Enums/DebitNoteStatus.php` — Borrador / Emitida / Anulada con label() y color()
- `app/Models/Tenant/DebitNote.php` — modelo con SoftDeletes, relaciones, helpers
- `app/Models/Tenant/Invoice.php` — relación `debitNotes()`, `amountDebited()`, `balance()` actualizado
- `app/Services/DebitNoteService.php` — create() / confirm() / annul()
- `app/Services/AccountingService.php` — `generateDebitNoteEntry()` + `generateDebitNoteReversal()`
- `app/Livewire/Tenant/Invoices/Index.php` — propiedades dn_*, métodos openDebitNote / saveDebitNote / confirmDebitNote / annulDebitNote
- `resources/views/livewire/tenant/invoices/index.blade.php` — tab "Notas débito" + modal creación + botón en tabla facturas
- `database/migrations/tenant/2025_02_02_000001_add_subtotal_tax_to_debit_notes.php`

### Lógica de asiento nota débito
```
Confirmar:
  DR 1305 Cuentas por cobrar   amount (subtotal + tax_amount)
    CR 4135 Ingresos           subtotal
    CR 2408 IVA por pagar      tax_amount

Anular (reverso):
  Invierte el asiento original
```

### Pendiente para aplicar en BD
```bash
php artisan tenants:migrate
```

---

## Funcionalidad 3 — Libro Auxiliar de IVA ✅

### Objetivo
Reporte que muestre todos los movimientos de IVA del período: IVA generado en ventas (2408 CR), IVA descontable en compras (2408 DR), IVA de retenciones (2367), saldo neto a pagar/favor a la DIAN.

### Archivos a crear/modificar

1. **`app/Services/ReportService.php`** — agregar método:
   ```php
   public function libroIva(string $from, string $to): array
   // Retorna: iva_ventas, iva_compras, reteiva_retenida, saldo_dian, movimientos[]
   ```
   - Consultar `journal_lines` donde `account_id` es la cuenta 2408 y 2367
   - Agrupar: ventas (líneas CR de facturas de venta) vs compras (líneas DR de facturas de compra)

2. **`app/Livewire/Tenant/Reports/Index.php`** — agregar tab `'iva'`:
   - Propiedades: `iva_from`, `iva_to`, `ivaData`
   - Método `loadIvaReport()` que llama `ReportService::libroIva()`

3. **`resources/views/livewire/tenant/reports/index.blade.php`** — nuevo tab "IVA":
   - Filtro de fechas (from/to)
   - Tabla resumen: IVA ventas | IVA compras | Reteiva | Saldo DIAN
   - Tabla detalle de movimientos
   - Botón exportar PDF

4. **`resources/views/pdf/libro_iva.blade.php`** — vista PDF con DomPDF

### Estructura del reporte
```
DECLARACIÓN AUXILIAR DE IVA — Período: MM/YYYY

IVA GENERADO (ventas)
  Fecha | Factura | Cliente | Base gravable | Tarifa | IVA

IVA DESCONTABLE (compras)
  Fecha | Factura | Proveedor | Base gravable | Tarifa | IVA desc.

RETEIVA PRACTICADA (por nosotros a proveedores régimen simplificado)
  ...

RESUMEN
  Total IVA generado:     $
  Total IVA descontable:  $
  Total Reteiva:          $
  Saldo a pagar DIAN:     $   (positivo = pagar, negativo = a favor)
```

---

## Funcionalidad 4 — Códigos CIIU ✅

### Objetivo
Permitir que la empresa registre su actividad económica principal con código CIIU colombiano, visible en reportes PDF y en la configuración de empresa.

### Archivos a crear/modificar

1. **Migración central** `database/migrations/XXXX_create_ciiu_codes_table.php`:
   ```sql
   ciiu_codes: id, code (varchar 6), name, section, division, active
   ```
   - Tabla en schema `public` (datos de referencia compartidos)
   - Seeder con ~50 códigos CIIU más comunes en Colombia

2. **`app/Models/Central/CiiuCode.php`** — modelo central (sin tenancy):
   ```php
   class CiiuCode extends CentralModel { ... }
   ```

3. **Migración tenant** `database/migrations/tenant/XXXX_add_ciiu_to_company_config.php`:
   ```sql
   ALTER TABLE company_config ADD COLUMN ciiu_code VARCHAR(6) NULL;
   ALTER TABLE company_config ADD COLUMN ciiu_description VARCHAR(255) NULL;
   ```

4. **`app/Livewire/Tenant/CompanyConfig/Index.php`** — agregar:
   - Propiedad `ciiu_code`
   - En `render()`: pasar `$ciiuCodes = CiiuCode::orderBy('code')->get()`
   - En `save()`: guardar `ciiu_code`

5. **`resources/views/livewire/tenant/company-config/index.blade.php`** — agregar:
   - Select searchable de CIIU con Alpine.js (filtro por texto)
   - Mostrar nombre del código seleccionado

6. **Actualizar vistas PDF** de reportes para incluir CIIU en el encabezado de la empresa.

### Códigos CIIU prioritarios (seeder)
```
4711 - Comercio al por menor en establecimientos no especializados con surtido compuesto principalmente de alimentos, bebidas o tabaco
4719 - Otros tipos de comercio al por menor en establecimientos no especializados
6201 - Actividades de desarrollo de sistemas informáticos
6202 - Actividades de consultoría informática y actividades de administración de instalaciones informáticas
8211 - Actividades combinadas de servicios administrativos de oficina
6920 - Actividades de contabilidad, teneduría de libros, auditoría financiera y asesoría tributaria
... (completar con ~50 más frecuentes)
```

---

## Orden de ejecución recomendado

1. `php artisan tenants:migrate` — aplicar migraciones pendientes de Func. 1 y 2
2. Implementar Funcionalidad 3 (Libro IVA) — solo PHP + Blade, sin nuevas migraciones
3. Implementar Funcionalidad 4 (CIIU) — requiere migración central + tenant

---

## Notas de arquitectura importantes

- **AccountingService**: método `accountId(string $code)` busca la cuenta por código. Si no existe, devuelve `null` y la línea se omite del asiento (no lanza excepción). Esto es intencional para permitir seeders parciales.
- **Tolerancia de balanceo**: `AccountingImbalanceException` se lanza si `|debits - credits| > 0.01`
- **Retenciones**: El campo `total` en `purchase_invoices` se actualiza a `total_a_pagar` (neto) cuando hay retenciones. El ciclo de pagos ya funciona sin cambios adicionales.
- **DebitNote.amount**: campo legacy, contiene el total (subtotal + tax_amount). `subtotal` y `tax_amount` se añadieron con la migración de Func. 2.
- **Schema search path**: Al hacer queries con `information_schema` pueden aparecer duplicados por tenant — es comportamiento esperado de PostgreSQL con `search_path` activo.
