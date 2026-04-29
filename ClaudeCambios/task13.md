# Task 13 — Ajustes ContaEdu: Perfil de empresa, IVA, Retenciones e Informes

## Contexto general

Estos ajustes provienen de una revisión funcional de ContaEdu. Afectan principalmente:
- La configuración del perfil de empresa (responsable vs. no responsable de IVA)
- La lógica de facturación electrónica (saldo IVA)
- El módulo de retenciones (ReteICA / Retefuente)
- El módulo de informes (nuevo certificado)
- El simulador de cierre de mes

Lee el `CLAUDE.md` y `SKILL.md` del proyecto antes de comenzar. Trabaja los ítems en el orden indicado ya que algunos dependen de los anteriores.

---

## Ítem 1 — No responsable de IVA: deshabilitar prefijo y facturación electrónica

**Archivo(s) probable(s):** formulario/componente Livewire de configuración de empresa.

**Lógica:**
- Cuando el campo `responsable_iva` del perfil de empresa sea `false`, deshabilitar visualmente (disabled + estilo grayed-out) los campos de:
  - Prefijo de facturación
  - Configuración de facturación electrónica (módulo completo o sección)
- Usar `wire:model` sobre `responsable_iva` y un condicional `@if` / `x-show` en la vista Livewire.
- El campo `responsable_iva` ya debe existir en el modelo de empresa; si no existe, agregarlo a la migración y al `$fillable`.

**Criterio de aceptación:**
- Al desmarcar "Responsable de IVA", los campos de prefijo y facturación electrónica quedan inaccesibles.
- Al marcar "Responsable de IVA", los campos vuelven a estar habilitados.

---

## Ítem 2 — Persona natural: agregar opción en tipo de aportante

**Archivo(s) probable(s):** configuración de empresa o módulo de nómina/aportes, enum o lista de tipos de aportante.

**Lógica:**
- Agregar la opción `"Persona Natural"` al selector de tipo de aportante.
- En Colombia, la persona natural independiente cotiza seguridad social sobre el **40% de sus ingresos brutos** (base mínima 1 SMMLV).
- Si existe lógica de cálculo de aportes downstream, bifurcarla para el caso `persona_natural`:
  - Base de cotización = `ingresos_brutos * 0.40` (mínimo 1 SMMLV vigente)
  - Salud: 12.5% sobre la base
  - Pensión: 16% sobre la base
- Si el cálculo de aportes aún no está implementado, solo agregar la opción al selector y dejar un `// TODO: lógica persona natural` comentado.

**Criterio de aceptación:**
- El selector muestra la opción "Persona Natural" junto a las existentes.
- No rompe las opciones ya existentes (sociedad, etc.).

---

## Ítem 3 — Facturación electrónica: mostrar saldo a favor / saldo a pagar por IVA

**Archivo(s) probable(s):** vista o componente del módulo de facturación electrónica / declaración de IVA.

**Lógica:**
```
total_iva_ventas   = suma del IVA de todas las facturas emitidas del período
total_iva_compras  = suma del IVA de todas las facturas recibidas/gastos del período
diferencia         = total_iva_ventas - total_iva_compras

Si diferencia > 0  → "Saldo a pagar a la DIAN: $diferencia"
Si diferencia < 0  → "Saldo a favor: $|diferencia|"
Si diferencia = 0  → "IVA en equilibrio"
```

**Implementación:**
- Agregar un panel resumen en la vista de facturación electrónica con los tres valores.
- Consultar las tablas correspondientes (facturas de venta y facturas de compra) filtrando por tenant y período activo.
- Mostrar los valores formateados en pesos colombianos (punto miles, coma decimales).

**Criterio de aceptación:**
- El panel muestra correctamente los tres valores.
- El mensaje cambia dinámicamente según la diferencia.
- Los valores coinciden con los registros reales en BD.

---

## Ítem 4 — No responsable de IVA: deshabilitar ReteICA, mantener Retefuente

**Archivo(s) probable(s):** formulario de configuración de empresa, sección de retenciones; también el formulario de creación de facturas.

**Lógica:**
- Cuando `responsable_iva = false`:
  - Los checkboxes o campos de **ReteICA** deben quedar deshabilitados (disabled + grayed-out).
  - Los campos de **Retención en la fuente (Retefuente)** permanecen activos.
- Este condicional puede compartir el mismo `wire:model` del ítem 1.
- Aplica tanto en la configuración de empresa como en el flujo de creación de documentos (facturas, compras).

**Criterio de aceptación:**
- No responsable de IVA: ReteICA inaccesible, Retefuente disponible.
- Responsable de IVA: ambas retenciones disponibles.

---

## Ítem 5 — Informes: agregar Certificado de Ingresos y Retención en la Fuente

> ⚠️ La fórmula exacta está **pendiente de definición** por parte del equipo. Implementar la estructura del reporte con los campos conocidos y dejar los cálculos marcados como `// PENDIENTE: fórmula oficial`.

**Archivo(s) probable(s):** módulo de Informes, probablemente un nuevo componente Livewire + vista de reporte + export PDF.

**Estructura del certificado (campos conocidos):**

| Campo | Fuente |
|---|---|
| Nombre / Razón social del empleador | Perfil de empresa |
| NIT del empleador | Perfil de empresa |
| Nombre del empleado | Módulo de empleados/nómina |
| Cédula del empleado | Módulo de empleados/nómina |
| Período certificado (año gravable) | Parámetro de consulta |
| Total ingresos brutos recibidos | Nómina del período |
| Aportes a salud (deducibles) | Nómina del período |
| Aportes a pensión (deducibles) | Nómina del período |
| Retención en la fuente practicada | Nómina del período |
| Total ingresos netos | `// PENDIENTE: fórmula` |

**Implementación:**
- Crear el ítem de menú en el módulo de Informes: "Certificado de Ingresos y Retenciones".
- Crear la vista con una tabla de previsualización por empleado.
- Agregar botón de descarga en PDF (usar el sistema de exportación ya existente en ContaEdu).
- Los campos con `// PENDIENTE` deben mostrarse en la UI como `"En definición"` para no bloquear el avance.

**Criterio de aceptación:**
- El certificado aparece en el menú de Informes.
- Muestra los campos ya disponibles con datos reales.
- Los campos pendientes se muestran claramente como pendientes, no como errores.

---

## Ítem 6 — Simulador de fin de mes: propagar efectos a Bancos, Informes y Certificado

**Archivo(s) probable(s):** servicio o job del cierre de mes, listeners de eventos Laravel.

**Lógica:**
- Al ejecutar el simulador de fin de mes, debe dispararse la recalculación de:
  1. **Bancos:** saldos y conciliación del período cerrado.
  2. **Informes:** balance general, P&G y demás informes contables del período.
  3. **Certificado de ingresos y retenciones:** acumular los datos del mes cerrado al resumen anual.

**Implementación sugerida (patrón Laravel Events):**
```php
// Evento
event(new MesSimuladoCerrado($tenant, $periodo));

// Listeners
MesSimuladoCerrado::listen([
    RecalcularSaldosBancos::class,
    RegenerarInformesContables::class,
    ActualizarCertificadoIngresos::class,
]);
```
- Si el simulador ya tiene un punto de entrada claro (método/servicio), agregar las llamadas ahí de forma directa si los eventos aún no están implementados.
- Verificar que cada módulo afectado filtre correctamente por `tenant_id` y `periodo`.

**Criterio de aceptación:**
- Después de ejecutar el simulador de fin de mes, los saldos en Bancos reflejan el cierre.
- Los informes contables muestran datos del período recién cerrado.
- El certificado de ingresos acumula correctamente el mes cerrado.

---

## Ítem 7 — PUC: revisar y corregir datos inconsistentes

> ⚠️ Este ítem **requiere input externo**: se necesita el listado específico de cuentas que no coinciden. No modificar el seeder del PUC hasta tener ese listado.

**Cuando se reciba el listado:**
- Comparar contra el PUC oficial (Decreto 2650/1993 o el estándar NIIF simplificado según aplique).
- Corregir en el archivo de seeder (JSON o array PHP).
- Re-correr el seeder usando `upsert` o `insertOrIgnore` según el patrón ya establecido en ContaEdu (no usar `HasFactory`).
- Verificar integridad de los 4 niveles: Clase → Grupo → Cuenta → Subcuenta.

**Criterio de aceptación:**
- El árbol del PUC en la UI no muestra cuentas duplicadas, mal clasificadas ni con códigos incorrectos.
- El seeder puede correrse en un tenant limpio y producir el PUC completo sin errores.

---

## Orden de implementación recomendado

```
1 → 4   (comparten el condicional responsable_iva, hacerlos juntos)
3       (facturación electrónica, independiente)
2       (tipo de aportante, independiente)
5       (certificado de informes, estructura base)
6       (cierre de mes, después de que el certificado tenga estructura)
7       (cuando llegue el listado de cuentas mal)
```

---

## Notas para Claude Code

- Proyecto: **ContaEdu** — Laravel 12, Livewire 3, stancl/tenancy v3, PostgreSQL schema-per-tenant.
- No usar `HasFactory` en ningún modelo.
- Todos los seeders de datos usan `insertOrIgnore` o `upsert`.
- Los componentes Livewire están organizados por módulo; respetar la estructura existente.
- Para exportación PDF, usar el sistema ya implementado en el proyecto (no instalar librerías nuevas sin consultar).
- Los valores monetarios se formatean siempre en pesos colombianos (COP): punto como separador de miles, coma como decimal.
