# TASK 04 — Integrar Portafolio en Nueva Oferta

> ⚠️ Prerequisito: TASK 01, 02 y 03 completados y verificados.

## Objetivo
Modificar el formulario "Nueva oferta" en Negocios para que el vendedor seleccione los ítems desde su portafolio en lugar de escribirlos manualmente. El comprador recibe la oferta y solo puede aceptar o rechazar.

## Contexto pedagógico
En la realidad empresarial un vendedor ofrece productos de su catálogo — no inventa los precios en cada transacción. Al forzar al estudiante a usar su portafolio, garantizamos que la cuenta de ingreso siempre sea la correcta y que los asientos contables resultantes sean consistentes con el tipo de empresa.

---

## Cambios en "Nueva oferta"

### Flujo actual (a reemplazar)
El vendedor escribe manualmente: descripción, cantidad, precio, %IVA, cuenta de ingreso.

### Flujo nuevo
```
Vendedor va a Negocios → Nueva oferta

1. Selecciona empresa compradora (compañero del grupo)
2. Escribe el concepto general de la transacción
3. Elige ítems DESDE SU PORTAFOLIO:
   ├── Se muestra listado de sus ítems activos
   ├── Selecciona el ítem → precio e IVA se precargan automáticamente
   ├── Solo edita la CANTIDAD
   └── Puede agregar múltiples ítems
4. El sistema calcula subtotal, IVA y total automáticamente
5. Checkboxes de retención (como ya existen) se mantienen
6. Enviar oferta
```

### Cambios en el formulario

**Antes** (campo libre):
```
Descripción | Cant. | Precio unit. | %IVA | Cta. ingreso | Total
[_________]   [1]    [_________]   [19%]  [___________]  $0
```

**Después** (desde portafolio):
```
Seleccionar del portafolio:
[ Consultoría contable — $150.000 — 19% ]  [+ Agregar]
[ Papel resma — $15.000 — 0% ]             [+ Agregar]

Ítems seleccionados:
PRODUCTO/SERVICIO    CANT.    PRECIO UNIT.    IVA    TOTAL
Consultoría          [1]      $150.000        19%    $150.000  [x]
```

Si el portafolio está vacío, mostrar mensaje:
> "Primero debes agregar productos o servicios en Mi portafolio antes de crear una oferta."
> [Ir a Mi portafolio →]

---

## Flujo del comprador — sin cambios en la lógica

El comprador en la pestaña "Recibidas" ve:
```
Oferta de TechNova SAS
Concepto: Prestación de servicios contables
─────────────────────────────────────────
Consultoría contable    1 und    $150.000    19%    $150.000
─────────────────────────────────────────
Subtotal:  $150.000
IVA 19%:    $28.500
Retención:  -$5.250
Total:     $173.250

[ Rechazar ]  [ Aceptar ]
```

Al aceptar → Job de contabilización automática en ambas empresas (lógica ya existente).

---

## Cambios técnicos

### En el Livewire Component de Nueva oferta:
- Cargar `portafolio_items` activos del tenant actual
- Al seleccionar un ítem: precargar precio, IVA y cuenta_ingreso_codigo
- Permitir editar solo la cantidad
- Validar que haya al menos un ítem antes de enviar

### En la tabla de ítems de oferta:
Agregar `portafolio_item_id` nullable para trazabilidad:

```php
$table->foreignId('portafolio_item_id')
      ->nullable()
      ->constrained('portafolio_items')
      ->nullOnDelete();
```

El campo es nullable para no romper ofertas existentes.

---

## Lo que NO se toca en esta tarea
- Lógica de contabilización automática — ya existe y funciona
- Pestañas Enviadas, Recibidas e Historial — sin cambios
- Módulo de Productos del ciclo contable

---

## Verificación
1. Ir a Negocios → Nueva oferta
2. Ítems se eligen del portafolio, no se escriben manualmente
3. Seleccionar empresa + ítem + cantidad → total calculado automáticamente
4. Enviar oferta → comprador la ve en Recibidas con datos del portafolio
5. Comprador acepta → asientos registrados en ambas empresas
6. Portafolio vacío → mensaje con enlace a Mi portafolio
