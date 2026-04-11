# TASK 11 — Zona Aprendizaje (Sandbox) + PUC Interactivo

> ⚠️ Prerequisito: TASKs 01–10 completados.
> ⚠️ Este TASK NO elimina nada de lo que ya funciona.
>     Reutiliza todos los Livewire components y Services existentes.
>     Solo agrega campo `modo`, nuevas rutas, PUC interactivo y sidebar corregido.

---

## Objetivo

Dos cambios en uno:

**1. Separar ContaEdu en dos zonas con los mismos módulos:**
```
APRENDIZAJE (/aprendizaje/*)     MI EMPRESA (/empresa/*)
────────────────────────         ─────────────────────────
Mismos módulos                   Mismos módulos
Datos aislados (modo=sandbox)    Datos reales (modo=real)
Se puede resetear                El docente evalúa esto
Banner amarillo visible          Sin banner
Excepción: sin Negocios/Banco    Con Negocios y Banco
```

**2. Agregar PUC Interactivo en ambas zonas:**
```
/aprendizaje/puc  → consulta académica del PUC
/empresa/puc      → referencia rápida mientras opera
```

---

## Estrategia técnica

- Un campo `modo` ('real' | 'sandbox') en las tablas operativas
- `modoContable()` detecta la zona por el prefijo de la URL
- Los mismos Livewire components filtran por `modo` automáticamente
- **No se crean tenants nuevos** — un solo tenant, datos separados por `modo`

---

## PARTE A — ZONA SANDBOX

### Paso A1 — Migración: campo `modo` en tablas operativas

Crear `database/migrations/tenant/2026_04_12_000001_add_modo_to_operational_tables.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'journal_entries',
        'invoices',
        'purchase_invoices',
        'purchase_orders',
        'payments',
        'cash_receipts',
        'credit_notes',
        'debit_notes',
        'fixed_assets',
        'bank_reconciliations',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'modo')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('modo', 10)->default('real')->after('id');
                    $t->index('modo');
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'modo')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('modo');
                });
            }
        }
    }
};
```

---

### Paso A2 — Helper `modoContable()`

En `app/helpers.php` agregar al final:

```php
/**
 * Retorna el modo contable activo ('real' | 'sandbox').
 * Se determina por el prefijo de la ruta actual.
 */
function modoContable(): string
{
    return request()->is('aprendizaje/*') ? 'sandbox' : 'real';
}
```

---

### Paso A3 — Scope `scopeModoActual()` en modelos

Agregar el siguiente método a cada uno de estos modelos:

- `app/Models/Tenant/JournalEntry.php`
- `app/Models/Tenant/Invoice.php`
- `app/Models/Tenant/PurchaseInvoice.php`
- `app/Models/Tenant/PurchaseOrder.php`
- `app/Models/Tenant/Payment.php`
- `app/Models/Tenant/CashReceipt.php`
- `app/Models/Tenant/CreditNote.php`
- `app/Models/Tenant/DebitNote.php`
- `app/Models/Tenant/FixedAsset.php`

```php
/**
 * Filtra por el modo activo según la zona de la URL (real o sandbox).
 */
public function scopeModoActual($query): void
{
    $query->where('modo', modoContable());
}
```

> ⚠️ NO agregar como global scope todavía — se usa explícitamente
>    en cada consulta para tener control total y evitar efectos secundarios.

---

### Paso A4 — AccountingService: estampar `modo` en asientos

En `app/Services/AccountingService.php`, en el método privado `createEntry()`,
agregar UNA línea al inicio del método:

```php
private function createEntry(array $entryData, array $lines): JournalEntry
{
    $entryData['modo'] = modoContable(); // ← agregar esta línea

    $totalDebit  = array_sum(array_column($lines, 'debit'));
    $totalCredit = array_sum(array_column($lines, 'credit'));

    if (abs($totalDebit - $totalCredit) >= 0.01) {
        throw new AccountingImbalanceException(
            "Asiento desequilibrado: débitos={$totalDebit}, créditos={$totalCredit}"
        );
    }

    $entry = JournalEntry::create($entryData);
    foreach ($lines as $line) {
        JournalLine::create(array_merge($line, ['journal_entry_id' => $entry->id]));
    }

    return $entry->load('lines');
}
```

---

### Paso A5 — Rutas `/aprendizaje/*`

En `routes/web.php`, DESPUÉS del cierre del grupo `prefix('empresa')`, agregar:

```php
// ─── Zona Aprendizaje (Sandbox) ─────────────────────────────────────────────
Route::middleware(['auth:student', 'tenant.initialize'])
    ->prefix('aprendizaje')
    ->name('sandbox.')
    ->group(function () {

        Route::get('/dashboard', [TenantDashboard::class, 'index'])->name('dashboard');

        // Maestros contables
        Route::get('/configuracion', CompanyConfig::class)->name('config');
        Route::get('/cuentas', PlanDeCuentas::class)->name('cuentas');
        Route::get('/terceros', TercerosIndex::class)->name('terceros');
        Route::get('/productos', ProductosIndex::class)->name('productos');

        // Operaciones
        Route::get('/facturas', InvoicesIndex::class)->name('facturas');
        Route::get('/compras', ComprasIndex::class)->name('compras');

        // Reportes y herramientas
        Route::get('/reportes', ReportesIndex::class)->name('reportes');
        Route::get('/reportes/pdf', ReportPdfController::class)->name('reportes.pdf');
        Route::get('/calendario-tributario', CalendarioIndex::class)->name('calendario');
        Route::get('/activos-fijos', ActivosFijosIndex::class)->name('activos-fijos');
        Route::get('/activos-fijos/pdf', ActivosFijosPdfController::class)->name('activos-fijos.pdf');
        Route::get('/conciliacion-bancaria', ConciliacionIndex::class)->name('conciliacion');
        Route::get('/conciliacion-bancaria/pdf', ConciliacionPdfController::class)->name('conciliacion.pdf');

        // Facturación electrónica simulada
        Route::prefix('facturacion-electronica')->name('fe.')->group(function () {
            Route::get('/', [FacturacionElectronicaController::class, 'index'])->name('index');
            Route::get('/crear', [FacturacionElectronicaController::class, 'crear'])->name('crear');
            Route::post('/', [FacturacionElectronicaController::class, 'store'])->name('store');
            Route::resource('resoluciones', FeResolucionController::class)
                ->except('destroy')
                ->parameters(['resoluciones' => 'resolucion']);
            Route::get('/{factura}', [FacturacionElectronicaController::class, 'show'])->name('show');
            Route::get('/{factura}/editar', [FacturacionElectronicaController::class, 'edit'])->name('edit');
            Route::put('/{factura}', [FacturacionElectronicaController::class, 'update'])->name('update');
            Route::delete('/{factura}', [FacturacionElectronicaController::class, 'destroy'])->name('destroy');
            Route::post('/{factura}/emitir', [FacturacionElectronicaController::class, 'emitir'])->name('emitir');
            Route::post('/{factura}/anular', [FacturacionElectronicaController::class, 'anular'])->name('anular');
            Route::get('/{factura}/pdf', [FacturacionElectronicaController::class, 'pdf'])->name('pdf');
        });

        // PUC Interactivo
        Route::get('/puc', \App\Livewire\Tenant\PucInteractivo::class)->name('puc');

        // Reset sandbox
        Route::post('/reset', [SandboxController::class, 'reset'])->name('reset');
    });
```

También agregar en el grupo `prefix('empresa')` existente, al final antes del cierre:

```php
// PUC Interactivo (referencia rápida desde la zona real)
Route::get('/puc', \App\Livewire\Tenant\PucInteractivo::class)->name('puc');
```

---

### Paso A6 — SandboxController

Crear `app/Http/Controllers/SandboxController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Tenant\CashReceipt;
use App\Models\Tenant\CreditNote;
use App\Models\Tenant\DebitNote;
use App\Models\Tenant\FixedAsset;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\JournalEntry;
use App\Models\Tenant\JournalLine;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PurchaseInvoice;
use App\Models\Tenant\PurchaseOrder;
use Illuminate\Support\Facades\DB;

class SandboxController extends Controller
{
    /**
     * Elimina todos los datos del sandbox del tenant actual.
     * Los datos de MI EMPRESA (modo=real) quedan intactos.
     */
    public function reset()
    {
        DB::transaction(function () {
            $entryIds = JournalEntry::where('modo', 'sandbox')->pluck('id');
            JournalLine::whereIn('journal_entry_id', $entryIds)->delete();
            JournalEntry::where('modo', 'sandbox')->delete();

            Invoice::where('modo', 'sandbox')->delete();
            PurchaseInvoice::where('modo', 'sandbox')->delete();
            PurchaseOrder::where('modo', 'sandbox')->delete();
            Payment::where('modo', 'sandbox')->delete();
            CashReceipt::where('modo', 'sandbox')->delete();
            CreditNote::where('modo', 'sandbox')->delete();
            DebitNote::where('modo', 'sandbox')->delete();
            FixedAsset::where('modo', 'sandbox')->delete();
        });

        return redirect()
            ->route('sandbox.dashboard')
            ->with('success', '✅ Empresa de aprendizaje reiniciada. ¡Puedes comenzar de nuevo!');
    }
}
```

---

### Paso A7 — Banner sandbox en el layout

En la vista del layout principal del estudiante (buscar
`resources/views/layouts/tenant.blade.php` o similar),
agregar ANTES del contenido principal (`@yield('content')` o el slot):

```blade
@if(request()->is('aprendizaje/*'))
<div style="
    background: rgba(234,179,8,0.10);
    border-left: 3px solid #ca8a04;
    padding: 0.5rem 1.5rem;
    font-size: 0.78rem;
    color: #92400e;
    display: flex;
    align-items: center;
    gap: 1rem;
">
    <span>🧪 <strong>Modo Aprendizaje</strong></span>
    <span>Las operaciones aquí no afectan tu empresa real.</span>
    <form method="POST" action="{{ route('sandbox.reset') }}" style="margin-left:auto"
          onsubmit="return confirm('¿Reiniciar toda la empresa de aprendizaje? Esta acción no se puede deshacer.')">
        @csrf
        <button type="submit" style="
            font-size:0.72rem;
            padding:0.2rem 0.7rem;
            border:1px solid #ca8a04;
            background:transparent;
            color:#92400e;
            border-radius:4px;
            cursor:pointer;
        ">🔄 Reiniciar</button>
    </form>
</div>
@endif
```

---

## PARTE B — PUC INTERACTIVO

### Paso B1 — Migración: campos académicos en `accounts`

Crear `database/migrations/tenant/2026_04_12_000002_add_academic_fields_to_accounts.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->text('descripcion')->nullable()->after('active');
            $table->text('dinamica_debe')->nullable()->after('descripcion');
            $table->text('dinamica_haber')->nullable()->after('dinamica_debe');
            $table->text('ejemplo')->nullable()->after('dinamica_haber');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['descripcion', 'dinamica_debe', 'dinamica_haber', 'ejemplo']);
        });
    }
};
```

---

### Paso B2 — Seed de contenido académico PUC

Crear `database/migrations/tenant/2026_04_12_000003_seed_puc_academic_content.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $cuentas = [
            '1105' => [
                'descripcion'    => 'Registra la existencia en dinero efectivo o en cheques con que cuenta el ente económico, tanto en moneda nacional como extranjera, disponible en forma inmediata.',
                'dinamica_debe'  => "1. Por las entradas de dinero en efectivo y cheques recibidos por cualquier concepto.\n2. Por los sobrantes en caja al efectuar arqueos.\n3. Por la constitución o incremento del fondo de caja menor.",
                'dinamica_haber' => "1. Por el valor de las consignaciones diarias en cuentas corrientes o de ahorro.\n2. Por los faltantes en caja al efectuar arqueos.\n3. Por la reducción o cancelación del fondo de caja menor.\n4. Por el valor de los pagos en efectivo.",
                'ejemplo'        => 'Venta de contado por $500.000: DR 1105 Caja $500.000 / CR 4135 Ingresos $500.000.',
            ],
            '1110' => [
                'descripcion'    => 'Registra los movimientos de fondos en cuentas corrientes y de ahorros en entidades bancarias, en moneda nacional o extranjera.',
                'dinamica_debe'  => "1. Por las consignaciones de dinero efectivo, cheques o transferencias recibidas.\n2. Por las notas crédito del banco (intereses ganados, etc.).",
                'dinamica_haber' => "1. Por el valor de los cheques girados o transferencias enviadas.\n2. Por las notas débito del banco (comisiones, GMF 4x1000, etc.).",
                'ejemplo'        => 'Pago a proveedor por transferencia $2.000.000: DR 2205 Proveedores $2.000.000 / CR 1110 Bancos $2.000.000.',
            ],
            '1305' => [
                'descripcion'    => 'Registra el valor de las deudas a cargo de clientes por concepto de ventas de bienes o servicios realizados en desarrollo de las actividades propias del ente económico.',
                'dinamica_debe'  => "1. Por el valor de las ventas a crédito (total factura con IVA).\n2. Por las notas débito enviadas al cliente.",
                'dinamica_haber' => "1. Por los pagos recibidos de los clientes (recibos de caja).\n2. Por las notas crédito aplicadas.\n3. Por las devoluciones y descuentos concedidos.",
                'ejemplo'        => 'Venta a crédito $1.190.000 (IVA incluido): DR 1305 Clientes $1.190.000 / CR 4135 Ingresos $1.000.000 / CR 2408 IVA por pagar $190.000.',
            ],
            '1355' => [
                'descripcion'    => 'Registra los anticipos de impuestos y las retenciones que terceros han practicado al ente económico (retenciones sufridas).',
                'dinamica_debe'  => "1. Por las retenciones en la fuente que le practican al ente (sufridas).\n2. Por la retención de IVA sufrida.\n3. Por la retención de ICA sufrida.\n4. Por los anticipos de impuesto de renta pagados.",
                'dinamica_haber' => "1. Por la aplicación contra el impuesto a pagar en la declaración.\n2. Por las devoluciones de saldos a favor aprobadas por la DIAN.",
                'ejemplo'        => 'Gran contribuyente retiene 3.5% sobre venta de $1.000.000: DR 1355 Anticipo impuestos $35.000 (reduce la cartera neta cobrada).',
            ],
            '1435' => [
                'descripcion'    => 'Registra el costo de las mercancías adquiridas por el ente económico para ser vendidas sin transformación. Cuenta principal de inventario para empresas comerciales.',
                'dinamica_debe'  => "1. Por el valor de las compras de mercancía.\n2. Por los fletes necesarios para poner la mercancía en almacén.\n3. Por las devoluciones en ventas (ingreso al inventario).",
                'dinamica_haber' => "1. Por el costo de la mercancía vendida (salida del inventario).\n2. Por las devoluciones en compras.\n3. Por los faltantes detectados en toma física.",
                'ejemplo'        => 'Compra mercancía $3.000.000: DR 1435 Mercancías $3.000.000 / CR 2205 Proveedores $3.000.000. Al vender: DR 6135 Costo ventas $3.000.000 / CR 1435 Mercancías $3.000.000.',
            ],
            '1592' => [
                'descripcion'    => 'Registra la acumulación de la depreciación calculada sobre el costo de propiedades, planta y equipo. Cuenta de naturaleza crédito que reduce el valor en libros del activo.',
                'dinamica_debe'  => "1. Por la venta o baja del activo (se cancela la depreciación acumulada).\n2. Por ajustes que reduzcan la depreciación acumulada.",
                'dinamica_haber' => "1. Por el valor de la cuota de depreciación del período.\n2. Por ajustes que incrementen la depreciación acumulada.",
                'ejemplo'        => 'Depreciación mensual computador $4.800.000, vida útil 36 meses: DR 5160 Gasto depreciación $133.333 / CR 1592 Depreciación acumulada $133.333.',
            ],
            '2205' => [
                'descripcion'    => 'Registra el valor de las obligaciones contraídas por el ente económico con proveedores nacionales por adquisición de bienes o servicios.',
                'dinamica_debe'  => "1. Por el valor de los pagos realizados a proveedores.\n2. Por las notas crédito recibidas de proveedores.\n3. Por las retenciones practicadas al proveedor.",
                'dinamica_haber' => "1. Por el valor de las compras a crédito (total factura del proveedor).\n2. Por las notas débito recibidas del proveedor.",
                'ejemplo'        => 'Compra a crédito $2.380.000 (IVA incluido): DR 1435 Mercancías $2.000.000 / DR 2408 IVA descontable $380.000 / CR 2205 Proveedores $2.380.000.',
            ],
            '2365' => [
                'descripcion'    => 'Registra el valor de las retenciones en la fuente practicadas por el ente económico en su calidad de agente retenedor, sobre los pagos realizados a terceros.',
                'dinamica_debe'  => "1. Por el pago a la DIAN en la declaración mensual.\n2. Por ajustes que reduzcan el saldo a pagar.",
                'dinamica_haber' => "1. Por el valor de las retenciones practicadas en cada pago a proveedores o contratistas.",
                'ejemplo'        => 'Pago honorarios $2.000.000, retención 11%: DR 5115 Honorarios $2.000.000 / CR 2365 Retención fuente $220.000 / CR 1110 Bancos $1.780.000.',
            ],
            '2367' => [
                'descripcion'    => 'Registra el valor del IVA retenido por el ente económico en su calidad de agente retenedor del impuesto sobre las ventas.',
                'dinamica_debe'  => "1. Por el pago a la DIAN del IVA retenido declarado.",
                'dinamica_haber' => "1. Por el valor del IVA retenido a proveedores en cada compra (generalmente 50% del IVA facturado).",
                'ejemplo'        => 'Compra $1.000.000 + IVA $190.000, se retiene 50% del IVA: CR 2367 Reteiva $95.000.',
            ],
            '2408' => [
                'descripcion'    => 'Registra el IVA generado en ventas (cobrado) y el IVA pagado en compras (descontable). Saldo crédito = IVA a pagar DIAN; saldo débito = saldo a favor.',
                'dinamica_debe'  => "1. Por el IVA pagado en compras (IVA descontable).\n2. Por el pago del IVA neto a la DIAN en la declaración bimestral.",
                'dinamica_haber' => "1. Por el IVA cobrado en ventas (IVA generado).",
                'ejemplo'        => 'Venta $1.000.000 + IVA 19%: CR 2408 IVA por pagar $190.000. Compra $500.000 + IVA: DR 2408 IVA descontable $95.000. Pago neto DIAN: $190.000 - $95.000 = $95.000.',
            ],
            '3105' => [
                'descripcion'    => 'Registra el valor del capital suscrito y pagado por los socios o accionistas al constituir la empresa o en aumentos de capital posteriores.',
                'dinamica_debe'  => "1. Por la reducción de capital aprobada por la asamblea.\n2. Por la absorción de pérdidas acumuladas.",
                'dinamica_haber' => "1. Por el capital pagado al constituir la empresa.\n2. Por los aumentos de capital aprobados y pagados.",
                'ejemplo'        => 'Constitución SAS capital $100.000.000: DR 1110 Bancos $100.000.000 / CR 3105 Capital suscrito y pagado $100.000.000.',
            ],
            '4135' => [
                'descripcion'    => 'Registra el valor de los ingresos obtenidos por ventas de bienes o servicios propios de la actividad comercial del ente económico.',
                'dinamica_debe'  => "1. Por las devoluciones en ventas.\n2. Por los descuentos comerciales concedidos.\n3. Por el cierre al final del período.",
                'dinamica_haber' => "1. Por el valor de las ventas (sin IVA).\n2. Por ajustes que incrementen los ingresos.",
                'ejemplo'        => 'Venta 10 unidades a $100.000 c/u: DR 1305 Clientes $1.190.000 / CR 4135 Ingresos $1.000.000 / CR 2408 IVA $190.000.',
            ],
            '5160' => [
                'descripcion'    => 'Registra el gasto por pérdida de valor de propiedades, planta y equipo por uso, paso del tiempo u obsolescencia.',
                'dinamica_debe'  => "1. Por el valor de la cuota de depreciación del período.",
                'dinamica_haber' => "1. Por el cierre al final del período contable.",
                'ejemplo'        => 'Vehículo $60.000.000, vida útil 60 meses, valor residual $5.000.000. Cuota mensual = $916.667: DR 5160 Gasto depreciación $916.667 / CR 1592 Depreciación acumulada $916.667.',
            ],
            '5195' => [
                'descripcion'    => 'Registra los gastos administrativos y de operación que no tienen clasificación específica en otras subcuentas de gastos.',
                'dinamica_debe'  => "1. Por el valor de gastos varios no clasificados en otras cuentas.",
                'dinamica_haber' => "1. Por el cierre al final del período contable.",
                'ejemplo'        => 'Compra útiles de oficina $150.000: DR 5195 Gastos generales $150.000 / CR 1105 Caja $150.000.',
            ],
            '6135' => [
                'descripcion'    => 'Registra el costo de las mercancías vendidas durante el período. Representa el valor en libros de los bienes que salieron del inventario por ventas.',
                'dinamica_debe'  => "1. Por el costo de las mercancías entregadas a clientes.",
                'dinamica_haber' => "1. Por las devoluciones en ventas (regresa al inventario).\n2. Por el cierre al final del período.",
                'ejemplo'        => 'Mercancía vendida con costo $700.000: DR 6135 Costo de ventas $700.000 / CR 1435 Mercancías $700.000. (Siempre acompaña la factura de venta).',
            ],
        ];

        foreach ($cuentas as $code => $data) {
            DB::table('accounts')->where('code', $code)->update($data);
        }
    }

    public function down(): void
    {
        DB::table('accounts')->update([
            'descripcion'    => null,
            'dinamica_debe'  => null,
            'dinamica_haber' => null,
            'ejemplo'        => null,
        ]);
    }
};
```

---

### Paso B3 — Modelo Account: campos fillable + helpers

En `app/Models/Tenant/Account.php`, actualizar `$fillable` y agregar dos métodos:

```php
protected $fillable = [
    'code',
    'name',
    'type',
    'nature',
    'parent_id',
    'level',
    'active',
    'descripcion',
    'dinamica_debe',
    'dinamica_haber',
    'ejemplo',
];

/**
 * Indica si la cuenta tiene contenido académico cargado.
 */
public function tieneContenidoAcademico(): bool
{
    return ! empty($this->descripcion);
}

/**
 * Retorna la cuenta que tiene la dinámica (la de 4 dígitos).
 * Las subcuentas heredan de su cuenta padre.
 */
public function cuentaConDinamica(): self
{
    if ($this->tieneContenidoAcademico()) {
        return $this;
    }

    return $this->parent?->cuentaConDinamica() ?? $this;
}
```

---

### Paso B4 — Componente Livewire PucInteractivo

Crear `app/Livewire/Tenant/PucInteractivo.php`:

```php
<?php

namespace App\Livewire\Tenant;

use App\Models\Tenant\Account;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('PUC Interactivo')]
class PucInteractivo extends Component
{
    public string $search = '';
    public ?int $selectedId = null;
    public ?Account $selectedAccount = null;
    public string $claseActiva = '';

    public function seleccionar(int $id): void
    {
        $account = Account::find($id);

        if ($account && ! $account->tieneContenidoAcademico()) {
            $conDinamica = $account->cuentaConDinamica();
            $account->descripcion    = $conDinamica->descripcion;
            $account->dinamica_debe  = $conDinamica->dinamica_debe;
            $account->dinamica_haber = $conDinamica->dinamica_haber;
            $account->ejemplo        = $conDinamica->ejemplo;
        }

        $this->selectedId      = $id;
        $this->selectedAccount = $account;
    }

    public function filtrarClase(string $clase): void
    {
        $this->claseActiva     = ($clase === $this->claseActiva) ? '' : $clase;
        $this->selectedAccount = null;
        $this->selectedId      = null;
    }

    public function render(): mixed
    {
        $query = Account::query()->orderBy('code');

        if ($this->search) {
            $query->where(fn ($q) =>
                $q->where('code', 'ilike', "%{$this->search}%")
                  ->orWhere('name', 'ilike', "%{$this->search}%")
            );
        } elseif ($this->claseActiva) {
            $query->where('code', 'like', $this->claseActiva . '%');
        }

        $accounts = $query->get();
        $clases   = Account::where('level', 1)->orderBy('code')->get();

        return view('livewire.tenant.puc.puc-interactivo', compact('accounts', 'clases'));
    }
}
```

---

### Paso B5 — Vista del PUC Interactivo

Crear `resources/views/livewire/tenant/puc/puc-interactivo.blade.php`:

```blade
<div>
    {{-- Header --}}
    <div class="page-header">
        <div>
            <div class="page-header-label">APRENDIZAJE CONTABLE</div>
            <h1 class="page-title">PUC Interactivo</h1>
            <p class="page-subtitle">Plan Único de Cuentas colombiano — Navega y consulta cada cuenta</p>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:320px 1fr; gap:1.5rem; align-items:start;">

        {{-- Panel izquierdo: filtros y lista --}}
        <div style="background:var(--card-bg,#fff); border-radius:10px; border:1px solid var(--border-color,#e5e7eb); overflow:hidden;">

            {{-- Búsqueda --}}
            <div style="padding:1rem; border-bottom:1px solid var(--border-color,#e5e7eb);">
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Buscar código o nombre..."
                       style="width:100%; padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:6px; font-size:0.85rem;" />
            </div>

            {{-- Filtros por clase --}}
            @if(! $search)
            <div style="padding:0.75rem; border-bottom:1px solid var(--border-color,#e5e7eb);">
                <div style="font-size:0.7rem; color:#6b7280; margin-bottom:0.5rem; text-transform:uppercase; letter-spacing:0.05em;">Filtrar por clase</div>
                @foreach($clases as $clase)
                <button wire:click="filtrarClase('{{ $clase->code }}')"
                        style="display:block; width:100%; text-align:left; padding:0.35rem 0.6rem; margin-bottom:2px; border-radius:5px; font-size:0.8rem; border:none; cursor:pointer;
                               background:{{ $claseActiva === $clase->code ? '#1a4731' : 'transparent' }};
                               color:{{ $claseActiva === $clase->code ? '#fff' : '#374151' }};">
                    <strong>{{ $clase->code }}</strong> — {{ Str::limit($clase->name, 25) }}
                </button>
                @endforeach
            </div>
            @endif

            {{-- Lista de cuentas --}}
            <div style="max-height:520px; overflow-y:auto;">
                @forelse($accounts as $account)
                <button wire:click="seleccionar({{ $account->id }})"
                        style="display:flex; align-items:center; gap:0.5rem; width:100%; text-align:left;
                               padding:{{ $account->level === 1 ? '0.6rem 1rem' : ($account->level === 2 ? '0.5rem 1.25rem' : ($account->level === 3 ? '0.45rem 1.5rem' : '0.4rem 1.75rem')) }};
                               border:none; border-bottom:1px solid #f3f4f6; cursor:pointer;
                               font-size:{{ $account->level <= 2 ? '0.8rem' : '0.78rem' }};
                               font-weight:{{ $account->level <= 2 ? '600' : '400' }};
                               background:{{ $selectedId === $account->id ? '#f0fdf4' : 'transparent' }};
                               color:{{ $selectedId === $account->id ? '#1a4731' : '#374151' }};">
                    <span style="font-family:monospace; color:#6b7280; min-width:52px; font-size:0.75rem;">{{ $account->code }}</span>
                    <span style="flex:1;">{{ $account->name }}</span>
                    @if($account->tieneContenidoAcademico())
                    <span style="color:#16a34a; font-size:0.6rem;" title="Contenido disponible">●</span>
                    @endif
                </button>
                @empty
                <div style="padding:2rem; text-align:center; color:#9ca3af; font-size:0.85rem;">
                    No se encontraron cuentas
                </div>
                @endforelse
            </div>
        </div>

        {{-- Panel derecho: detalle --}}
        <div style="background:var(--card-bg,#fff); border-radius:10px; border:1px solid var(--border-color,#e5e7eb); padding:1.5rem; min-height:400px;">

            @if($selectedAccount)

            {{-- Badges --}}
            <div style="display:flex; gap:0.5rem; margin-bottom:1rem; flex-wrap:wrap;">
                <span style="background:#f0fdf4; color:#166534; padding:0.2rem 0.6rem; border-radius:20px; font-size:0.72rem; font-weight:600;">
                    {{ ucfirst($selectedAccount->type) }}
                </span>
                <span style="background:#eff6ff; color:#1e40af; padding:0.2rem 0.6rem; border-radius:20px; font-size:0.72rem;">
                    Naturaleza: {{ ucfirst($selectedAccount->nature) }}
                </span>
                <span style="background:#fafafa; color:#6b7280; padding:0.2rem 0.6rem; border-radius:20px; font-size:0.72rem;">
                    Nivel {{ $selectedAccount->level }}
                </span>
            </div>

            {{-- Código y nombre --}}
            <div style="font-family:monospace; font-size:1.5rem; font-weight:700; color:#1a4731;">
                {{ $selectedAccount->code }}
            </div>
            <h2 style="font-size:1.1rem; font-weight:600; color:#111827; margin:0.25rem 0 1.25rem;">
                {{ $selectedAccount->name }}
            </h2>

            @if($selectedAccount->tieneContenidoAcademico())

            {{-- Descripción --}}
            <div style="margin-bottom:1.25rem;">
                <div style="font-size:0.72rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.4rem;">
                    📋 Descripción
                </div>
                <p style="font-size:0.88rem; color:#374151; line-height:1.6; margin:0;">
                    {{ $selectedAccount->descripcion }}
                </p>
            </div>

            {{-- Dinámica débito/crédito --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.25rem;">
                <div style="background:#fef9f0; border:1px solid #fbbf24; border-radius:8px; padding:1rem;">
                    <div style="font-size:0.72rem; font-weight:700; color:#92400e; margin-bottom:0.5rem;">
                        ↑ DÉBITO — Se debita cuando...
                    </div>
                    <div style="font-size:0.82rem; color:#374151; line-height:1.6; white-space:pre-line;">{{ $selectedAccount->dinamica_debe }}</div>
                </div>
                <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:1rem;">
                    <div style="font-size:0.72rem; font-weight:700; color:#166534; margin-bottom:0.5rem;">
                        ↓ CRÉDITO — Se acredita cuando...
                    </div>
                    <div style="font-size:0.82rem; color:#374151; line-height:1.6; white-space:pre-line;">{{ $selectedAccount->dinamica_haber }}</div>
                </div>
            </div>

            {{-- Ejemplo --}}
            @if($selectedAccount->ejemplo)
            <div style="background:#f8fafc; border-left:3px solid #1a4731; border-radius:0 8px 8px 0; padding:1rem;">
                <div style="font-size:0.72rem; font-weight:600; color:#1a4731; margin-bottom:0.4rem;">💡 Ejemplo práctico</div>
                <p style="font-size:0.84rem; color:#374151; margin:0; line-height:1.6;">{{ $selectedAccount->ejemplo }}</p>
            </div>
            @endif

            @else
            <div style="text-align:center; padding:2rem; color:#9ca3af;">
                <div style="font-size:2rem; margin-bottom:0.5rem;">📄</div>
                <p style="font-size:0.88rem;">Esta cuenta no tiene contenido académico detallado.</p>
                @if($selectedAccount->level >= 4)
                <p style="font-size:0.82rem;">
                    Consulta la cuenta padre
                    <strong>{{ substr($selectedAccount->code, 0, 4) }}</strong>
                    para ver su dinámica.
                </p>
                @endif
            </div>
            @endif

            @else
            {{-- Estado vacío --}}
            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:300px; text-align:center; color:#9ca3af;">
                <div style="font-size:3rem; margin-bottom:1rem;">📚</div>
                <h3 style="font-size:1rem; color:#374151; margin-bottom:0.5rem;">Selecciona una cuenta</h3>
                <p style="font-size:0.85rem; max-width:280px; line-height:1.5;">
                    Haz clic en cualquier cuenta del PUC para ver su descripción,
                    dinámica contable y ejemplos prácticos.
                </p>
                <div style="margin-top:1rem; font-size:0.78rem; background:#f9fafb; padding:0.5rem 1rem; border-radius:6px;">
                    Las cuentas con <span style="color:#16a34a;">●</span> tienen contenido académico completo
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
```

---

## PARTE C — SIDEBAR CORREGIDO

### Paso C1 — Estructura final del sidebar

Buscar el layout del sidebar del estudiante y reemplazar la estructura
de ítems para que quede exactamente así:

```
─── APRENDIZAJE CONTABLE ──────────────────────────────
  🏠 Inicio              → /aprendizaje/dashboard
  📚 PUC Interactivo     → /aprendizaje/puc             ← NUEVO
  🧾 Facturas            → /aprendizaje/facturas
  🛒 Compras             → /aprendizaje/compras
  👥 Terceros            → /aprendizaje/terceros
  📦 Productos           → /aprendizaje/productos
  📊 Plan de cuentas     → /aprendizaje/cuentas
  📈 Reportes            → /aprendizaje/reportes
  📅 Calendario          → /aprendizaje/calendario-tributario
  🏗️ Activos fijos       → /aprendizaje/activos-fijos
  ⚖️ Conciliación        → /aprendizaje/conciliacion-bancaria
  ⚡ F. Electrónica      → /aprendizaje/facturacion-electronica
  🏢 Empresas Maestras   → /empresa/referencias         (sin cambio)

─── MI EMPRESA ────────────────────────────────────────
  🏠 Inicio              → /empresa/dashboard
  📚 PUC                 → /empresa/puc                 ← NUEVO
  ⚙️ Configuración       → /empresa/configuracion
  👥 Terceros            → /empresa/terceros
  📦 Productos           → /empresa/productos
  🧾 Facturas            → /empresa/facturas
  🛒 Compras             → /empresa/compras
  📈 Reportes            → /empresa/reportes
  📅 Calendario          → /empresa/calendario-tributario
  🏗️ Activos fijos       → /empresa/activos-fijos
  ⚖️ Conciliación        → /empresa/conciliacion-bancaria
  ⚡ F. Electrónica      → /empresa/facturacion-electronica
  🤝 Negocios            → /empresa/negocios
  🏦 Banco               → /empresa/banco
```

---

## Lo que NO se toca

- Ningún Livewire component existente (se reutilizan tal cual)
- `IntercompanyService` — Negocios siempre es modo 'real'
- `bank_accounts` y `bank_transactions` — Banco siempre modo 'real'
- PUC Seeder existente — solo se agregan campos académicos encima
- Lógica de docente/auditoría — sin cambios

---

## Orden de ejecución

```
PARTE A — Sandbox
1.  Migración campo modo (Paso A1)
2.  Helper modoContable() en helpers.php (Paso A2)
3.  Scope scopeModoActual() en 9 modelos (Paso A3)
4.  Agregar $entryData['modo'] en AccountingService (Paso A4)
5.  Rutas /aprendizaje/* en web.php (Paso A5)
6.  Crear SandboxController (Paso A6)
7.  Banner sandbox en layout (Paso A7)

PARTE B — PUC Interactivo
8.  Migración campos académicos en accounts (Paso B1)
9.  Seed contenido PUC (Paso B2)
10. Actualizar modelo Account (Paso B3)
11. Crear PucInteractivo.php (Paso B4)
12. Crear vista puc-interactivo.blade.php (Paso B5)

PARTE C — Sidebar
13. Actualizar sidebar con estructura final (Paso C1)
```

---

## Verificación

1. `/aprendizaje/facturas` → crear factura → aparece con `modo=sandbox`
2. `/empresa/facturas` → la factura sandbox NO aparece
3. `/aprendizaje/reportes` → libro diario solo con asientos sandbox
4. `/empresa/reportes` → libro diario solo con asientos reales
5. Banner amarillo visible en todas las páginas `/aprendizaje/*`
6. Banner NO aparece en `/empresa/*`
7. Botón "Reiniciar" en banner → borra sandbox, empresa real intacta
8. `/aprendizaje/puc` → PUC interactivo carga, 9 clases visibles
9. Clic en `1305 Clientes` → descripción + dinámica + ejemplo visible
10. Clic en subcuenta `130505` → hereda contenido de `1305`
11. `/empresa/puc` → mismo componente funciona en zona real
12. Sidebar muestra estructura correcta en ambas zonas
13. Todos los ítems del sandbox apuntan a `/aprendizaje/*`
14. Todos los ítems de MI EMPRESA apuntan a `/empresa/*`
