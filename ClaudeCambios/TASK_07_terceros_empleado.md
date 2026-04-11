# TASK 07 — Terceros: Agregar Tipo Empleado

> ⚠️ Prerequisito: TASK 06 completado y verificado.

## Objetivo
Agregar el tipo **Empleado** al módulo de Terceros. Actualmente los terceros tienen tipos Cliente, Proveedor y Ambos. El empleado es un tercero especial que requiere datos adicionales propios de la relación laboral colombiana y genera registros contables en cuentas específicas distintas a las de clientes y proveedores.

## Contexto normativo
Según la normativa colombiana (Estatuto Tributario Art. 383), la retención en la fuente por salarios aplica cuando los ingresos gravables superan 95 UVT mensuales (~$4.730.905 COP para 2025). Existen dos procedimientos de cálculo que el empleado debe tener configurado:
- **Procedimiento 1**: retención fija calculada mes a mes
- **Procedimiento 2**: retención variable calculada sobre el promedio de los últimos 6 meses

---

## Cambios requeridos

### 1. Migración — tabla de terceros (tenant)
Agregar el tipo `empleado` al enum o campo de tipo en la tabla de terceros:

```php
// Si el campo es un enum, modificarlo para incluir 'empleado'
// Si es string con validación, agregar 'empleado' a los valores permitidos
Schema::table('thirds', function (Blueprint $table) {
    // Agregar columnas exclusivas de empleados (nullable para no afectar registros existentes)
    $table->string('cargo')->nullable();
    $table->decimal('salario_basico', 15, 2)->nullable();
    $table->enum('tipo_contrato', [
        'indefinido',
        'fijo',
        'obra_labor',
        'prestacion_servicios'
    ])->nullable();
    $table->enum('procedimiento_retencion', ['1', '2'])->nullable()->default('1');
    $table->string('afp')->nullable();          // fondo de pensiones
    $table->string('eps')->nullable();           // entidad de salud
    $table->string('arl')->nullable();           // administradora de riesgos laborales
    $table->date('fecha_ingreso')->nullable();
    $table->date('fecha_retiro')->nullable();
    $table->boolean('activo_laboralmente')->default(true);
});
```

---

### 2. Modelo Tercero
- Agregar `empleado` a los tipos válidos
- Agregar los nuevos campos al `$fillable`
- Agregar método helper: `esEmpleado()`, `esCliente()`, `esProveedor()`

---

### 3. Vista — Formulario de Terceros

**Selector de tipo actualizado:**
```
Tipo de tercero *
[ Cliente | Proveedor | Empleado | Ambos (Cliente/Proveedor) ]
```

**Campos comunes a todos los tipos:**
- Tipo de documento (CC, NIT, CE, Pasaporte)
- Número de documento
- Nombre / Razón social
- Dirección
- Teléfono
- Correo
- Ciudad / Municipio

**Campos adicionales — solo visibles cuando tipo = Empleado:**
```
─── Información laboral ────────────────────────────────

Cargo *                         Fecha de ingreso *
[____________________]          [dd/mm/aaaa]

Salario básico mensual *        Tipo de contrato *
[$__________________]           [ Indefinido | Fijo | Obra/Labor | Prestación servicios ]

Procedimiento de retención *
[ Procedimiento 1 — Mensual fijo | Procedimiento 2 — Promedio 6 meses ]
(Ver Art. 383 Estatuto Tributario)

EPS                             AFP
[____________________]          [____________________]

ARL
[____________________]
```

Los campos laborales deben ocultarse completamente con `wire:show` o `x-show` cuando el tipo no es Empleado.

---

### 4. Livewire Component de Terceros
- Agregar propiedad `$tipo` que controla visibilidad de campos laborales
- Al cambiar tipo → limpiar campos que no aplican
- Validación condicional:

```php
$rules = [
    'nombre' => 'required|string',
    'tipo_documento' => 'required',
    'numero_documento' => 'required',
    // ...campos comunes
];

if ($this->tipo === 'empleado') {
    $rules['cargo'] = 'required|string';
    $rules['salario_basico'] = 'required|numeric|min:0';
    $rules['tipo_contrato'] = 'required|in:indefinido,fijo,obra_labor,prestacion_servicios';
    $rules['procedimiento_retencion'] = 'required|in:1,2';
    $rules['fecha_ingreso'] = 'required|date';
}
```

---

### 5. Listado de Terceros — filtro por tipo
Agregar filtro para ver solo Empleados, solo Clientes o solo Proveedores:

```
[ Todos | Clientes | Proveedores | Empleados ]
```

Los empleados deben aparecer con un badge distintivo en el listado.

---

### 6. Impacto en otros módulos

**Facturas de venta:** El selector de cliente NO debe mostrar empleados.

**Facturas de compra (TASK 06):** El selector de proveedor NO debe mostrar empleados.

**Retención en la fuente:** Los empleados aparecen en el módulo de retenciones por salarios (futuro TASK) con su procedimiento configurado.

**Reportes:** Los empleados aparecen en su propia sección — no mezclados con clientes ni proveedores.

---

### 7. Cuentas PUC asociadas al empleado
A diferencia de clientes (1305) y proveedores (2205), el empleado usa cuentas específicas:

| Concepto | Cuenta PUC |
|----------|-----------|
| Salarios por pagar | 2505 |
| Cesantías consolidadas | 2510 |
| Intereses sobre cesantías | 2515 |
| Prima de servicios | 2520 |
| Vacaciones consolidadas | 2525 |
| Gastos de personal — sueldos | 510505 |
| Gastos de personal — prestaciones | 510525-510545 |

Estos no se configuran en el tercero — son las cuentas que usará el módulo de nómina cuando se implemente.

---

## Lo que NO se toca en esta tarea
- Módulo de nómina (viene después)
- Cálculo automático de retención por salarios (viene después)
- Módulo de Negocios
- Facturas existentes

---

## Verificación
1. Ir a Terceros → crear un tercero tipo Empleado
2. Verificar que aparecen los campos laborales (cargo, salario, contrato, procedimiento retención, EPS, AFP, ARL)
3. Crear un tercero tipo Cliente → verificar que NO aparecen campos laborales
4. En el listado filtrar por "Empleados" → solo aparecen empleados
5. Ir a Facturas → selector de cliente NO muestra empleados
6. Ir a Facturas de Compra → selector de proveedor NO muestra empleados
7. Verificar en DB que los campos laborales se guardaron correctamente
