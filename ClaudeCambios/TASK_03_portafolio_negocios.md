# TASK 03 — Mi Portafolio en Módulo Negocios

> ⚠️ Prerequisito: TASK 01 y TASK 02 completados y verificados.

## Objetivo
Agregar una pestaña "Mi portafolio" dentro del módulo Negocios donde cada empresa estudiantil define los productos o servicios que ofrece al Mercado Interempresarial del grupo. Este portafolio es independiente del módulo de Productos del ciclo contable — es exclusivamente para el mercado entre estudiantes.

## Contexto pedagógico
Antes de poder vender en el mercado del grupo, el estudiante debe definir qué ofrece su empresa, a qué precio y con qué tratamiento tributario. Esto simula exactamente lo que hace un empresario real al configurar su catálogo de ventas.

El sector empresarial elegido en Configuración (TASK 01) precarga sugerencias de cuentas de ingreso según el tipo de empresa.

---

## Modelo de datos

### Tabla: `portafolio_items` (schema del tenant)

```php
Schema::create('portafolio_items', function (Blueprint $table) {
    $table->id();
    $table->string('nombre');
    $table->text('descripcion')->nullable();
    $table->enum('tipo', ['producto', 'servicio']);
    $table->decimal('precio', 15, 2);
    $table->enum('iva', ['19', '5', '0'])->default('19');
    $table->string('cuenta_ingreso_codigo');   // código PUC ej: 4135, 4160, 4165
    $table->string('cuenta_ingreso_nombre');   // nombre legible de la cuenta
    $table->boolean('activo')->default(true);
    $table->timestamps();
});
```

---

## Vista — Pestaña "Mi portafolio" en Negocios

El módulo Negocios actualmente tiene las pestañas:
`Nueva oferta | Enviadas | Recibidas | Historial`

Agregar la pestaña **"Mi portafolio"** como primera pestaña:
`Mi portafolio | Nueva oferta | Enviadas | Recibidas | Historial`

### Contenido de la pestaña Mi portafolio

**Header:**
```
Mi portafolio                                    [+ Agregar producto/servicio]
Define qué ofreces al mercado de tu grupo
```

**Listado de ítems** (tabla o cards):
```
NOMBRE          TIPO        PRECIO       IVA    CUENTA INGRESO    ESTADO
Consultoría     Servicio    $150.000     19%    4160 — Servicios  ✅ Activo
Papel resma     Producto    $15.000       0%    4135 — Comercio   ✅ Activo
```

**Formulario de creación/edición:**
```
Nombre *                    Tipo *
[________________]          [ Producto | Servicio ]

Descripción
[________________________________]

Precio de venta *           IVA *
[$______________]            [ 19% | 5% | 0% - Exento ]

Cuenta de ingreso *
[ Buscar cuenta del PUC... ]
(Sugerencia según sector: si es Servicios → 4160, si es Comercial → 4135, etc.)

[ Cancelar ]  [ Guardar ]
```

### Cuentas de ingreso sugeridas según sector (TASK 01)

| Sector | Cuenta sugerida | Nombre |
|--------|----------------|--------|
| Comercial | 4135 | Comercio al por mayor y al por menor |
| Servicios | 4160 | Servicios |
| Industrial | 4120 | Industrias manufactureras |
| Avícola | 4105 | Agricultura, ganadería, caza y silvicultura |
| Ganadera | 4105 | Agricultura, ganadería, caza y silvicultura |
| Otros | 4195 | Otros ingresos operacionales |

---

## Livewire Component
Crear `PortafolioComponent` con:
- Listado de ítems del portafolio
- Modal o inline form para crear/editar
- Toggle de activo/inactivo
- Validaciones: nombre requerido, precio > 0, cuenta_ingreso_codigo requerido

---

## Lo que NO se toca en esta tarea
- El módulo de Productos del ciclo contable — no se mezclan
- La pestaña "Nueva oferta" — la integración del portafolio ahí va en TASK 04
- Lógica de contabilización — eso viene con las ofertas

---

## Verificación
1. Ir a Negocios → ver la pestaña "Mi portafolio" como primera pestaña
2. Crear un producto y un servicio con sus datos completos
3. Verificar que la cuenta sugerida corresponde al sector de la empresa
4. Editar un ítem — los cambios deben persistir
5. Desactivar un ítem — no debe aparecer en el portafolio activo
6. Verificar en DB que los datos están en la tabla `portafolio_items` del schema correcto
