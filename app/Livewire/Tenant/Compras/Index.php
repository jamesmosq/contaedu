<?php
namespace App\Livewire\Tenant\Compras;

use App\Enums\PurchaseInvoiceStatus;
use App\Models\Tenant\PurchaseInvoice;
use App\Models\Tenant\PurchaseInvoiceLine;
use App\Models\Tenant\Payment;
use App\Models\Tenant\Third;
use App\Models\Tenant\Product;
use App\Services\PurchaseService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    // Vista activa: 'invoices' | 'payments'
    public string $view = 'invoices';

    public string $search      = '';
    public bool   $showForm    = false;
    public ?int   $editingId   = null;

    // Cabecera factura compra
    public int    $third_id                  = 0;
    public string $supplier_invoice_number   = '';
    public string $date                      = '';
    public string $due_date                  = '';
    public string $notes                     = '';
    public array  $lines                     = [];

    // Pago
    public bool   $showPaymentForm           = false;
    public int    $payment_third_id          = 0;
    public string $payment_date              = '';
    public string $payment_notes             = '';
    public array  $payment_items             = []; // [{purchase_invoice_id, amount_applied, invoice_ref, invoice_balance}]

    public function mount(): void
    {
        $this->date         = now()->toDateString();
        $this->due_date     = now()->addDays(30)->toDateString();
        $this->payment_date = now()->toDateString();
    }

    // ─── Facturas de compra ──────────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->resetInvoiceForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $invoice = PurchaseInvoice::with('lines')->findOrFail($id);
        $this->editingId                = $id;
        $this->third_id                 = $invoice->third_id;
        $this->supplier_invoice_number  = $invoice->supplier_invoice_number ?? '';
        $this->date                     = $invoice->date->toDateString();
        $this->due_date                 = $invoice->due_date?->toDateString() ?? '';
        $this->notes                    = $invoice->notes ?? '';
        $this->lines                    = $invoice->lines->map(fn($l) => [
            'product_id'  => $l->product_id,
            'description' => $l->description,
            'qty'         => $l->qty,
            'unit_cost'   => $l->unit_cost,
            'tax_rate'    => $l->tax_rate,
        ])->toArray();
        $this->showForm = true;
    }

    public function addLine(): void
    {
        $this->lines[] = ['product_id'=>null,'description'=>'','qty'=>1,'unit_cost'=>0,'tax_rate'=>19];
    }

    public function removeLine(int $index): void
    {
        array_splice($this->lines, $index, 1);
        $this->lines = array_values($this->lines);
    }

    public function updatedLines(mixed $value, string $key): void
    {
        if (str_ends_with($key, '.product_id') && $value) {
            $idx     = (int) explode('.', $key)[0];
            $product = Product::find($value);
            if ($product) {
                $this->lines[$idx]['description'] = $product->name;
                $this->lines[$idx]['unit_cost']   = $product->cost_price;
                $this->lines[$idx]['tax_rate']    = $product->tax_rate->value;
            }
        }
    }

    public function save(PurchaseService $service): void
    {
        $this->validate([
            'third_id'   => ['required','integer','min:1'],
            'date'       => ['required','date'],
            'lines'      => ['required','array','min:1'],
            'lines.*.description' => ['required','string'],
            'lines.*.qty'         => ['required','numeric','min:0.01'],
            'lines.*.unit_cost'   => ['required','numeric','min:0'],
        ], [
            'third_id.min' => 'Selecciona un proveedor.',
            'lines.min'    => 'Agrega al menos una línea.',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($service) {
            $lineData = array_map(function (array $l): array {
                $subtotal = round($l['unit_cost'] * $l['qty'], 2);
                $tax      = round($subtotal * ($l['tax_rate'] / 100), 2);
                return [
                    'product_id'   => $l['product_id'] ?: null,
                    'description'  => $l['description'],
                    'qty'          => $l['qty'],
                    'unit_cost'    => $l['unit_cost'],
                    'tax_rate'     => (int) $l['tax_rate'],
                    'line_subtotal'=> $subtotal,
                    'line_tax'     => $tax,
                    'line_total'   => $subtotal + $tax,
                ];
            }, $this->lines);

            $subtotal  = array_sum(array_column($lineData, 'line_subtotal'));
            $taxAmount = array_sum(array_column($lineData, 'line_tax'));

            $headerData = [
                'third_id'                => $this->third_id,
                'supplier_invoice_number' => $this->supplier_invoice_number ?: null,
                'date'                    => $this->date,
                'due_date'                => $this->due_date ?: null,
                'notes'                   => $this->notes ?: null,
                'subtotal'                => $subtotal,
                'tax_amount'              => $taxAmount,
                'total'                   => $subtotal + $taxAmount,
                'status'                  => 'borrador',
            ];

            if ($this->editingId) {
                $invoice = PurchaseInvoice::findOrFail($this->editingId);
                $invoice->update($headerData);
                $invoice->lines()->delete();
            } else {
                $invoice = PurchaseInvoice::create($headerData);
            }

            foreach ($lineData as $ld) {
                $invoice->lines()->create($ld);
            }
        });

        $this->resetInvoiceForm();
        session()->flash('success', 'Factura de compra guardada.');
    }

    public function confirm(int $id, PurchaseService $service): void
    {
        try {
            $invoice = PurchaseInvoice::with('lines.product', 'third')->findOrFail($id);
            $service->confirmInvoice($invoice);
            session()->flash('success', 'Factura confirmada. Asiento de compra generado.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function delete(int $id): void
    {
        $invoice = PurchaseInvoice::findOrFail($id);
        if ($invoice->status->value === 'borrador') {
            $invoice->lines()->delete();
            $invoice->delete();
        }
    }

    private function resetInvoiceForm(): void
    {
        $this->reset(['editingId','third_id','supplier_invoice_number','notes','lines']);
        $this->showForm = false;
        $this->date     = now()->toDateString();
        $this->due_date = now()->addDays(30)->toDateString();
    }

    // ─── Pagos ──────────────────────────────────────────────────────────────

    public function openPayment(int $supplierId): void
    {
        $this->payment_third_id = $supplierId;
        $this->payment_date     = now()->toDateString();
        $this->payment_notes    = '';

        // Cargar facturas pendientes del proveedor
        $pending = PurchaseInvoice::where('third_id', $supplierId)
            ->where('status', 'pendiente')
            ->with('payments')
            ->get();

        $this->payment_items = $pending->map(fn($inv) => [
            'purchase_invoice_id' => $inv->id,
            'invoice_ref'         => ($inv->supplier_invoice_number ?? 'FC-' . str_pad($inv->id, 5, '0', STR_PAD_LEFT)),
            'invoice_balance'     => $inv->balance(),
            'amount_applied'      => $inv->balance(),
        ])->filter(fn($i) => $i['invoice_balance'] > 0)->values()->toArray();

        $this->showPaymentForm = true;
    }

    public function applyPayment(PurchaseService $service): void
    {
        $this->validate([
            'payment_third_id'              => ['required','integer','min:1'],
            'payment_date'                  => ['required','date'],
            'payment_items'                 => ['required','array','min:1'],
            'payment_items.*.amount_applied'=> ['required','numeric','min:0.01'],
        ], ['payment_third_id.min' => 'Selecciona un proveedor.']);

        try {
            $total = array_sum(array_column($this->payment_items, 'amount_applied'));

            $payment = Payment::create([
                'third_id' => $this->payment_third_id,
                'date'     => $this->payment_date,
                'total'    => $total,
                'notes'    => $this->payment_notes ?: null,
                'status'   => 'borrador',
            ]);

            $items = array_map(fn($i) => [
                'purchase_invoice_id' => $i['purchase_invoice_id'],
                'amount_applied'      => $i['amount_applied'],
            ], $this->payment_items);

            $service->applyPayment($payment, $items);

            $this->showPaymentForm = false;
            $this->reset(['payment_third_id','payment_notes','payment_items']);
            session()->flash('success', 'Pago aplicado y asiento generado.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function updatingSearch(): void { $this->resetPage(); }

    public function render(): mixed
    {
        $invoices = PurchaseInvoice::with('third')
            ->when($this->search, fn($q) => $q->whereHas('third', fn($q2) => $q2->where('name', 'ilike', "%{$this->search}%")))
            ->orderByDesc('date')->orderByDesc('id')
            ->paginate(15);

        $suppliers  = Third::whereIn('type', ['proveedor','ambos'])->where('active', true)->orderBy('name')->get();
        $products   = Product::where('active', true)->orderBy('name')->get();
        $statuses   = PurchaseInvoiceStatus::cases();
        $payments   = Payment::with('third')->orderByDesc('date')->orderByDesc('id')->limit(50)->get();

        return view('livewire.tenant.compras.index', compact('invoices','suppliers','products','statuses','payments'))
            ->title('Compras');
    }
}
