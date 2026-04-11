# TASK 02 — Capital Inicial Automático de $100M

> ⚠️ Prerequisito: TASK 01 completado y verificado.

## Objetivo
Al crear una empresa estudiantil en ContaEdu, el sistema debe registrar automáticamente un capital inicial de $100.000.000 COP con su asiento contable correspondiente. Esto garantiza que el estudiante tenga liquidez real desde el primer día para operar en el Mercado Interempresarial y en el ciclo contable normal.

## Contexto pedagógico
El asiento de constitución es el primer ejercicio contable que debe practicar un estudiante. Al automatizarlo, el estudiante parte de un balance real y puede ver desde el inicio cómo el capital de los accionistas se refleja en el activo disponible.

---

## Asiento contable a registrar automáticamente

```
DÉBITO:  1110 Bancos                    $100.000.000
CRÉDITO: 3105 Capital suscrito y pagado $100.000.000
Concepto: "Capital inicial de constitución"
Fecha: fecha de creación de la empresa
```

---

## Dónde implementar
En el proceso de creación de la empresa estudiantil (cuando el coordinador/profesor crea un estudiante o cuando el tenant se inicializa), después de que el tenant esté listo y las migraciones hayan corrido, registrar el asiento en la tabla `journal_entries` (o la tabla de asientos que use ContaEdu) del schema del tenant.

Buscar el evento o método que se dispara al crear un tenant nuevo — puede ser:
- Un observer en el modelo Tenant
- Un job que se lanza después de `tenancy()->initialize()`
- Un seeder que ya corre al crear el tenant

Agregar ahí la lógica de creación del asiento inicial usando los códigos de cuenta `1110` y `3105` que ya existen en el PUC sembrado por `PucSeeder`.

---

## Reglas importantes
- El asiento solo se crea UNA vez — verificar que no exista antes de crearlo
- Si el tenant ya tiene asientos registrados, no crear el capital inicial (evitar duplicados en tenants existentes)
- El asiento debe estar marcado como aprobado/contabilizado automáticamente — no como borrador
- Usar `DB::transaction()` para garantizar atomicidad

---

## Lo que NO se toca en esta tarea
- Flujo de creación de estudiantes en el frontend
- Módulo de Negocios
- Configuración de empresa (eso fue TASK 01)

---

## Verificación
1. Crear un tenant/empresa estudiantil nuevo
2. Ir a Plan de cuentas → verificar que 1110 Bancos tiene saldo $100.000.000
3. Ir a Libro diario → verificar que existe el asiento de constitución
4. Ir a Inicio → verificar que el balance muestra Activo = Pasivo + Patrimonio desde el día 1
5. Verificar que al crear un segundo tenant no se duplica el asiento
