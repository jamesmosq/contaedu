# TASK 05 — Régimen Tributario y Habilitación de Facturación Electrónica

> ⚠️ Prerequisito: TASK 01 completado y verificado.

## Objetivo
Actualizar el campo de régimen tributario en Configuración de empresa con la terminología correcta vigente en Colombia (Ley 2010 de 2019) y agregar la lógica que habilita o deshabilita automáticamente el módulo de Facturación Electrónica según el régimen seleccionado.

## Contexto normativo
Desde la Ley 2010 de 2019 el Régimen Simplificado desapareció en Colombia. Los términos correctos vigentes son:
- **Responsable de IVA** — todas las sociedades y personas naturales que declaran IVA. Están obligadas a expedir Facturación Electrónica.
- **No responsable de IVA** — pequeños contribuyentes que no declaran IVA (antes llamados Régimen Simplificado). No están obligados a expedir Facturación Electrónica.

---

## Cambios requeridos

### 1. Migración (tenant)
Actualizar el campo `regimen` (o como se llame en la tabla) para aceptar los nuevos valores:

```php
Schema::table('company_settings', function (Blueprint $table) {
    $table->string('regimen')->default('no_responsable_iva')->change();
});
```

Valores válidos:
- `responsable_iva`
- `no_responsable_iva`

---

### 2. Migración — campo `fe_habilitada`
Si no existe, agregar columna para controlar si F.E. está habilitada:

```php
Schema::table('company_settings', function (Blueprint $table) {
    $table->boolean('fe_habilitada')->default(false);
});
```

---

### 3. Lógica de negocio — regla crítica

```
Si regimen = 'responsable_iva'
    → fe_habilitada = true (automático, no editable por el estudiante)

Si regimen = 'no_responsable_iva'
    → fe_habilitada = false (automático, no editable por el estudiante)
```

Esta lógica se aplica en el método de guardado del Livewire Component — nunca se permite que el estudiante habilite F.E. manualmente si es No responsable de IVA.

---

### 4. Livewire Component de Configuración
- Actualizar las opciones del selector de régimen
- Al cambiar el régimen, actualizar `fe_habilitada` automáticamente
- Si `fe_habilitada = false`, el módulo F. Electrónica en el sidebar debe mostrarse deshabilitado o con un mensaje explicativo

```php
public function updatedRegimen($value)
{
    $this->fe_habilitada = ($value === 'responsable_iva');
}
```

---

### 5. Vista — Configuración de empresa

Reemplazar el selector actual por:

```
Régimen tributario *
[ Responsable de IVA | No responsable de IVA ]

// Si Responsable de IVA:
✅ Facturación Electrónica habilitada (requerida para este régimen)

// Si No responsable de IVA:
🚫 Facturación Electrónica no disponible para este régimen
```

El mensaje debe ser visual, coherente con el diseño forest green/gold existente.

---

### 6. Módulo F. Electrónica en el sidebar

Si `fe_habilitada = false`:
- El ítem "F. Electrónica" en el sidebar debe aparecer con estilo deshabilitado (gris, sin hover)
- Al hacer clic, mostrar mensaje: *"Tu régimen tributario no requiere Facturación Electrónica"*
- No redirigir al módulo

Si `fe_habilitada = true`:
- Comportamiento normal — acceso completo al módulo

---

### 7. Actualizar registros existentes
En los tenants que ya existen con "Régimen Simplificado", migrar el valor al nuevo término:

```php
// En el seeder o en una migración de datos
DB::table('company_settings')
    ->where('regimen', 'simplificado')
    ->update(['regimen' => 'no_responsable_iva', 'fe_habilitada' => false]);

DB::table('company_settings')
    ->where('regimen', 'comun')
    ->orWhere('regimen', 'responsable')
    ->update(['regimen' => 'responsable_iva', 'fe_habilitada' => true]);
```

---

## Lo que NO se toca en esta tarea
- Lógica interna del módulo F. Electrónica
- Módulo de Negocios
- Módulo de Compras
- Capital inicial (TASK 02)
- Portafolio (TASK 03 y 04)

---

## Verificación
1. Ir a Configuración → ver "Responsable de IVA" y "No responsable de IVA" como opciones
2. Seleccionar **Responsable de IVA** → guardar → F. Electrónica habilitada en sidebar
3. Seleccionar **No responsable de IVA** → guardar → F. Electrónica deshabilitada en sidebar
4. Intentar acceder a F. Electrónica siendo No responsable → ver mensaje explicativo
5. Verificar que tenants existentes migraron correctamente sus valores
6. Verificar en DB que `fe_habilitada` refleja el régimen guardado
