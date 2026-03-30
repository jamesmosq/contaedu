# ContaEdu — Plataforma Contable Educativa
## Contexto del proyecto

Sistema web educativo tipo SaaS multi-tenant que simula un software contable similar a Siigo, para que estudiantes de administración y contabilidad colombiana practiquen el ciclo contable completo: clientes, proveedores, facturación, compras, contabilidad de doble partida y reportes financieros. Cada estudiante opera su propia empresa virtual completamente aislada. El docente audita todas las empresas del grupo.

**No usar MoonShine.** Todos los paneles (superadmin, docente, estudiante) se construyen con Blade + Livewire 3. Esto evita conflictos con el multitenancy y da control total sobre el flujo de auditoría.

---

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Framework | Laravel 11 (último estable) |
| Frontend | Livewire 3 + Alpine.js + Blade |
| Estilos | Tailwind CSS |
| Base de datos | **PostgreSQL** (local con pgAdmin/DataGrip, Railway en producción) |
| Multitenancy | `stancl/tenancy` v3 — modo **PostgreSQL schemas** (un servidor, un schema por tenant) |
| PDF | `barryvdh/laravel-dompdf` |
| Autenticación | Laravel Breeze adaptado + guard propio para estudiantes |
| Entorno local | WAMP64 (Windows) + PostgreSQL instalado por separado, dominio `contaedu.test` |
| Producción | Railway — un servicio Laravel + un servicio PostgreSQL |

---

## Arquitectura multi-tenant (PostgreSQL schemas)

`stancl/tenancy` en modo schema de PostgreSQL crea un schema separado dentro del mismo servidor de BD por cada tenant (empresa estudiantil). El schema `public` es la base de datos central (landlord). Cada empresa estudiantil tiene su propio schema, por ejemplo `tenant_cc12345678`.

Esto es ideal para Railway porque solo se necesita **una instancia de PostgreSQL**, no múltiples bases de datos.

```
PostgreSQL (una sola instancia)
├── schema: public          ← BD central: institutions, groups, users, tenants
├── schema: tenant_cc001    ← Empresa de estudiante 1
├── schema: tenant_cc002    ← Empresa de estudiante 2
└── schema: tenant_ccN      ← Empresa de estudiante N
```

### Identificación del tenant

NO se usa identificación por dominio (subdominios). Se identifica al tenant por **path** o por **request data** (cédula del estudiante en login). Esto simplifica Railway, que no maneja subdominios dinámicos fácilmente.

El flujo es:
1. Estudiante ingresa cédula + contraseña en `/login`
2. El sistema busca en la tabla `tenants` del schema `public` qué schema corresponde a esa cédula
3. Se inicializa el tenant: `tenancy()->initialize($tenant)`
4. Se redirige al dashboard de la empresa del estudiante

---

## Roles del sistema

| Rol | Guard | Acceso |
|---|---|---|
| `superadmin` | `web` | Panel central: CRUD de instituciones y docentes |
| `docente` | `web` | Panel de grupo: crear empresas, asignar estudiantes, modo auditoría |
| `estudiante` | `student` | Solo su empresa: todos los módulos contables |

---

## Módulos y fases de desarrollo

### Fase 1 — Fundación del sistema (ejecutar primero, completar antes de continuar)

**Objetivo**: sistema funcionando con login diferenciado por rol y multitenancy activo.

**Instalación base**:
```bash
composer create-project laravel/laravel contaedu
cd contaedu
composer require stancl/tenancy livewire/livewire barryvdh/laravel-dompdf
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install && npm run build
```

**Configurar `.env` para PostgreSQL**:
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=contaedu
DB_USERNAME=postgres
DB_PASSWORD=base1234
```

**Migraciones de BD central** (schema `public`):
```
institutions: id, name, nit, city, active, timestamps
groups: id, institution_id, teacher_id, name, period, active, timestamps
users: id, group_id, role (superadmin|teacher), name, email, password, remember_token, timestamps
tenants: id (string = cc del estudiante), student_name, company_name, nit_empresa, group_id, active, tenancy_db_name (schema name), timestamps
student_scores: id, tenant_id, module, score, notes, graded_by, timestamps
```

**Configuración de tenancy** (`config/tenancy.php`):
- Usar `PostgreSQLSchemaManager` para crear schemas por tenant
- El ID del tenant = cédula del estudiante (string, ej: `cc1023456789`)
- Schema name = `tenant_` + cédula del estudiante

**Guards**: crear guard `student` separado en `config/auth.php` con su propio provider que busca en la tabla `tenants` (no en `users`).

**Criterios de aceptación Fase 1**:
- [ ] Superadmin puede hacer login y crear una institución y un docente
- [ ] Docente puede hacer login y ver su panel
- [ ] Docente crea una empresa: ingresa cédula del estudiante, nombre, NIT empresa → se crea el schema `tenant_cc...` en PostgreSQL
- [ ] Estudiante hace login con cédula + contraseña y llega a su dashboard vacío
- [ ] En DataGrip/pgAdmin se puede verificar que el schema del estudiante existe y está vacío
- [ ] Seeder: 1 institución, 1 docente, 3 estudiantes con sus schemas y empresas

---

### Fase 2 — Maestros contables (dentro de cada tenant)

**Objetivo**: cada empresa tiene configuración inicial y puede registrar sus terceros y productos.

**Migraciones de tenant** (en `database/migrations/tenant/`):

```sql
company_config: nit, razon_social, regimen (simplificado|comun|gran_contribuyente),
                direccion, telefono, email, logo_path,
                prefijo_factura, resolucion_dian (texto libre, educativo),
                timestamps

accounts: id, code (varchar 10), name, type (activo|pasivo|patrimonio|ingreso|costo|gasto|orden),
          nature (debito|credito), parent_id (FK self), level (1-4), active, timestamps

thirds: id, document_type (cc|nit|ce|pasaporte), document, name, type (cliente|proveedor|ambos),
        regimen (simplificado|comun), address, phone, email, active, timestamps

products: id, code, name, description, unit (und|kg|lt|m|caja|par|otro),
          sale_price, cost_price,
          inventory_account_id (FK accounts), revenue_account_id (FK accounts),
          cogs_account_id (FK accounts), tax_rate (0|5|19), active, timestamps
```

**Seeder del PUC colombiano**: al crear cada tenant, sembrar automáticamente el Plan Único de Cuentas estándar de Colombia. Mínimo cuentas de nivel 2 de las clases 1 a 6:

```
Clase 1 - Activo
  11 Disponible (Caja, Bancos)
  12 Inversiones
  13 Deudores (Cuentas por Cobrar clientes 1305)
  14 Inventarios (1435 Mercancías no fabricadas por la empresa)
  15 Propiedades planta y equipo
  16 Intangibles

Clase 2 - Pasivo
  21 Obligaciones financieras
  22 Proveedores (2205 Nacionales)
  23 Cuentas por pagar
  24 Impuestos, gravámenes y tasas (2408 IVA por pagar)

Clase 3 - Patrimonio
  31 Capital social
  33 Reservas
  36 Resultados del ejercicio (3610 Utilidad del ejercicio)

Clase 4 - Ingresos
  41 Operacionales (4135 Comercio al por mayor y menor)

Clase 5 - Gastos
  51 Operacionales de administración
  52 Operacionales de ventas

Clase 6 - Costos de venta
  61 Costo de ventas (6135 Comercio al por mayor y menor)
```

**Criterios de aceptación Fase 2**:
- [ ] Al crear el tenant se siembran automáticamente las cuentas del PUC
- [ ] Estudiante puede ver el plan de cuentas y agregar subcuentas auxiliares (nivel 3-4)
- [ ] CRUD de terceros: clientes y proveedores
- [ ] CRUD de productos con vinculación de cuentas contables
- [ ] CRUD de configuración de empresa

---

### Fase 3 — Operaciones de venta

**Objetivo**: ciclo completo de venta con generación automática de asientos contables.

**Migraciones de tenant**:

```sql
invoices: id, type (venta|compra), series, number (consecutivo), date,
          due_date, third_id, status (borrador|emitida|anulada),
          subtotal, tax_amount, total, notes, timestamps

invoice_lines: id, invoice_id, product_id, description, qty, unit_price,
               discount_pct, tax_rate, line_subtotal, line_tax, line_total

credit_notes: id, invoice_id (FK facturas de venta), date, reason, total, status, timestamps
credit_note_lines: id, credit_note_id, invoice_line_id, qty, unit_price, line_total

debit_notes: id, invoice_id, date, reason, amount, status, timestamps

cash_receipts: id, third_id, date, total, notes, status, timestamps
cash_receipt_items: id, cash_receipt_id, invoice_id, amount_applied
```

**Asientos automáticos** — al confirmar una factura de venta, `AccountingService` genera automáticamente:

```
Débito  1305 Cuentas por cobrar clientes     $total_con_iva
Crédito 4135 Ingresos por ventas             $subtotal
Crédito 2408 IVA por pagar                  $iva
```

Y si el producto tiene costo (inventario):
```
Débito  6135 Costo de ventas                 $costo_total
Crédito 1435 Inventario de mercancías        $costo_total
```

Al registrar un recibo de caja:
```
Débito  1105 Caja (o 1110 Bancos)            $monto_recibido
Crédito 1305 Cuentas por cobrar clientes     $monto_recibido
```

**Regla de validación**: antes de guardar cualquier `JournalEntry`, verificar que `SUM(debits) === SUM(credits)`. Si no cuadra, lanzar `AccountingImbalanceException` y revertir la transacción completa (`DB::transaction()`).

**Criterios de aceptación Fase 3**:
- [ ] Crear factura borrador, agregar líneas, confirmar → estado cambia a "emitida"
- [ ] Al confirmar, se genera asiento contable automático y se puede ver en el libro diario
- [ ] Anular factura genera asiento de reverso
- [ ] Nota crédito reduce cartera del cliente
- [ ] Recibo de caja salda cartera

---

### Fase 4 — Operaciones de compra

**Objetivo**: ciclo completo de compra (proveedor → orden → factura → pago).

**Migraciones de tenant**:

```sql
purchase_orders: id, third_id (proveedor), date, expected_date,
                 status (pendiente|parcial|recibida|cancelada), total, timestamps

purchase_order_lines: id, purchase_order_id, product_id, qty, unit_cost, line_total

purchase_invoices: id, third_id, purchase_order_id (nullable), supplier_invoice_number,
                   date, due_date, status (pendiente|pagada|anulada), subtotal, tax, total

purchase_invoice_lines: id, purchase_invoice_id, product_id, qty, unit_cost, tax_rate, line_total

payments: id, third_id (proveedor), date, total, notes, status, timestamps
payment_items: id, payment_id, purchase_invoice_id, amount_applied
```

**Asientos automáticos de compra**:

Al confirmar factura de compra:
```
Débito  1435 Inventario                      $subtotal
Débito  2408 IVA descontable                 $iva
Crédito 2205 Proveedores nacionales          $total
```

Al registrar pago a proveedor:
```
Débito  2205 Proveedores nacionales          $monto_pagado
Crédito 1105 Caja (o 1110 Bancos)           $monto_pagado
```

**Criterios de aceptación Fase 4**:
- [ ] Orden de compra → recibir mercancía → entra a inventario automáticamente
- [ ] Factura de compra genera CxP al proveedor
- [ ] Pago saldo la cuenta del proveedor con asiento correcto

---

### Fase 5 — Contabilidad y reportes

**Migraciones de tenant**:

```sql
journal_entries: id, date, reference, description, document_type, document_id, auto_generated, timestamps
journal_lines: id, journal_entry_id, account_id, debit, credit, description
```

**Reportes a implementar** (con filtros por fecha):

1. **Libro diario**: todos los asientos en orden cronológico, con referencia al documento origen
2. **Libro mayor por cuenta**: seleccionar una cuenta, ver todos sus movimientos con saldo acumulado
3. **Balance de comprobación**: todas las cuentas activas con: saldo inicial + débitos del período + créditos del período + saldo final
4. **Estado de resultados**: ingresos (clase 4) menos costos (clase 6) menos gastos (clase 5) = utilidad/pérdida
5. **Balance general**: activos (clase 1) = pasivos (clase 2) + patrimonio (clase 3)
6. **Cartera por cobrar**: facturas de venta emitidas con días vencidas (aging)
7. **Cuentas por pagar**: facturas de compra pendientes con días vencidos

Todos los reportes se pueden **exportar a PDF** con `barryvdh/laravel-dompdf`, usando una vista Blade limpia con los colores corporativos de la empresa del estudiante.

**Criterios de aceptación Fase 5**:
- [ ] Libro diario muestra TODOS los asientos incluyendo los generados automáticamente
- [ ] Balance de comprobación: suma de débitos = suma de créditos siempre
- [ ] Estado de resultados cuadra con facturas confirmadas
- [ ] Balance general: activos = pasivos + patrimonio
- [ ] PDFs generados correctamente con nombre de empresa y período

---

### Fase 6 — Panel del docente (modo auditoría)

**Objetivo**: el docente puede supervisar y calificar a todos los estudiantes.

**Vistas del docente**:

1. **Dashboard del grupo**: tabla con columnas: estudiante, empresa, facturas emitidas, total facturado, última actividad, acciones.

2. **Modo auditoría**: el docente hace clic en "Auditar empresa" de un estudiante → el sistema inicializa el tenant del estudiante (`tenancy()->initialize($tenant)`) con un flag de solo lectura. Todas las vistas del estudiante se renderizan normalmente PERO con un banner en la parte superior: `"Modo auditoría — empresa de [nombre estudiante] — Solo lectura"`. Todos los botones de guardar/confirmar/anular están deshabilitados o hidden con `@if(!session('audit_mode'))`.

3. **Panel comparativo**: métricas de todos los estudiantes del grupo en una tabla: facturas de venta (#, total), facturas de compra (#, total), balance de caja estimado, si tienen el balance cuadrado (activos = pasivos + patrimonio).

4. **Rúbrica de calificación**: el docente puede ingresar una nota (1.0 a 5.0) por cada módulo para cada estudiante:
   - Maestros contables (clientes, proveedores, productos)
   - Facturación y cobro
   - Compras y pagos
   - Cierre contable (reportes cuadrados)
   - Nota final (promedio ponderado)

**Criterios de aceptación Fase 6**:
- [ ] El docente puede ver todas las empresas del grupo con sus métricas
- [ ] El modo auditoría permite navegar toda la empresa del estudiante sin poder modificar nada
- [ ] El banner de auditoría es visible y claro
- [ ] La rúbrica persiste las notas y muestra promedio del grupo

---

## Estructura de archivos

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # Superadmin (crear instituciones, docentes)
│   │   ├── Teacher/        # Docente (grupos, auditoría, rúbrica)
│   │   ├── Student/        # Auth del estudiante
│   │   └── Tenant/         # Módulos del estudiante (facturas, compras, etc.)
│   └── Middleware/
│       ├── InitializeTenancyByStudent.php
│       └── AuditModeOnly.php
├── Livewire/
│   ├── Teacher/            # Componentes del panel docente
│   └── Tenant/             # Componentes de los módulos del estudiante
│       ├── Invoices/
│       ├── Purchases/
│       ├── Accounting/
│       └── Reports/
├── Models/
│   ├── Central/            # User, Tenant, Group, Institution (schema public)
│   └── Tenant/             # Account, Third, Product, Invoice, etc. (schema del tenant)
└── Services/
    ├── AccountingService.php      # Generación y validación de asientos en doble partida
    ├── InvoiceService.php         # Lógica de facturación
    ├── PurchaseService.php        # Lógica de compras
    ├── TenantProvisionService.php # Crear schema + sembrar PUC al crear empresa
    └── ReportService.php          # Generación de reportes

database/
├── migrations/             # Schema public (institutions, users, tenants, groups)
└── migrations/tenant/      # Migraciones que se ejecutan en cada tenant schema
```

---

## Reglas de desarrollo para Claude Code

### Generales
- **Sin MoonShine** — todo Blade + Livewire 3
- **Sin `HasFactory`** en modelos de negocio del tenant (Account, Third, Product, Invoice, JournalEntry, etc.)
- **Sin `use HasFactory`** — los seeders usan `DB::table()->insert()` o `Model::create()` directamente
- Separar siempre lógica de negocio en Services — los controladores solo coordinan, los Livewire components solo presentan
- Usar **enums de PHP 8.1** para estados, tipos y roles — no strings libres
- **Soft deletes** en todos los documentos contables (facturas, notas, recibos, asientos)
- Idioma: toda la interfaz en **español colombiano** (`es` locale)

### Regla crítica de contabilidad
Toda operación que genere dinero debe envolverse en `DB::transaction()`. Dentro de la transacción: primero guardar el documento, luego llamar a `AccountingService::generateEntry()`. Si el servicio falla (débitos ≠ créditos), se lanza `AccountingImbalanceException` y toda la transacción se revierte. **Nunca guardar un documento sin su asiento completo.**

### Sobre stancl/tenancy en modo schema
- Usar `PostgreSQLSchemaManager` (no `MySQLDatabaseManager`)
- El nombre del schema = `tenant_` + id del tenant (cédula del estudiante)
- Las migraciones del tenant van en `database/migrations/tenant/`
- Al crear un tenant, el pipeline de eventos debe: 1) crear schema, 2) migrar, 3) sembrar PUC
- Para el modo auditoría: llamar `tenancy()->initialize($tenant)` y guardar en sesión `audit_mode = true`

### Deployment en Railway
- Configurar `Procfile` o `railway.json` con comando de start: `php artisan serve --host=0.0.0.0 --port=$PORT` (o usar Nginx config de Railway)
- Variables de entorno en Railway: `DB_CONNECTION=pgsql`, `APP_KEY`, `APP_URL`
- Para las migraciones en deploy: `php artisan migrate --force` y `php artisan tenants:migrate --force`
- El schema `public` siempre existe en PostgreSQL — usarlo como landlord sin configuración extra

---

## Orden de ejecución para Claude Code

Ejecutar cada fase en orden. No avanzar a la siguiente sin validar los criterios de aceptación de la fase actual.

1. Decir: **"ejecuta la Fase 1"** → Claude Code crea el proyecto, instala dependencias, configura tenancy con PostgreSQL schemas, crea migraciones centrales, guards, vistas de login, seeders
2. Validar manualmente que el login de los 3 roles funciona y que el schema del tenant se crea en PostgreSQL
3. Decir: **"la Fase 1 está aprobada, ejecuta la Fase 2"**
4. Continuar hasta Fase 6

Antes de cada fase, Claude Code debe:
1. Leer este archivo completo
2. Listar brevemente qué archivos va a crear o modificar
3. Confirmar que no hay conflictos con lo ya construido
4. Proceder con la implementación

---

## Estado actual de implementación

> Última actualización: 2026-03-29

### Roles del sistema (actualizado)

La jerarquía real implementada es de 4 niveles:

| Rol | Guard | Acceso |
|---|---|---|
| `superadmin` | `web` | Panel central: CRUD de instituciones, coordinadores y docentes |
| `coordinator` | `web` | Panel de institución: gestiona sus docentes y estudiantes, modo auditoría |
| `teacher` | `web` | Panel de grupo: crea empresas, asigna estudiantes, auditoría, empresas demo, rúbrica |
| `student` | `student` (guard propio) | Solo su empresa: todos los módulos contables |

### Cambios en BD central

- `institutions` tiene columna `coordinator_id` (FK nullable a `users`) — migración `2026_03_29_080410_add_coordinator_id_to_institutions`.
- `student_scores` tiene columnas `period` (string nullable) y `archived_at` (timestamp nullable) — migración `2026_03_29_074932_add_period_to_student_scores`.
- `UserRole` enum tiene los valores: `superadmin`, `coordinator`, `teacher`.

### Funcionalidades implementadas

#### Superadmin (`/admin`)
- CRUD de instituciones
- CRUD de docentes (asigna a institución, crea grupo inicial automáticamente)
- CRUD de coordinadores (crea usuario rol=coordinator y asigna `coordinator_id` en la institución)
- Transferencia de estudiantes entre grupos (3 modos: keep / reset / fresh)

#### Coordinador (`/coordinador`)
- Dashboard con tabs: Resumen, Docentes, Estudiantes
- CRUD de docentes **scoped a su institución**
- Transferencia de estudiantes **scoped a grupos de su institución**
- Modo auditoría de empresas estudiantiles (rutas bajo `coordinator.auditoria.*`)
- Layout propio: `layouts/coordinator.blade.php` + `layouts/coordinator-navigation.blade.php`
- Componente: `app/Livewire/Coordinator/Dashboard.php`
- Controlador auditoría: `app/Http/Controllers/Coordinator/AuditController.php`

#### Docente (`/docente`)
- Dashboard con métricas del grupo
- Modo auditoría de empresas estudiantiles
- Rúbrica de calificación con `period` y `archived_at` para historial por período
- Panel comparativo entre estudiantes
- Anuncios
- **Empresas demo** (acceso completo): todos los módulos incluyendo Facturación Electrónica

#### Estudiante (`/empresa`)
- Todos los módulos contables: configuración, plan de cuentas, terceros, productos, facturas, compras, reportes, calendario tributario, activos fijos, conciliación bancaria
- **Facturación Electrónica simulada** (módulo completo)
- Empresas de referencia (empresas demo del docente en solo lectura)

### Middlewares clave

- `CheckRole` soporta roles múltiples con sintaxis variádica: `role:coordinator,superadmin`
- `InitializeTenancyByStudent` reconoce 4 modos de sesión: normal (student), audit (teacher), demo (teacher), reference (student)

### Archivos de navegación tenant

`resources/views/layouts/tenant-navigation.blade.php` maneja los 4 modos de navegación:
- `demo_mode` → nav del docente en su empresa demo (incluye F. Electrónica)
- `reference_mode` → nav del estudiante viendo empresa de referencia
- `audit_mode` → nav del docente auditando empresa de estudiante
- Sin sesión especial → nav normal del estudiante (incluye F. Electrónica)

### Transferencia de estudiantes

`app/Services/TransferStudentService.php` soporta 3 modos:
- `keep` — solo cambia de grupo, datos intactos
- `reset` — trunca tablas transaccionales (facturas, compras, asientos), conserva PUC/config/terceros/productos
- `fresh` — recrea el schema completo desde cero

Las notas del período anterior quedan archivadas (`archived_at`) al transferir. `StudentScore::scopeCurrent()` filtra solo las activas.

### Inputs numéricos

Campos de cédula/NIT: `type="text" inputmode="numeric" pattern="[0-9\-]+"` (no `type="number"` para evitar que herramientas como Fake Filler pongan letras).
Campos de teléfono: `type="tel" inputmode="tel"`.

### Colores dinámicos en Tailwind

Tailwind JIT no detecta clases como `bg-{{ $variable }}-100`. La solución implementada es mover la lógica de color a métodos del Enum (`badgeClasses()`, `messageClasses()`) que devuelven strings estáticos completos.

### Notas de migración

Las migraciones de las tablas centrales tempranas (institutions, groups, tenants, student_scores) no tienen registro en la tabla `migrations` porque fueron creadas antes de que el historial quedara completo. Para nuevas migraciones sobre esas tablas, usar siempre `--path` para ejecutarlas individualmente.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3.6
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- livewire/livewire (LIVEWIRE) - v4
- laravel/breeze (BREEZE) - v2
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `livewire-development` — Develops reactive Livewire 4 components. Activates when creating, updating, or modifying Livewire components; working with wire:model, wire:click, wire:loading, or any wire: directives; adding real-time updates, loading states, or reactivity; debugging component behavior; writing Livewire tests; or when the user mentions Livewire, component, counter, or reactive UI.
- `pest-testing` — Tests applications using the Pest 4 PHP framework. Activates when writing tests, creating unit or feature tests, adding assertions, testing Livewire components, browser testing, debugging test failures, working with datasets or mocking; or when the user mentions test, spec, TDD, expects, assertion, coverage, or needs to verify functionality works.
- `tailwindcss-development` — Styles applications using Tailwind CSS v4 utilities. Activates when adding styles, restyling components, working with gradients, spacing, layout, flex, grid, responsive design, dark mode, colors, typography, or borders; or when the user mentions CSS, styling, classes, Tailwind, restyle, hero section, cards, buttons, or any visual/UI changes.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd and will be available at: `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs for the user.
- You must not run any commands to make the site available via HTTP(S). It is always available through Laravel Herd.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== livewire/core rules ===

# Livewire

- Livewire allows you to build dynamic, reactive interfaces using only PHP — no JavaScript required.
- Instead of writing frontend code in JavaScript frameworks, you use Alpine.js to build the UI when client-side interactions are required.
- State lives on the server; the UI reflects it. Validate and authorize in actions (they're like HTTP requests).
- IMPORTANT: Activate `livewire-development` every time you're working with Livewire-related tasks.

=== pint/core rules ===

# Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.
- CRITICAL: ALWAYS use `search-docs` tool for version-specific Pest documentation and updated code examples.
- IMPORTANT: Activate `pest-testing` every time you're working with a Pest or testing-related task.

=== tailwindcss/core rules ===

# Tailwind CSS

- Always use existing Tailwind conventions; check project patterns before adding new ones.
- IMPORTANT: Always use `search-docs` tool for version-specific Tailwind CSS documentation and updated code examples. Never rely on training data.
- IMPORTANT: Activate `tailwindcss-development` every time you're working with a Tailwind CSS or styling-related task.
</laravel-boost-guidelines>
