# TASK 08 — Módulo Banco: Simulador Bancario

> ⚠️ Prerequisito: TASK 02 (capital inicial) completado y verificado.
> ⚠️ Módulo nuevo independiente. Se integra con:
>     - Conciliación (ya existe) — bidireccional
>     - Negocios (panel de saldo + flujo de cobros)
>     - Facturas de compra (TASK 06) — pago desde cuenta bancaria
>     - Reportes — panel del profesor

---

## Objetivo

Crear un módulo simulador bancario donde cada empresa estudiantil tiene una cuenta bancaria virtual en uno de 3 bancos colombianos reales. Todos los movimientos de dinero fluyen a través de esa cuenta. El estudiante aprende a leer extractos, gestionar documentos bancarios, entender costos bancarios reales, solicitar documentos del banco y hacer conciliación.

---

## Contexto pedagógico

Un contador colombiano necesita dominar:
- Extracto bancario — documento de referencia para conciliación
- Notas débito y crédito bancarias — cuando el banco cobra o abona
- GMF 4x1000 — impuesto nacional en cada retiro o transferencia
- Transferencias ACH — entre bancos diferentes con costo real
- Cuota de manejo — cobro mensual automático del banco
- Diferencia entre cuenta corriente y cuenta de ahorros
- Certificado bancario y referencia bancaria — documentos comerciales reales
- Historial de chequera — control de cheques emitidos y cobrados
- Conciliación bancaria — cruzar libro contable con extracto

ContaEdu ya tiene el módulo de Conciliación. Este módulo bancario es su fuente de datos y lo complementa con operaciones bidireccionales.

---

## Cuenta bancaria por defecto — sin fricción desde el día 1

Al crear la empresa estudiantil, el sistema asigna **automáticamente** uno de los 3 bancos de forma aleatoria. El estudiante opera desde el primer momento sin configurar nada.

```
Empresa creada (tenant inicializado)
        ↓
Sistema asigna banco aleatorio:
  random(['bancolombia', 'davivienda', 'banco_bogota'])
        ↓
Genera número de cuenta simulado automáticamente:
  Bancolombia:     XXX-XXXXXX-XX
  Davivienda:      XXXX-XXXX-XXXX
  Banco de Bogotá: XXX-XXXXX-X
        ↓
Capital inicial $100M va directo a esa cuenta (TASK 02):
  Db 1110 Bancos [banco asignado]   $100.000.000
  Cr 3105 Capital suscrito          $100.000.000
        ↓
Estudiante opera inmediatamente — sin bloqueo
```

**Regla:** La cuenta por defecto es siempre **corriente**. Si el estudiante quiere ahorros, la abre manualmente.

---

## Segunda cuenta — apertura voluntaria

El estudiante puede abrir una segunda cuenta desde el módulo Banco. Reglas:

- **Máximo 2 cuentas activas** simultáneamente
- La segunda debe ser en **banco diferente** al de la cuenta por defecto
- Puede ser corriente o de ahorros
- Al abrirla, define monto inicial a consignar desde su cuenta existente:

```
Estudiante abre cuenta en Davivienda Ahorros
        ↓
Define monto inicial: $20.000.000
        ↓
Asiento automático:
  Db 1110 Bancos Davivienda Ahorros   $20.000.000
  Cr 1110 Bancos [banco original]     $20.000.000
  (Si bancos diferentes → genera comisión ACH + GMF)
        ↓
Ahora tiene dos cuentas activas
```

---

## Los 3 bancos simulados — particularidades reales

### 🔵 Bancolombia
**Perfil:** Banco más grande de Colombia. Favorito de empresas medianas y grandes.

| Característica | Valor simulado |
|---------------|----------------|
| Tipos de cuenta | Corriente / Ahorros |
| Cuota de manejo corriente | $15.900 + IVA mensual |
| Cuota de manejo ahorros | $8.900 + IVA mensual |
| Transferencias misma red | Sin costo |
| Transferencias ACH otras redes | $4.200 por transacción |
| Consignaciones digitales | Sin costo |
| Chequera | 30 cheques |
| Sobregiro | No disponible |
| GMF 4x1000 | Aplica en retiros y transferencias |
| Intereses ahorros mensuales | 0.3% sobre saldo promedio |
| Límite transferencias ahorros | Ilimitado |

### 🔴 Davivienda
**Perfil:** Fuerte presencia en Pymes. Primeras 5 transferencias del mes sin costo.

| Característica | Valor simulado |
|---------------|----------------|
| Tipos de cuenta | Corriente / Ahorros |
| Cuota de manejo corriente | $12.800 + IVA mensual |
| Cuota de manejo ahorros | $6.400 + IVA mensual |
| Transferencias misma red (primeras 5) | Sin costo |
| Transferencias misma red (desde la 6) | $2.100 por transacción |
| Transferencias ACH otras redes | $3.800 por transacción |
| Consignaciones propias | Sin costo |
| Consignaciones de terceros | $3.800 por consignación |
| Chequera | 30 cheques |
| Sobregiro | No disponible |
| GMF 4x1000 | Aplica en retiros y transferencias |
| Intereses ahorros mensuales | 0.25% sobre saldo promedio |
| Límite transferencias ahorros | 3 sin costo, desde la 4a $2.100 |

### 🟢 Banco de Bogotá
**Perfil:** Banco tradicional, Grupo Aval. Único con sobregiro en el simulador.

| Característica | Valor simulado |
|---------------|----------------|
| Tipos de cuenta | Corriente / Ahorros |
| Cuota de manejo corriente | $14.500 + IVA mensual |
| Cuota de manejo ahorros | $7.200 + IVA mensual |
| Transferencias red Aval | Sin costo |
| Transferencias ACH otras redes | $4.500 por transacción |
| Consignaciones | Sin costo en canales Aval |
| Chequera | 10, 30 o 100 cheques (estudiante elige) |
| Sobregiro (Cupo Ágil) | Hasta $5.000.000 — único con sobregiro |
| GMF 4x1000 | Aplica en retiros y transferencias |
| Intereses ahorros mensuales | 0.35% sobre saldo promedio (mejor tasa) |
| Límite diario transacciones | $25.000.000 |

---

## Cuenta corriente vs cuenta de ahorros

| Característica | Corriente | Ahorros |
|---------------|-----------|---------|
| Cheques | ✅ Sí | ❌ No |
| Sobregiro | Solo Banco de Bogotá | ❌ No |
| Intereses mensuales | ❌ No | ✅ Sí (según banco) |
| GMF 4x1000 | ✅ Aplica | ✅ Aplica |
| Transferencias | Ilimitadas | Límite según banco |
| Uso en Negocios | ✅ Sí | ✅ Sí |

Intereses de ahorros — asiento automático fin de mes:
```
Db 1110 Bancos [banco ahorros]
Cr 4210 Intereses (ingreso no operacional)
```

---

## Flujo de cobros en Negocios — cómo opera con cuentas bancarias

### Cuando Estudiante A vende a Estudiante B

```
B acepta la oferta de A
        ↓
Sistema muestra a B selector de cuenta para pagar:
  "¿De qué cuenta deseas pagar?"
  [ 🔵 Bancolombia Corriente ***4521 — Saldo: $87.500.000 ]
  [ 🔴 Davivienda Ahorros ***8832   — Saldo: $12.000.000 ]
        ↓
Sistema muestra a A selector de cuenta para recibir:
  "¿A qué cuenta deseas recibir el pago?"
  [ 🔵 Bancolombia Corriente ***4521 ]
  (Si A solo tiene una → se selecciona automáticamente)
        ↓
Sistema valida saldo suficiente en cuenta de B
```

### Caso 1 — Mismo banco (sin costo ACH)

```
B (Bancolombia) → paga → A (Bancolombia)

Empresa B:
  Db 2205 Proveedores               $1.000.000
  Cr 1110 Bancos Bancolombia        $1.004.000
  Db 530520 GMF 4x1000              $4.000

Empresa A:
  Db 1110 Bancos Bancolombia        $1.000.000
  Cr 1305 Clientes                  $1.000.000

Extracto B: -$1.004.000 | Extracto A: +$1.000.000
```

### Caso 2 — Bancos diferentes (con costo ACH)

```
B (Davivienda) → paga → A (Bancolombia)

Empresa B:
  Db 2205 Proveedores               $1.000.000
  Cr 1110 Bancos Davivienda         $1.007.800
  Db 5305 Comisión bancaria ACH     $3.800
  Db 530520 GMF 4x1000              $4.000

Empresa A:
  Db 1110 Bancos Bancolombia        $1.000.000
  Cr 1305 Clientes                  $1.000.000

Extracto B: -$1.007.800 | Extracto A: +$1.000.000
```

**Momento pedagógico clave:** B aprende que pagar desde otro banco cuesta más — entiende por qué las empresas reales dominan sus pagos en el mismo banco del proveedor.

### Caso 3 — Saldo insuficiente en todas las cuentas

```
Sistema bloquea:
"No tienes saldo suficiente en ninguna cuenta.
 Saldo disponible: $500.000 | Valor a pagar: $1.000.000"
        ↓
La oferta queda en estado "pendiente_pago"
B puede:
├── Esperar recibir dinero de otras ventas
├── Hacer consignación desde caja
└── Rechazar la oferta (penalidad en historial crediticio)
```

---

## Integración con Facturas de compra (TASK 06)

Cuando el estudiante paga una factura de compra, el pago sale directamente de su cuenta bancaria:

```
Factura de compra registrada → 2205 Proveedores (pasivo)
        ↓
Estudiante va a pagar la factura
        ↓
"¿De qué cuenta deseas pagar?"
[ 🔵 Bancolombia Corriente ***4521 — Saldo: $87.500.000 ]
        ↓
Sistema genera:
  Db 2205 Proveedores
  Cr 1110 Bancos [cuenta seleccionada]
  + GMF automático
        ↓
Registro en bank_transactions
        ↓
Aparece en extracto bancario para conciliar
```

---

## Documentos bancarios — pestaña "Documentos" en el módulo Banco

El estudiante solicita documentos desde el módulo Banco. Todos se generan inmediatamente en PDF.

```
┌─────────────────────────────────────────────────────────────┐
│  DOCUMENTOS BANCARIOS                    🔵 Bancolombia     │
├─────────────────────────────────────────────────────────────┤
│  📄 Extracto bancario         [Solicitar] [Ver historial]   │
│     Último generado: Marzo 2026                             │
│                                                             │
│  📋 Certificado bancario      [Solicitar]                   │
│     Confirma titularidad y saldo promedio                   │
│                                                             │
│  📝 Referencia bancaria       [Solicitar]                   │
│     Carta que confirma que eres cliente del banco           │
│                                                             │
│  ✅ Paz y salvo               [Solicitar]                   │
│     Solo disponible al cerrar la cuenta                     │
│                                                             │
│  🧾 Talonario de consignaciones [Ver historial]             │
│     47 consignaciones registradas                           │
│                                                             │
│  📑 Historial de chequera     [Ver historial]               │
│     Solo cuenta corriente con chequera activa               │
└─────────────────────────────────────────────────────────────┘
```

---

### 1. Extracto bancario

**Cuándo se genera:**
- **Automáticamente** al simular fin de mes — el sistema lo genera para todas las cuentas activas
- **A solicitud** en cualquier momento — muestra movimientos del período actual hasta la fecha

**Contenido del PDF:**

```
══════════════════════════════════════════════════════
                    BANCOLOMBIA
              EXTRACTO DE CUENTA CORRIENTE
══════════════════════════════════════════════════════
Titular:    TechNova SAS
NIT:        901234567-8
Cuenta:     123-456789-01
Período:    01/03/2026 — 31/03/2026
══════════════════════════════════════════════════════
Saldo inicial:                        $100.000.000
──────────────────────────────────────────────────────
FECHA      DESCRIPCIÓN              DÉBITOS    CRÉDITOS
01/03/26   Capital constitución               100.000.000
10/03/26   Pago oferta TechCorp     1.004.000
15/03/26   Cobro venta Distrib.                2.500.000
28/03/26   Pago factura Proveedor   500.000
31/03/26   Cuota de manejo           18.841
31/03/26   GMF 4x1000                 6.016
──────────────────────────────────────────────────────
Total débitos:                        1.528.857
Total créditos:                     102.500.000
──────────────────────────────────────────────────────
SALDO FINAL:                        $100.971.143
══════════════════════════════════════════════════════
```

---

### 2. Certificado bancario

**A solicitud.** Se genera inmediatamente en PDF.

**Contenido:**

```
══════════════════════════════════════════════════════
                    BANCOLOMBIA
              CERTIFICADO BANCARIO
══════════════════════════════════════════════════════
Bancolombia S.A. certifica que:

Titular:            TechNova SAS
NIT:                901234567-8
Tipo de cuenta:     Corriente
Número de cuenta:   123-456789-01
Fecha de apertura:  01/04/2026
Estado:             Activa

Saldo a la fecha:               $87.500.000
Saldo promedio últimos 3 meses: $91.200.000

Bogotá D.C., 10 de abril de 2026
══════════════════════════════════════════════════════
Documento generado por el Simulador Bancario ContaEdu
```

**Uso pedagógico:** El estudiante puede presentar este certificado a otro compañero antes de una negociación grande — demuestra su capacidad financiera.

---

### 3. Referencia bancaria

**A solicitud.** Se genera inmediatamente en PDF.

**Contenido:**

```
══════════════════════════════════════════════════════
                    BANCOLOMBIA
              REFERENCIA BANCARIA
══════════════════════════════════════════════════════
Bancolombia S.A. hace constar que:

TechNova SAS, identificada con NIT 901234567-8,
es cliente de nuestra entidad desde el 01/04/2026,
con manejo de cuenta corriente No. 123-456789-01.

La empresa ha manejado su cuenta con normalidad
durante el tiempo de vinculación con la entidad.

Bogotá D.C., 10 de abril de 2026

Esta referencia se expide a solicitud del interesado.
══════════════════════════════════════════════════════
Documento generado por el Simulador Bancario ContaEdu
```

---

### 4. Paz y salvo

**Solo disponible cuando el estudiante va a cerrar la cuenta.** Al solicitar el cierre:
1. El sistema verifica que el saldo sea $0 y no haya cheques pendientes
2. Si hay saldo → debe transferirlo antes de cerrar
3. Si hay cheques pendientes → no puede cerrar hasta que estén cobrados
4. Si todo está limpio → genera el paz y salvo y cierra la cuenta

---

### 5. Talonario de consignaciones

Historial completo de todas las consignaciones con número consecutivo. Cada consignación genera un comprobante numerado que es el soporte para el archivo contable.

```
NRO.   FECHA       DESCRIPCIÓN                    VALOR
001    01/04/26    Capital inicial constitución   $100.000.000
002    15/04/26    Cobro factura TechCorp           $2.500.000
003    20/04/26    Transferencia desde Davivienda   $5.000.000
```

---

### 6. Historial de chequera (solo cuenta corriente)

Control completo de cheques emitidos:

```
NRO.    FECHA       BENEFICIARIO       VALOR        ESTADO
0001    10/04/26    Distribuidora X    $500.000      Cobrado
0002    15/04/26    Papelería Y        $120.000      Pendiente
0003    20/04/26    Transportes Z      $800.000      Devuelto ⚠️
```

**Cheque devuelto** — cuando el destinatario intenta cobrar un cheque sin saldo:
```
Evento pedagógico: cheque devuelto
        ↓
Asiento reversión:
  Db 1305 Clientes (reversión de pago)
  Cr 1110 Bancos (reversión)
        ↓
Nota débito del banco por sanción:
  Db 5305 Comisiones bancarias (sanción cheque devuelto)
  Cr 1110 Bancos
        ↓
Afecta negativamente el historial crediticio del estudiante
```

---

## Sobregiro — Banco de Bogotá (único)

**Cupo Ágil:** hasta $5.000.000 de sobregiro.

```
Saldo disponible: $200.000
Pago requerido:   $1.000.000
        ↓
Sistema detecta que hay Cupo Ágil disponible:
"Tu saldo es insuficiente. ¿Deseas usar el Cupo Ágil?
 Cupo disponible: $5.000.000 | Costo: interés diario 0.1%"
        ↓
Estudiante acepta → transacción procede
Saldo resultante: -$804.000 (en sobregiro)
        ↓
Al simular fin de mes — si sigue en sobregiro:
  Nota débito automática por intereses de mora:
  Db 530505 Intereses financieros
  Cr 1110 Bancos
        ↓
Si no paga en 2 períodos → bloqueo de nuevas transacciones
hasta saldar el sobregiro
```

---

## Notificaciones y alertas automáticas

El sistema genera alertas visibles en el módulo Banco y en el inicio del estudiante:

| Evento | Alerta |
|--------|--------|
| Saldo < $5.000.000 | ⚠️ "Tu saldo bancario es bajo" |
| Cuota de manejo próxima (3 días) | 📅 "En 3 días se cobra cuota de manejo" |
| Sobregiro activo | 🔴 "Tienes sobregiro activo — genera intereses" |
| Cheque pendiente > 30 días | ⏰ "Cheque #0002 lleva 30 días sin cobrar" |
| Transferencia recibida | 💰 "Recibiste $2.500.000 de TechCorp" |
| Bloqueo por sobregiro no pagado | 🚫 "Cuenta bloqueada — saldo el sobregiro" |

---

## GMF 4x1000 — lógica de cálculo

```php
$gmf = 0;
if (in_array($tipo, ['retiro', 'transferencia_salida', 'cheque', 'pago_proveedor'])) {
    $gmf = round($valor * 0.004); // 4 por mil
}
// Asiento automático:
// Db 530520 GMF — 4x1000
// Cr 1110 Bancos
```

No aplica en: consignaciones recibidas, notas crédito, intereses.

---

## Modelo de datos

### Tabla: `bank_accounts`

```php
Schema::create('bank_accounts', function (Blueprint $table) {
    $table->id();
    $table->enum('bank', ['bancolombia', 'davivienda', 'banco_bogota']);
    $table->string('account_number');
    $table->enum('account_type', ['corriente', 'ahorros'])->default('corriente');
    $table->decimal('saldo', 15, 2)->default(0);
    $table->decimal('sobregiro_disponible', 15, 2)->default(0);
    $table->decimal('sobregiro_usado', 15, 2)->default(0);
    $table->boolean('es_principal')->default(false);
    $table->boolean('activa')->default(true);
    $table->boolean('bloqueada')->default(false);     // por sobregiro no pagado
    $table->integer('cheques_disponibles')->nullable(); // null = sin chequera
    $table->integer('cheques_emitidos')->default(0);
    $table->date('fecha_apertura');
    $table->timestamps();
});
```

### Tabla: `bank_transactions`

```php
Schema::create('bank_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('bank_account_id')->constrained();
    $table->enum('tipo', [
        'consignacion',
        'retiro',
        'transferencia_salida',
        'transferencia_entrada',
        'nota_debito',
        'nota_credito',
        'cheque',
        'cheque_devuelto',
        'pago_proveedor',
        'cobro_cliente',
        'cuota_manejo',
        'intereses_ahorros',
        'intereses_sobregiro',
        'gmf',
        'comision_ach',
        'sancion_cheque_devuelto',
    ]);
    $table->decimal('valor', 15, 2);
    $table->decimal('gmf', 15, 2)->default(0);
    $table->decimal('comision', 15, 2)->default(0);
    $table->decimal('saldo_despues', 15, 2);
    $table->string('descripcion');
    $table->string('referencia')->nullable();            // número de cheque, consecutivo
    $table->string('banco_destino')->nullable();
    $table->string('cuenta_destino')->nullable();
    $table->boolean('conciliado')->default(false);
    $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries');
    $table->foreignId('intercompany_invoice_id')->nullable()->constrained('intercompany_invoices');
    $table->foreignId('purchase_invoice_id')->nullable()->constrained('purchase_invoices');
    $table->date('fecha_transaccion');
    $table->timestamps();
});
```

### Tabla: `bank_statements`

```php
Schema::create('bank_statements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('bank_account_id')->constrained();
    $table->date('periodo_inicio');
    $table->date('periodo_fin');
    $table->decimal('saldo_inicial', 15, 2);
    $table->decimal('total_debitos', 15, 2);
    $table->decimal('total_creditos', 15, 2);
    $table->decimal('saldo_final', 15, 2);
    $table->string('pdf_path')->nullable();              // ruta del PDF generado
    $table->timestamps();
});
```

### Tabla: `bank_documents`

```php
Schema::create('bank_documents', function (Blueprint $table) {
    $table->id();
    $table->foreignId('bank_account_id')->constrained();
    $table->enum('tipo', [
        'certificado',
        'referencia',
        'paz_y_salvo',
        'extracto',
    ]);
    $table->string('pdf_path');
    $table->timestamp('generado_at');
    $table->timestamps();
});
```

### Tabla: `bank_checks` (historial de chequera)

```php
Schema::create('bank_checks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('bank_account_id')->constrained();
    $table->string('numero_cheque');
    $table->string('beneficiario');
    $table->decimal('valor', 15, 2);
    $table->date('fecha_emision');
    $table->date('fecha_cobro')->nullable();
    $table->enum('estado', ['emitido', 'cobrado', 'devuelto', 'anulado']);
    $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries');
    $table->timestamps();
});
```

---

## Integración con Conciliación (bidireccional)

### Dirección 1: Banco → Conciliación
Cada `bank_transaction` queda disponible en Conciliación con `conciliado = false`. El estudiante compara con el libro diario y marca las que coinciden.

### Dirección 2: Conciliación → Banco
Desde Conciliación, al encontrar diferencias:

**Opción A — Nota bancaria no registrada:**
El estudiante la registra directamente desde Conciliación:
1. Crea registro en `bank_transactions` con `conciliado = true`
2. Genera asiento en `journal_entries`
3. Actualiza saldo de la cuenta

**Opción B — Error contable:**
Redirige al módulo correspondiente para corregir.

---

## Panel de saldo en Negocios

Una cuenta:
```
💰 Saldo banco: $87.500.000
🔵 Bancolombia Corriente ***4521
```

Dos cuentas:
```
💰 Saldo total: $99.500.000
🔵 Bancolombia Corriente ***4521   $87.500.000
🔴 Davivienda Ahorros ***8832      $12.000.000
```

---

## Panel del profesor — reportes bancarios del grupo

El profesor ve en su panel de grupo:

```
ACTIVIDAD BANCARIA DEL GRUPO
─────────────────────────────────────────────────────
Empresa              Banco         Saldo        Estado
TechNova SAS         Bancolombia   $87.500.000  ✅
Castaño Distrib.     Davivienda    $12.000.000  ✅
Inversiones Pérez    Banco Bogotá  -$800.000    🔴 Sobregiro
─────────────────────────────────────────────────────
Volumen transacciones del grupo este período: $45.200.000
Estudiantes con saldo crítico (< $1M): 1
Estudiantes en sobregiro: 1
```

---

## Sidebar — estructura completa del módulo

```
├── Conciliación
├── Banco                    ← NUEVO
│   ├── Mi cuenta
│   │   ├── Movimientos
│   │   ├── Transferencias
│   │   └── Abrir segunda cuenta
│   ├── Extracto
│   └── Documentos
│       ├── Extracto bancario
│       ├── Certificado bancario
│       ├── Referencia bancaria
│       ├── Paz y salvo
│       ├── Talonario de consignaciones
│       └── Historial de chequera
├── F. Electrónica
```

---

## Asientos contables generados automáticamente

| Transacción | Débito | Crédito |
|------------|--------|---------|
| Consignación recibida | 1110 Bancos | Origen (1305, 4xxx) |
| Retiro / pago | 2xxx / 5xxx | 1110 Bancos |
| GMF 4x1000 | 530520 GMF | 1110 Bancos |
| Cuota de manejo | 530510 Comisiones bancarias | 1110 Bancos |
| Nota crédito (intereses) | 1110 Bancos | 4210 Intereses |
| Transferencia ACH salida | cuenta destino | 1110 Bancos + comisión |
| Intereses sobregiro | 530505 Intereses financieros | 1110 Bancos |
| Sanción cheque devuelto | 5305 Comisiones bancarias | 1110 Bancos |

---

## Reglas de negocio críticas

1. **Cuenta por defecto siempre existe** — el estudiante nunca está bloqueado para operar
2. **Máximo 2 cuentas** — una por defecto + una opcional en banco diferente
3. **Segunda cuenta en banco diferente** — no se pueden tener dos cuentas en el mismo banco
4. **Saldo insuficiente bloquea el pago** — mensaje claro con opciones
5. **GMF automático** — nunca manual, siempre calculado por el sistema
6. **Cuota de manejo al simular fin de mes** — no se puede evitar
7. **Intereses de ahorros al simular fin de mes** — automático
8. **Sobregiro solo en Banco de Bogotá** — máximo $5.000.000
9. **Sobregiro no pagado en 2 períodos** → bloquea nuevas transacciones
10. **Cheque devuelto** → reversión + sanción + afecta historial crediticio
11. **Todos los documentos se generan inmediatamente** — sin tiempo de espera simulado
12. **Paz y salvo solo al cerrar la cuenta** — verifica saldo $0 y sin cheques pendientes
13. **Toda transacción bancaria genera asiento contable** — trazabilidad via `journal_entry_id`
14. **Toda transacción de Negocios o Facturas pregunta la cuenta** — si solo tiene una, automática

---

## Lo que NO se toca en esta tarea
- Módulo de Facturas de venta existente
- Módulo de Compras (órdenes) existente
- PUC — las cuentas ya existen (1110, 530520, 5305, 530505, 4210)
- Tenants existentes — la cuenta por defecto solo se crea en tenants nuevos

---

## Verificación completa

1. Crear empresa → banco asignado automáticamente → saldo $100M
2. Panel Negocios → muestra banco y saldo correctamente
3. Abrir segunda cuenta en banco diferente → consignación inicial correcta con asiento
4. Venta en Negocios mismo banco → sin comisión ACH, GMF aplicado
5. Venta en Negocios bancos diferentes → comisión ACH + GMF en extracto del comprador
6. Saldo insuficiente → bloqueo con mensaje claro y opciones
7. Pagar factura de compra → sale de cuenta bancaria con GMF
8. Fin de mes simulado → cuota de manejo descontada con asiento
9. Fin de mes cuenta ahorros → intereses abonados con asiento
10. Solicitar certificado bancario → PDF generado inmediatamente
11. Solicitar referencia bancaria → PDF generado inmediatamente
12. Solicitar extracto → PDF con todos los movimientos del período
13. Emitir cheque → aparece en historial con estado "emitido"
14. Cheque devuelto → reversión + sanción + alerta
15. Desde Conciliación → registrar nota débito → aparece en banco y libro diario
16. Sobregiro Banco de Bogotá → opera hasta $5M en negativo
17. Sobregiro no pagado 2 períodos → cuenta bloqueada
18. Panel del profesor → ve estudiantes en sobregiro y saldo crítico
19. Paz y salvo → solo disponible al cerrar cuenta con saldo $0
