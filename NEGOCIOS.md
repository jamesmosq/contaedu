# NEGOCIOS — Mercado Interempresarial Estudiantil

Feature de ContaEdu que permite a los estudiantes realizar transacciones comerciales entre sus empresas virtuales dentro del mismo grupo de clase, simulando el ciclo contable completo en un ecosistema real de mercado.

---

## Contexto pedagógico

### Por qué existe esta feature

El PUC colombiano existe porque las empresas interactúan entre sí. La retención en la fuente que una empresa aplica es el anticipo de impuesto de la otra. Ese ciclo **no se puede enseñar con ejercicios individuales** — requiere un ecosistema donde las decisiones de un estudiante afecten la contabilidad de otro.

### Premisa fundamental

Cada estudiante en ContaEdu ya tiene una empresa virtual propia con su PUC sembrado. Esta feature conecta esas empresas dentro del mismo grupo de clase para que puedan hacerse transacciones reales que se contabilizan automáticamente en ambas partes.

---

## Arquitectura del sistema de negocios

### Alcance

- **Solo dentro del mismo grupo de clase** — un estudiante del grupo A no puede negociar con uno del grupo B
- **Un estudiante puede negociar con todos sus compañeros** — no hay restricción de cantidad de contrapartes
- **Un estudiante puede ser vendedor y comprador simultáneamente** — igual que en la realidad empresarial

### Modalidades de mercado (configuradas por el profesor)

**Modalidad 1 — Libre mercado**
Cada estudiante elige libremente con quién negocia, cuándo y qué producto o servicio ofrece. Genera el ecosistema más rico pero requiere estudiantes con dominio previo del PUC. Recomendada para ciclos avanzados.

**Modalidad 2 — Mercado por sectores (recomendada para inicio)**
El profesor define sectores económicos dentro del grupo. Ejemplo:
- Proveedores de materia prima (2-3 empresas)
- Empresas manufactureras (3-4 empresas)
- Comercializadoras (2-3 empresas)
- Empresas de servicios (1-2 empresas)

Las transacciones fluyen por la cadena de valor de forma natural. Una manufacturera le compra al proveedor y le vende al comercializador. Más controlado y más fácil de evaluar por el profesor.

**Modalidad 3 — Rondas de negocio**
El profesor abre rondas semanales. En cada ronda cada estudiante debe cerrar un mínimo de transacciones definido por el profesor. Al cerrar la ronda el profesor evalúa los estados financieros de cada empresa.

---

## Flujo de una transacción

```
Estudiante A (vendedor)          Estudiante B (comprador)
        │                                  │
        │  1. Crea oferta de venta         │
        │  (producto, precio, IVA,         │
        │   retención si aplica)           │
        │                                  │
        │  2. Envía oferta ──────────────► │
        │                                  │
        │                    3. Revisa     │
        │                    la oferta     │
        │                                  │
        │  ◄────────────── 4a. Acepta      │
        │       ó                          │
        │  ◄────────────── 4b. Rechaza     │
        │                  (con motivo)    │
        │                                  │
        │  5. Al aceptar: job contabiliza en AMBAS empresas simultáneamente
        │                                  │
   Vendedor registra:              Comprador registra:
   - Db 1305 Clientes              - Db gasto o activo
   - Cr 4135 Ingresos              - Db 240810 IVA descontable
   - Cr 2408 IVA generado          - Cr 2205 Proveedores
   - Db 1355 Retención             - Cr 2365 Retención practicada
     practicada (si aplica)          (si aplica)
```

---

## Lo que aprende cada estudiante por rol

### Como vendedor aprende a registrar:
| Cuenta | Código PUC | Naturaleza |
|--------|-----------|-----------|
| Cuentas por cobrar — clientes | 1305 | Débito |
| Ingresos operacionales | 4135 | Crédito |
| IVA generado | 2408 | Crédito |
| Anticipo impuestos (retención sufrida) | 1355 | Débito |

### Como comprador aprende a registrar:
| Cuenta | Código PUC | Naturaleza |
|--------|-----------|-----------|
| Gasto o activo según lo comprado | 51xx / 15xx | Débito |
| IVA descontable | 240810 | Débito |
| Cuentas por pagar — proveedores | 2205 | Crédito |
| Retención en la fuente practicada | 2365 | Crédito |

### El ciclo completo
Un estudiante que vende Y compra en el mismo periodo contable practica **todas** las cuentas relevantes del PUC en un solo ciclo — algo que con ejercicios individuales tomaría semanas.

---

## Requisitos mínimos por ronda (sugeridos)

Para garantizar que el estudiante practica el ciclo completo:

- Mínimo 2 proveedores diferentes
- Mínimo 2 clientes diferentes
- Al menos 1 transacción que incluya retención en la fuente
- Al menos 1 transacción con IVA del 19%
- Balance cuadrado al cierre de la ronda

---

## Modelo de datos

### Tabla central: `intercompany_invoices`

```php
Schema::create('intercompany_invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('seller_student_id')->constrained('students');
    $table->foreignId('buyer_student_id')->constrained('students');
    $table->foreignId('group_id')->constrained('groups');           // mismo grupo obligatorio
    $table->string('consecutive');                                  // número de factura simulada
    $table->enum('status', ['pendiente', 'aceptada', 'rechazada']);
    $table->decimal('subtotal', 15, 2);
    $table->decimal('iva', 15, 2)->default(0);
    $table->decimal('retencion_fuente', 15, 2)->default(0);
    $table->decimal('retencion_iva', 15, 2)->default(0);
    $table->decimal('retencion_ica', 15, 2)->default(0);
    $table->decimal('total', 15, 2);
    $table->text('concepto');
    $table->string('rechazo_motivo')->nullable();
    $table->timestamp('accepted_at')->nullable();
    $table->timestamps();
    $table->softDeletes();

    // Validación: vendedor ≠ comprador
    // Validación: ambos pertenecen al mismo grupo (a nivel de aplicación)
});
```

### Tabla de líneas: `intercompany_invoice_items`

```php
Schema::create('intercompany_invoice_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('intercompany_invoice_id')->constrained()->cascadeOnDelete();
    $table->string('descripcion');
    $table->decimal('cantidad', 10, 2);
    $table->decimal('precio_unitario', 15, 2);
    $table->decimal('subtotal', 15, 2);
    $table->string('cuenta_ingreso_codigo');   // código PUC del vendedor
    $table->string('cuenta_gasto_codigo');     // código PUC del comprador
    $table->timestamps();
});
```

### Tabla de asientos generados: `intercompany_journal_entries`

```php
Schema::create('intercompany_journal_entries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('intercompany_invoice_id')->constrained();
    $table->enum('party', ['seller', 'buyer']);
    $table->foreignId('journal_entry_id')->constrained('journal_entries'); // asiento real en el PUC
    $table->timestamps();
});
```

---

## Lógica del job de contabilización

Al aceptar una factura interempresarial se dispara `ProcessIntercompanyInvoiceJob`:

```php
// Pseudocódigo del job
DB::transaction(function () use ($invoice) {

    // 1. Asiento en la empresa del VENDEDOR
    tenancy()->initialize($invoice->seller->tenant);
    JournalEntry::create([
        // Db 1305 Clientes por el total
        // Cr 4135 Ingresos por el subtotal
        // Cr 2408 IVA generado
        // Db 1355 Anticipo impuestos por retención sufrida
    ]);

    // 2. Asiento en la empresa del COMPRADOR
    tenancy()->initialize($invoice->buyer->tenant);
    JournalEntry::create([
        // Db gasto o activo por el subtotal
        // Db 240810 IVA descontable
        // Cr 2205 Proveedores por el total neto
        // Cr 2365 Retención practicada
    ]);

    // 3. Registrar los asientos en intercompany_journal_entries
    // 4. Actualizar progreso del ciclo contable de ambos estudiantes
});
```

---

## Vista del profesor

El profesor tiene un panel de "Mercado del grupo" donde ve:

- **Mapa de relaciones**: qué empresas han negociado entre sí (grafo de conexiones)
- **Ranking de actividad**: estudiantes ordenados por número de transacciones cerradas
- **Alertas de descuadre**: transacciones donde vendedor y comprador registraron montos diferentes
- **Estado por estudiante**: cuántas transacciones como vendedor, cuántas como comprador, balance actual
- **Exportar para evaluación**: reporte de cada empresa con sus estados financieros del periodo

### El momento más rico pedagógicamente — el descuadre

Cuando dos estudiantes tienen una transacción y **no cuadran**:
- El vendedor registró $1.000.000 de venta
- El comprador registró $900.000 de compra

El profesor puede usar ese conflicto como caso de clase: ¿quién tiene razón? ¿faltó el IVA? ¿hubo un descuento que uno registró y el otro no? Esto es contabilidad real aplicada.

---

## Sidebar de navegación

La feature agrega una nueva sección **"Negocios"** al sidebar del estudiante, entre Compras y Terceros:

```
├── Inicio
├── Facturas
├── Compras
├── Negocios          ← NUEVO
│   ├── Mis ofertas enviadas
│   ├── Ofertas recibidas
│   └── Historial de transacciones
├── Terceros
├── Productos
...
```

Y en el panel del **profesor/coordinador**:

```
├── Panel de grupo
├── Mercado del grupo ← NUEVO
│   ├── Transacciones activas
│   ├── Mapa de relaciones
│   └── Evaluación por ronda
...
```

---

## Reglas de negocio críticas

1. **Mismo grupo obligatorio** — validar a nivel de aplicación antes de mostrar compañeros disponibles. Un estudiante solo ve como posibles contrapartes a los del mismo `group_id`.

2. **No puede negociar consigo mismo** — `seller_student_id !== buyer_student_id`.

3. **La contabilización es atómica** — si falla el asiento del vendedor o del comprador, se hace rollback completo. Nunca un asiento parcial.

4. **El rechazo no genera asiento** — solo cambia el estado a `rechazada` y registra el motivo.

5. **Una oferta aceptada no se puede revertir** — igual que una factura real. Si hay un error, se genera una nota crédito interempresarial.

6. **Retención en la fuente** — solo aplica si el monto de la transacción supera 4 UVT ($185.108 COP para 2025, según tabla DIAN). El sistema calcula automáticamente si aplica o no.

7. **El profesor puede anular** — solo el profesor/coordinador puede anular una transacción aceptada, con trazabilidad completa.

---

## Progreso del ciclo contable

La feature actualiza el progreso del ciclo contable del estudiante:

| Hito | Condición |
|------|-----------|
| Primera venta realizada | 1 factura interempresarial aceptada como vendedor |
| Primera compra realizada | 1 factura interempresarial aceptada como comprador |
| Retención aplicada | 1 transacción con retención registrada |
| Ciclo completo de negocios | Venta + compra + balance cuadrado en el mismo periodo |

---

## Fases de implementación

### Fase 1 — MVP
- Tabla `intercompany_invoices` e `intercompany_invoice_items`
- Flujo básico: crear oferta → enviar → aceptar/rechazar
- Job de contabilización automática en ambas empresas
- Vista del estudiante: mis ofertas y ofertas recibidas

### Fase 2 — Panel del profesor
- Mapa de relaciones del grupo
- Alertas de descuadre
- Reporte de evaluación por ronda

### Fase 3 — Modalidades avanzadas
- Configuración de sectores económicos por el profesor
- Rondas de negocio con fechas de apertura y cierre
- Requisitos mínimos por ronda configurables
- Nota crédito interempresarial

---

## Stack técnico

Igual al resto de ContaEdu:
- **Backend**: Laravel 12 + Livewire 3
- **Multi-tenancy**: stancl/tenancy v3 schema-per-tenant
- **DB**: PostgreSQL
- **UI**: diseño forest green/gold (Playfair Display), coherente con el sistema de diseño existente
- **Jobs**: queue con `ProcessIntercompanyInvoiceJob` en el tenant correcto
- **Sin factories** — los datos de prueba se crean desde seeders o desde la UI
