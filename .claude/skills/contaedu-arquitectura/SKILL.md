---
name: contaedu-arquitectura
description: >-
  Arquitectura completa de ContaEdu. Activar SIEMPRE al inicio de cualquier
  tarea — antes de tocar código. Cubre multitenancy, zonas sandbox/real,
  separación Central/Tenant, servicios contables y flujo de datos.
---

# ContaEdu — Arquitectura General

## Stack

- Laravel 12, Livewire 4, PostgreSQL, Tailwind CSS v4
- Multi-tenancy: `stancl/tenancy` con schema separado por tenant (PostgreSQL search_path)
- Deploy: Railway (producción), Laravel Herd (local Windows)
- IDE: JetBrains WebStorm

---

## Multitenancy: Central vs Tenant

### Base de datos `public` (Central)
Tablas compartidas entre todos los tenants. Modelos en `app/Models/Central/`:

```
tenants                  → cada estudiante es un tenant
institutions             → instituciones educativas
groups                   → grupos de clase
intercompany_invoices    → facturas del mercado interempresarial
intercompany_invoice_items
intercompany_journal_entries → vínculos asiento ↔ tenant
portafolio_items         → productos que cada empresa ofrece al mercado
transfer_requests
platform_notifications
```

### Schema por tenant (Tenant)
Cada estudiante tiene su propio schema PostgreSQL. Modelos en `app/Models/Tenant/`:

```
accounts                 → PUC (Plan Único de Cuentas)
journal_entries          → asientos contables
journal_lines            → líneas de cada asiento
invoices / invoice_lines → facturas de venta
purchase_invoices        → facturas de compra
purchase_orders          → órdenes de compra
payments / payment_items → pagos a proveedores
cash_receipts            → recibos de caja
credit_notes / debit_notes
fixed_assets             → activos fijos
bank_accounts            → cuentas bancarias simuladas
bank_transactions        → movimientos bancarios
bank_reconciliations     → conciliaciones bancarias
thirds                   → clientes, proveedores, empleados
products                 → inventario con cuentas PUC vinculadas
company_config           → configuración de la empresa virtual
fe_*                     → facturación electrónica simulada (DIAN)
```

### Regla crítica de multitenancy

**NUNCA** mezclar modelos Central y Tenant en el mismo contexto de tenant.
Antes de hacer `tenancy()->initialize($otroTenant)`, pre-cargar todos los
modelos Central que se necesiten. Después del switch, solo acceder a modelos Tenant.

```php
// ✅ CORRECTO
$sellerTenant = CentralTenant::on('pgsql')->find($sellerId); // pre-cargar
tenancy()->initialize($sellerTenant);
$entry = JournalEntry::create([...]); // modelo Tenant, ok

// ❌ INCORRECTO
tenancy()->initialize($sellerTenant);
$tenant = CentralTenant::find($id); // FALLA — search_path ya cambió
```

---

## Dos zonas: Aprendizaje vs Mi Empresa

### Campo `modo` en tablas operativas

Las tablas operativas tienen `modo VARCHAR(10) DEFAULT 'real'`:

```
journal_entries, invoices, purchase_invoices, purchase_orders,
payments, cash_receipts, credit_notes, debit_notes,
fixed_assets, bank_reconciliations
```

Valores: `'real'` | `'sandbox'`

### Helper `modoContable()`

```php
// app/helpers.php
function modoContable(): string
{
    return request()->is('aprendizaje/*') ? 'sandbox' : 'real';
}
```

### Rutas por zona

```
/aprendizaje/*  → name prefix: sandbox.*  → modo=sandbox
/empresa/*      → name prefix: student.*  → modo=real
```

### Qué tiene cada zona

```
APRENDIZAJE (/aprendizaje/*)    MI EMPRESA (/empresa/*)
────────────────────────────    ───────────────────────
✅ PUC Interactivo              ✅ PUC Interactivo
✅ Facturas                     ✅ Facturas
✅ Compras                      ✅ Compras
✅ Terceros                     ✅ Terceros
✅ Productos                    ✅ Productos
✅ Plan de cuentas              ✅ Plan de cuentas
✅ Reportes                     ✅ Reportes
✅ Calendario                   ✅ Calendario
✅ Activos fijos                ✅ Activos fijos
✅ Conciliación                 ✅ Conciliación
✅ F. Electrónica               ✅ F. Electrónica
✅ Configuración                ✅ Configuración
✅ Empresas Maestras            ✅ Empresas Maestras
❌ Negocios                     ✅ Negocios ← solo real
❌ Banco                        ✅ Banco ← solo real
```

### Regla: Banco y Negocios siempre modo real

`bank_accounts` y `bank_transactions` NO tienen campo `modo`.
`IntercompanyService` NO llama a `modoContable()` — siempre opera en real.

### Scope para filtrar por modo

```php
// En modelos con campo modo:
public function scopeModoActual($query): void
{
    $query->where('modo', modoContable());
}

// Uso en queries:
Invoice::modoActual()->get();
JournalEntry::modoActual()->whereBetween('date', [...])->get();
```

---

## Roles y autenticación

```
superadmin  → guard: web,    /admin/*
coordinator → guard: web,    /coordinador/*
teacher     → guard: web,    /docente/*
student     → guard: student, /empresa/* y /aprendizaje/*
```

Los docentes pueden auditar empresas de estudiantes en modo solo lectura
mediante `AuditController` y `CoordinatorAudit`.

---

## Servicios principales

| Servicio | Responsabilidad |
|----------|----------------|
| `AccountingService` | Genera asientos para facturas, compras, pagos, depreciación, notas |
| `IntercompanyService` | Acepta/anula negocios interempresariales, asientos en ambas empresas |
| `BankService` | GMF, ACH, cuotas de manejo, creación de cuentas bancarias |
| `ReportService` | Libro diario, mayor, balance, estado de resultados, IVA |
| `RetencionService` | Cálculo de retenciones (fuente, IVA, ICA) |
| `FixedAssetService` | Depreciación mensual línea recta |
| `BankReconciliationService` | Conciliación bancaria |

---

## Flujo de un asiento contable

```
Operación (factura/compra/pago)
    ↓
AccountingService::generate*Entry()
    ↓ llama a createEntry() que estampa modo=modoContable()
    ↓
JournalEntry::create() + JournalLine::create() × N
    ↓
Aparece en /aprendizaje/reportes (si sandbox)
    o en /empresa/reportes (si real)
```

---

## Estructura de archivos clave

```
app/
  Http/Controllers/
    Student/          → TenantDashboard, SandboxController, ReferenceController
    Teacher/          → AuditController, DemoController, TeacherDashboard
    Admin/            → AdminDashboard
  Livewire/
    Tenant/           → components compartidos entre ambas zonas
      Invoices/Index.php
      Compras/Index.php
      Banco/Index.php
      Negocios/Index.php
      Reportes/Index.php
      PlanDeCuentas.php
      PucInteractivo.php  ← nuevo (TASK 11)
    Student/          → Referencias.php, NotificationBell.php
    Teacher/          → paneles docente
  Models/
    Central/          → modelos BD pública
    Tenant/           → modelos BD por estudiante
  Services/           → lógica de negocio
database/
  migrations/
    tenant/           → migraciones que corren en cada schema de estudiante
    (raíz)            → migraciones de BD central (public)
  seeders/
    PucSeeder.php     → siembra las 9 clases del PUC con todas las cuentas
    TenantDatabaseSeeder.php → llama PucSeeder + FeResolucionSeeder
routes/
  web.php             → todas las rutas (admin, teacher, student)
```

---

## Convenciones importantes

1. **Nunca** hardcodear códigos PUC en vistas — siempre pasar por `Account::where('code', ...)`
2. **Siempre** validar cuadre débitos = créditos en `createEntry()` — lanza `AccountingImbalanceException`
3. Las migraciones de tenant van en `database/migrations/tenant/` con fecha `2026_04_XX_XXXXXX`
4. Un cambio en un Livewire component de `Tenant/` aplica automáticamente a AMBAS zonas
5. Para lógica exclusiva de una zona usar `request()->is('aprendizaje/*')`
