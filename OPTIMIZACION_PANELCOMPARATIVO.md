# OPTIMIZACION_PANEL_COMPARATIVO

## Contexto

El panel comparativo del docente cruza múltiples tenants simultáneamente. Cada estudiante tiene su propia empresa virtual en un tenant independiente (`stancl/tenancy` v3). El panel muestra: `# facturas venta`, `total ventas`, `# facturas compra`, `total compras`, `balance cuadrado` por cada estudiante.

Escenario real: un docente puede tener hasta 3 grupos con un máximo de 40 estudiantes por grupo.

---

## Problema identificado

- El panel actual carga todos los grupos con todos sus estudiantes de una sola vez
- Cada estudiante implica un cambio de contexto de tenant (conexión, resolución, configuración)
- Los totales se calculan en tiempo de lectura mediante agregaciones (`COUNT`, `SUM`) sobre la tabla de facturas de cada tenant
- El costo del panel crece progresivamente a medida que los estudiantes registran más transacciones durante el semestre

---

## Decisiones de implementación

### 1. Filtro por grupo activo

- El panel comparativo carga únicamente el grupo que el docente selecciona
- No se carga ningún dato al montar el componente sin un grupo seleccionado
- El selector de grupo es el punto de entrada obligatorio antes de cualquier consulta

### 2. Materialización de totales en el tenant del estudiante

- Cada tenant de estudiante mantiene un registro de resumen con sus totales ya calculados
- Este registro se actualiza cuando el estudiante registra o modifica una factura, no cuando el docente abre el panel
- Los campos a materializar son: `total_facturas_venta`, `monto_total_ventas`, `total_facturas_compra`, `monto_total_compras`, `balance_cuadrado`
- `balance_cuadrado` es un booleano que se evalúa y almacena en tiempo de escritura

---

## Restricciones

- No implementar carga diferida (`#[Lazy]`) como solución al rendimiento
- No implementar Redis cache en este panel — el hit rate es bajo dado el patrón de uso
- No calcular totales en tiempo de lectura — siempre leer valores materializados
- No usar Stored Procedures para las agregaciones del comparativo
- El panel no debe renderizar ninguna fila sin un grupo activo seleccionado

---

## Orden de implementación

1. Filtro por grupo activo en el componente Livewire del panel comparativo
2. Tabla o campo de resumen materializado en el tenant del estudiante
3. Actualización del resumen al registrar o modificar facturas de venta y compra
4. Lectura del resumen desde el panel comparativo del docente

---

## Resultado esperado

El costo de renderizar el panel es constante por número de estudiantes del grupo seleccionado, independientemente del volumen de facturas acumuladas durante el semestre.
