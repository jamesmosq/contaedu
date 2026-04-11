---
name: contaedu-migrations
description: >-
  Convenciones de migraciones en ContaEdu. Activar cuando se creen o
  modifiquen migraciones, seeders o estructura de base de datos.
  Cubre nomenclatura, separación Central/Tenant, guards de seguridad
  y patrones de seeds de datos.
---

# ContaEdu — Migraciones y Base de Datos

## Dos tipos de migraciones

### 1. Migraciones centrales (BD pública)
Ubicación: `database/migrations/` (raíz)
Afectan: tablas compartidas entre todos los tenants

```
tenants, institutions, groups,
intercompany_invoices, intercompany_invoice_items,
intercompany_journal_entries, portafolio_items,
platform_notifications, transfer_requests, etc.
```

### 2. Migraciones de tenant
Ubicación: `database/migrations/tenant/`
Afectan: el schema de cada estudiante individualmente
Se ejecutan automáticamente en `AutoMigrateTenant` al inicializar el tenant

```
accounts, journal_entries, journal_lines,
invoices, purchase_invoices, bank_accounts, etc.
```

**Regla crítica:** si la tabla pertenece a un estudiante → va en `tenant/`.
Si es compartida entre todos → va en la raíz.

---

## Nomenclatura de archivos

```
# Formato: YYYY_MM_DD_NNNNNN_descripcion_snake_case.php

# Migraciones tenant — usar fecha actual y secuencia dentro del día:
2026_04_12_000001_add_modo_to_operational_tables.php
2026_04_12_000002_add_academic_fields_to_accounts.php
2026_04_12_000003_seed_puc_academic_content.php

# Migraciones centrales — mismo formato
2026_04_12_000001_add_campo_to_tabla_central.php
```

Incrementar el número de secuencia (000001, 000002...) para cada
migración del mismo día para garantizar el orden de ejecución.

---

## Guards de seguridad obligatorios

Siempre verificar antes de modificar una tabla:

```php
// Antes de agregar columna:
if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'modo')) {
    Schema::table($table, function (Blueprint $t) {
        $t->string('modo', 10)->default('real')->after('id');
    });
}

// Antes de eliminar columna:
if (Schema::hasTable($table) && Schema::hasColumn($table, 'modo')) {
    Schema::table($table, function (Blueprint $t) {
        $t->dropColumn('modo');
    });
}

// Antes de crear tabla:
if (! Schema::hasTable('nombre_tabla')) {
    Schema::create('nombre_tabla', function (Blueprint $t) { ... });
}
```

---

## Patrón para seeds de datos en migraciones

Cuando una migración necesita insertar o actualizar datos
(como el seed del PUC académico), usar `DB::table()` directamente:

```php
public function up(): void
{
    // Actualizar datos existentes
    DB::table('accounts')
        ->where('code', '1305')
        ->update([
            'descripcion'    => 'Registra el valor de las deudas...',
            'dinamica_debe'  => "1. Por ventas a crédito...\n2. Por notas débito...",
            'dinamica_haber' => "1. Por pagos recibidos...\n2. Por notas crédito...",
            'ejemplo'        => 'Venta a crédito...',
        ]);

    // Insertar si no existe
    DB::table('accounts')->insertOrIgnore([
        'code'  => '240810',
        'name'  => 'IVA descontable',
        'type'  => 'pasivo',
        'nature'=> 'debito',
        'level' => 4,
        'active'=> true,
    ]);
}
```

---

## Seeder de tenant (TenantDatabaseSeeder)

```php
// database/seeders/TenantDatabaseSeeder.php
class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PucSeeder::class);       // siembra las 9 clases PUC
        $this->call(FeResolucionSeeder::class); // resolución FE simulada
    }
}
```

El `PucSeeder` usa `insertOrIgnore` — es seguro correrlo varias veces.

---

## AutoMigrateTenant — cómo corren las migraciones de tenant

```php
// app/Listeners/AutoMigrateTenant.php
// Se ejecuta en TenancyBootstrapped (primer acceso del estudiante)

// Si el tenant es NUEVO → corre TODAS las migraciones + seedCapitalInicial()
// Si el tenant EXISTE → corre solo las migraciones pendientes (nuevas)
```

Esto significa que una nueva migración en `database/migrations/tenant/`
se aplica automáticamente la próxima vez que el estudiante accede.
**No necesitas correr artisan manualmente por cada estudiante.**

Para aplicar a TODOS los tenants existentes de una vez:

```bash
php artisan tenants:migrate-all
```

---

## Estructura de tablas importantes

### `accounts` (PUC)
```
id, code VARCHAR(10) UNIQUE, name, type, nature,
parent_id, level TINYINT(1-4), active BOOLEAN,
descripcion TEXT, dinamica_debe TEXT,
dinamica_haber TEXT, ejemplo TEXT,
created_at, updated_at
```

### `journal_entries` (asientos)
```
id, modo VARCHAR(10) DEFAULT 'real',
date DATE, reference VARCHAR(30),
description, document_type VARCHAR(30),
document_id, auto_generated BOOLEAN,
deleted_at, created_at, updated_at
```

### `journal_lines` (líneas de asiento)
```
id, journal_entry_id FK,
account_id FK → accounts,
debit DECIMAL, credit DECIMAL,
description, created_at, updated_at
```

### `invoices` (facturas de venta)
```
id, modo VARCHAR(10) DEFAULT 'real',
date, reference, third_id FK,
subtotal, tax_amount, total,
status (borrador|emitida|anulada),
...retenciones..., created_at, updated_at
```

### `bank_accounts` (cuentas bancarias) — sin campo modo
```
id, bank, account_number, account_type,
saldo DECIMAL, es_principal BOOLEAN,
activa BOOLEAN, bloqueada BOOLEAN,
recibe_pagos_negocios BOOLEAN,
fecha_apertura DATE, created_at, updated_at
```

---

## Tipos de columnas usados en el proyecto

```php
$t->string('modo', 10)->default('real');      // campo modo
$t->text('descripcion')->nullable();           // texto largo nullable
$t->decimal('subtotal', 15, 2)->default(0);   // moneda colombiana
$t->unsignedBigInteger('account_id');          // FK sin constraint explícito
$t->unsignedTinyInteger('level');              // nivel PUC 1-4
$t->boolean('active')->default(true);
$t->softDeletes();                             // para journal_entries
$t->index('modo');                             // índice en campo modo
```

---

## Convención de tipos PUC

```
type    → 'activo' | 'pasivo' | 'patrimonio' | 'ingreso' | 'costo' | 'gasto' | 'orden'
nature  → 'debito' | 'credito'
level   → 1 (clase) | 2 (grupo) | 3 (cuenta) | 4 (subcuenta)
```

---

## Comandos útiles

```bash
# Correr migraciones de tenant para UN tenant específico
php artisan tenants:migrate --tenants=ID_TENANT

# Correr migraciones de tenant para TODOS
php artisan tenants:migrate-all

# Ver migraciones pendientes (BD central)
php artisan migrate:status

# Crear migración central
php artisan make:migration add_campo_to_tabla

# Las migraciones de tenant se crean manualmente en database/migrations/tenant/
# No usar make:migration para tenant — copiar y adaptar una existente
```
