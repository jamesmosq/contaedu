# TASK 01 — Sector Empresarial en Configuración de Empresa

## Objetivo
Agregar el campo `sector_empresarial` a la configuración de empresa. Este campo clasifica pedagógicamente la empresa dentro del Mercado Interempresarial (módulo Negocios) y más adelante precargará las cuentas de ingreso del PUC sugeridas en el portafolio.

## Contexto del proyecto
- Laravel 12 + Livewire 3
- Multi-tenant: stancl/tenancy v3, schema por tenant (PostgreSQL)
- Sin factories — sin HasFactory en modelos
- La migración corre en tenants: `php artisan tenants:migrate`

---

## Cambios requeridos

### 1. Migración (tenant)
Agregar columna a la tabla donde vive la configuración de empresa (verificar nombre real de la tabla antes de crear la migración):

```php
Schema::table('company_settings', function (Blueprint $table) {
    $table->string('sector_empresarial')->default('comercial');
});
```

Valores válidos: `industrial`, `comercial`, `servicios`, `avicola`, `ganadera`, `otros`

---

### 2. Modelo
Agregar `sector_empresarial` al `$fillable` del modelo de configuración de empresa.

---

### 3. Livewire Component de Configuración
- Agregar propiedad `$sector_empresarial`
- Cargarlo en `mount()` desde la configuración existente
- Guardarlo en el método de guardado existente
- Validación: `'sector_empresarial' => 'required|in:industrial,comercial,servicios,avicola,ganadera,otros'`

---

### 4. Vista — Configuración de empresa
Agregar el campo visualmente **después del Código CIIU** y **antes de Teléfono**, coherente con el diseño forest green/gold existente:

```
Sector empresarial *
Clasificación para el Mercado Interempresarial

[ Industrial | Comercial | Servicios | Avícola | Ganadera | Otros ]
```

---

## Lo que NO se toca en esta tarea
- Módulo de Negocios
- Módulo de Productos
- Lógica de facturación o ciclo contable
- Capital inicial (eso va en TASK 02)

---

## Verificación
1. Ir a Configuración de empresa
2. Ver el campo "Sector empresarial" después de Código CIIU
3. Seleccionar un valor y guardar
4. Recargar — el valor debe persistir
5. Verificar en DB que la columna tiene el valor guardado
