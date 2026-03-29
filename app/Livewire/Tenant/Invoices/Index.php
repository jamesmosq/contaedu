<?php

namespace App\Livewire\Tenant\Invoices;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\ReceiptStatus;
use App\Models\Tenant\CashReceipt;
use App\Models\Tenant\CashReceiptItem;
use App\Models\Tenant\CreditNote;
use App\Models\Tenant\DebitNote;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\Product;
use App\Models\Tenant\Third;
use App\Services\AccountingService;
use App\Services\DebitNoteService;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.tenant')]
#[Title('Facturas')]
class Index extends Component
{
    use WithPagination;

    // Pestaña activa: 'facturas' | 'recibos' | 'notas' | 'notas_debito'
    public string $activeTab = 'facturas';

    // ─── Facturas ────────────────────────────────────────────────────────────
    public string $search = '';

    public string $filterStatus = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public int $third_id = 0;

    public string $date = '';

    public string $due_date = '';

    public string $series = 'FV';

    public string $notes = '';

    public array $lines = [];

    // ─── Recibo de caja ──────────────────────────────────────────────────────
    public bool $showReceiptForm = false;

    public ?int $receipt_invoice_id = null;

    public string $receipt_date = '';

    public float $receipt_amount = 0;

    public string $receipt_notes = '';

    public string $receipt_ref = '';   // solo lectura, para mostrar en modal

    // ─── Nota de crédito ─────────────────────────────────────────────────────
    public bool $showCreditNoteForm = false;

    public ?int $cn_invoice_id = null;

    public string $cn_date = '';

    public string $cn_reason = '';

    public array $cn_lines = [];  // [{invoice_line_id, description, max_qty, qty, unit_price, tax_rate}]

    public string $cn_invoice_ref = '';  // solo lectura, para mostrar en modal

    // ─── Nota débito ─────────────────────────────────────────────────────────
    public bool $showDebitNoteForm = false;

    public ?int $dn_invoice_id = null;

    public string $dn_date = '';

    public string $dn_reason = '';

    public float $dn_subtotal = 0;

    public int $dn_tax_rate = 19;

    public string $dn_invoice_ref = '';  // solo lectura, para mostrar en modal

    public function mount(): void
    {
        $this->date = now()->toDateString();
        $this->due_date = now()->addDays(30)->toDateString();
    }

    // ─── Facturas: CRUD ──────────────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $invoice = Invoice::with('lines')->findOrFail($id);
        if (! $invoice->isBorrador()) {
            return;
        }

        $this->editingId = $id;
        $this->third_id = $invoice->third_id;
        $this->date = $invoice->date->toDateString();
        $this->due_date = $invoice->due_date?->toDateString() ?? '';
        $this->series = $invoice->series;
        $this->notes = $invoice->notes ?? '';
        $this->lines = $invoice->lines->map(fn ($l) => [
            'product_id' => $l->product_id,
            'description' => $l->description,
            'qty' => $l->qty,
            'unit_price' => $l->unit_price,
            'discount_pct' => $l->discount_pct,
            'tax_rate' => $l->tax_rate,
        ])->toArray();

        $this->showForm = true;
    }

    public function addLine(): void
    {
        $this->lines[] = [
            'product_id' => null,
            'description' => '',
            'qty' => 1,
            'unit_price' => 0,
            'discount_pct' => 0,
            'tax_rate' => 19,
        ];
    }

    public function removeLine(int $index): void
    {
        array_splice($this->lines, $index, 1);
        $this->lines = array_values($this->lines);
    }

    public function updatedLines(mixed $value, string $key): void
    {
        if (str_ends_with($key, '.product_id') && $value) {
            $idx = (int) explode('.', $key)[0];
            $product = Product::find($value);
            if ($product) {
                $this->lines[$idx]['description'] = $product->name;
                $this->lines[$idx]['unit_price'] = $product->sale_price;
                $this->lines[$idx]['tax_rate'] = $product->tax_rate->value;
            }
        }
    }

    public function save(InvoiceService $service): void
    {
        $this->validate([
            'third_id' => ['required', 'integer', 'min:1'],
            'date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'series' => ['required', 'string', 'max:5'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string'],
            'lines.*.qty' => ['required', 'numeric', 'min:0.01'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
        ], [
            'third_id.min' => 'Selecciona un cliente.',
            'lines.min' => 'Agrega al menos una línea.',
            'lines.*.description.required' => 'La descripción es requerida.',
            'lines.*.qty.min' => 'La cantidad debe ser mayor a 0.',
        ]);

        $lineData = array_map(function (array $l): array {
            $base = $l['unit_price'] * $l['qty'];
            $discount = $base * ($l['discount_pct'] / 100);
            $subtotal = round($base - $discount, 2);
            $tax = round($subtotal * ($l['tax_rate'] / 100), 2);

            return [
                'product_id' => $l['product_id'] ?: null,
                'description' => $l['description'],
                'qty' => $l['qty'],
                'unit_price' => $l['unit_price'],
                'discount_pct' => $l['discount_pct'],
                'tax_rate' => (int) $l['tax_rate'],
                'line_subtotal' => $subtotal,
                'line_tax' => $tax,
                'line_total' => $subtotal + $tax,
            ];
        }, $this->lines);

        $headerData = [
            'type' => InvoiceType::Venta->value,
            'third_id' => $this->third_id,
            'date' => $this->date,
            'due_date' => $this->due_date ?: null,
            'series' => $this->series,
            'notes' => $this->notes ?: null,
            'status' => InvoiceStatus::Borrador->value,
        ];

        $service->saveDraft($headerData, $lineData, $this->editingId);
        $this->resetForm();
    }

    public function confirm(int $id, InvoiceService $service): void
    {
        try {
            $service->confirm(Invoice::with('lines.product', 'third')->findOrFail($id));
            $this->dispatch('notify', type: 'success', message: 'Factura confirmada y asiento contable generado.');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function annul(int $id, InvoiceService $service): void
    {
        try {
            $service->annul(Invoice::with('lines.product', 'third')->findOrFail($id));
            $this->dispatch('notify', type: 'success', message: 'Factura anulada. Asiento de reverso generado.');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function delete(int $id): void
    {
        $invoice = Invoice::findOrFail($id);
        if ($invoice->isBorrador()) {
            $invoice->delete();
        }
    }

    public function cancelForm(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'third_id', 'notes', 'lines']);
        $this->showForm = false;
        $this->date = now()->toDateString();
        $this->due_date = now()->addDays(30)->toDateString();
        $this->series = 'FV';
    }

    // ─── Recibo de caja ──────────────────────────────────────────────────────

    public function openReceipt(int $invoiceId): void
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $this->receipt_invoice_id = $invoiceId;
        $this->receipt_date = now()->toDateString();
        $this->receipt_amount = round($invoice->balance(), 2);
        $this->receipt_notes = '';
        $this->receipt_ref = $invoice->fullReference().' — '.$invoice->third->name;
        $this->showReceiptForm = true;
    }

    public function saveReceipt(AccountingService $accounting): void
    {
        $this->validate([
            'receipt_invoice_id' => ['required', 'integer'],
            'receipt_date' => ['required', 'date'],
            'receipt_amount' => ['required', 'numeric', 'min:0.01'],
        ], [
            'receipt_amount.min' => 'El monto debe ser mayor a cero.',
        ]);

        $invoice = Invoice::with('third')->findOrFail($this->receipt_invoice_id);

        if ($this->receipt_amount > $invoice->balance() + 0.01) {
            $this->addError('receipt_amount', 'El monto supera el saldo pendiente de la factura.');

            return;
        }

        try {
            DB::transaction(function () use ($invoice, $accounting) {
                $receipt = CashReceipt::create([
                    'third_id' => $invoice->third_id,
                    'date' => $this->receipt_date,
                    'total' => $this->receipt_amount,
                    'notes' => $this->receipt_notes ?: null,
                    'status' => ReceiptStatus::Borrador->value,
                ]);

                CashReceiptItem::create([
                    'cash_receipt_id' => $receipt->id,
                    'invoice_id' => $invoice->id,
                    'amount_applied' => $this->receipt_amount,
                ]);

                $receipt->load('third');
                $accounting->generateReceiptEntry($receipt);
                $receipt->update(['status' => ReceiptStatus::Aplicado->value]);
            });

            $this->showReceiptForm = false;
            $this->reset(['receipt_invoice_id', 'receipt_date', 'receipt_amount', 'receipt_notes', 'receipt_ref']);
            $this->dispatch('notify', type: 'success', message: 'Recibo de caja registrado y asiento generado.');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    // ─── Nota de crédito ─────────────────────────────────────────────────────

    public function openCreditNote(int $invoiceId): void
    {
        $invoice = Invoice::with('lines')->findOrFail($invoiceId);

        $this->cn_invoice_id = $invoiceId;
        $this->cn_date = now()->toDateString();
        $this->cn_reason = '';
        $this->cn_invoice_ref = $invoice->fullReference().' — '.$invoice->third->name;

        $this->cn_lines = $invoice->lines->map(fn ($l) => [
            'invoice_line_id' => $l->id,
            'description' => $l->description,
            'max_qty' => $l->qty,
            'qty' => $l->qty,
            'unit_price' => $l->unit_price,
            'tax_rate' => $l->tax_rate,
        ])->toArray();

        $this->showCreditNoteForm = true;
    }

    public function saveCreditNote(AccountingService $accounting): void
    {
        $this->validate([
            'cn_invoice_id' => ['required', 'integer'],
            'cn_date' => ['required', 'date'],
            'cn_reason' => ['required', 'string', 'min:5'],
            'cn_lines' => ['required', 'array', 'min:1'],
            'cn_lines.*.qty' => ['required', 'numeric', 'min:0'],
        ], [
            'cn_reason.min' => 'La razón debe tener al menos 5 caracteres.',
            'cn_lines.*.qty' => 'La cantidad no puede ser negativa.',
        ]);

        $activeLines = array_filter($this->cn_lines, fn ($l) => $l['qty'] > 0);

        if (empty($activeLines)) {
            $this->addError('cn_lines', 'Al menos una línea debe tener cantidad mayor a 0.');

            return;
        }

        $invoice = Invoice::with('third')->findOrFail($this->cn_invoice_id);

        try {
            DB::transaction(function () use ($invoice, $activeLines, $accounting) {
                $subtotal = 0.0;
                $taxAmount = 0.0;

                $noteLines = [];
                foreach ($activeLines as $l) {
                    $lineSub = round($l['unit_price'] * $l['qty'], 2);
                    $lineTax = round($lineSub * ($l['tax_rate'] / 100), 2);
                    $lineTotal = $lineSub + $lineTax;

                    $subtotal += $lineSub;
                    $taxAmount += $lineTax;

                    $noteLines[] = [
                        'invoice_line_id' => $l['invoice_line_id'],
                        'description' => $l['description'],
                        'qty' => $l['qty'],
                        'unit_price' => $l['unit_price'],
                        'tax_rate' => (int) $l['tax_rate'],
                        'line_subtotal' => $lineSub,
                        'line_tax' => $lineTax,
                        'line_total' => $lineTotal,
                    ];
                }

                $subtotal = round($subtotal, 2);
                $taxAmount = round($taxAmount, 2);
                $total = $subtotal + $taxAmount;

                $creditNote = CreditNote::create([
                    'invoice_id' => $invoice->id,
                    'date' => $this->cn_date,
                    'reason' => $this->cn_reason,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total' => $total,
                    'status' => 'aplicada',
                ]);

                foreach ($noteLines as $nl) {
                    $creditNote->lines()->create($nl);
                }

                $creditNote->load('invoice.third');
                $accounting->generateCreditNoteEntry($creditNote);
            });

            $this->showCreditNoteForm = false;
            $this->reset(['cn_invoice_id', 'cn_date', 'cn_reason', 'cn_lines', 'cn_invoice_ref']);
            $this->dispatch('notify', type: 'success', message: 'Nota de crédito aplicada y asiento generado.');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    // ─── Nota débito ─────────────────────────────────────────────────────────

    public function openDebitNote(int $invoiceId): void
    {
        $invoice = Invoice::with('third')->findOrFail($invoiceId);

        $this->dn_invoice_id = $invoiceId;
        $this->dn_date = now()->toDateString();
        $this->dn_reason = '';
        $this->dn_subtotal = 0;
        $this->dn_tax_rate = 19;
        $this->dn_invoice_ref = $invoice->fullReference().' — '.$invoice->third->name;

        $this->showDebitNoteForm = true;
    }

    public function saveDebitNote(DebitNoteService $service): void
    {
        $this->validate([
            'dn_invoice_id' => ['required', 'integer'],
            'dn_date' => ['required', 'date'],
            'dn_reason' => ['required', 'string', 'min:5'],
            'dn_subtotal' => ['required', 'numeric', 'min:0.01'],
            'dn_tax_rate' => ['required', 'integer', 'in:0,5,19'],
        ], [
            'dn_reason.min' => 'La razón debe tener al menos 5 caracteres.',
            'dn_subtotal.min' => 'El valor debe ser mayor a cero.',
        ]);

        try {
            $invoice = Invoice::findOrFail($this->dn_invoice_id);
            $service->create($invoice, [
                'date' => $this->dn_date,
                'reason' => $this->dn_reason,
                'subtotal' => $this->dn_subtotal,
                'tax_rate' => $this->dn_tax_rate,
            ]);

            $this->showDebitNoteForm = false;
            $this->reset(['dn_invoice_id', 'dn_date', 'dn_reason', 'dn_subtotal', 'dn_tax_rate', 'dn_invoice_ref']);
            $this->dispatch('notify', type: 'success', message: 'Nota débito creada en borrador.');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function confirmDebitNote(int $id, DebitNoteService $service): void
    {
        try {
            $service->confirm(DebitNote::with('invoice.third')->findOrFail($id));
            $this->dispatch('notify', type: 'success', message: 'Nota débito emitida. Asiento contable generado.');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function annulDebitNote(int $id, DebitNoteService $service): void
    {
        try {
            $service->annul(DebitNote::findOrFail($id));
            $this->dispatch('notify', type: 'success', message: 'Nota débito anulada. Asiento de reverso generado.');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage();
    }

    public function render(): mixed
    {
        $invoices = Invoice::with('third')
            ->when($this->search, fn ($q) => $q->whereHas('third', fn ($q2) => $q2->where('name', 'ilike', "%{$this->search}%"))
                ->orWhere('series', 'ilike', "%{$this->search}%"))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(15);

        $receipts = CashReceipt::with('third')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $creditNotes = CreditNote::with('invoice.third')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $debitNotes = DebitNote::with('invoice.third')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $thirds = Third::where('type', '!=', 'proveedor')->where('active', true)->orderBy('name')->get();
        $products = Product::where('active', true)->orderBy('name')->get();
        $statuses = InvoiceStatus::cases();

        return view('livewire.tenant.invoices.index', compact(
            'invoices', 'receipts', 'creditNotes', 'debitNotes', 'thirds', 'products', 'statuses'
        ))->title('Facturación');
    }
}
