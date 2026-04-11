<?php

namespace App\Livewire\Tenant\Compras;

use App\Enums\ConceptoRetencion;
use App\Enums\PurchaseInvoiceStatus;
use App\Enums\PurchaseOrderStatus;
use App\Models\Tenant\BankAccount;
use App\Models\Tenant\BankTransaction;
use App\Models\Tenant\Payment;
use App\Models\Tenant\Product;
use App\Models\Tenant\PurchaseInvoice;
use App\Models\Tenant\PurchaseOrder;
use App\Models\Tenant\Third;
use App\Services\PurchaseService;
use App\Services\RetencionService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.tenant')]
#[Title('Compras')]
class Index extends Component
{
    use WithPagination;

    // Vista activa: 'orders' | 'invoices' | 'payments'
    public string $view = 'orders';

    public string $search = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    // Cabecera factura compra
    public int $third_id = 0;

    public string $supplier_invoice_number = '';

    public string $date = '';

    public string $due_date = '';

    public string $notes = '';

    public array $lines = [];

    // ─── Órdenes de compra ──────────────────────────────────────────────────
    public bool $showOrderForm = false;

    public ?int $editingOrderId = null;

    public int $order_third_id = 0;

    public string $order_date = '';

    public string $order_expected_date = '';

    public string $order_notes = '';

    public array $order_lines = [];

    // ─── Retenciones (formulario de confirmación) ───────────────────────────

    /** Valor del enum ConceptoRetencion (string) o '' para ninguno */
    public string $retencion_concepto = '';

    public bool $aplicar_reteiva = false;

    /** Porcentaje de Reteica a aplicar (0 = no aplica) */
    public float $reteica_porcentaje = 0.0;

    /** Resumen calculado de retenciones (se llena al abrir el modal de confirmar) */
    public array $retencionesSummary = [];

    public bool $showConfirmModal = false;

    public ?int $confirmingInvoiceId = null;

    // ─── Pago ────────────────────────────────────────────────────────────────
    public bool $showPaymentForm = false;
    public ?int $payment_bank_account_id = null; // null = pago desde caja

    public int $payment_third_id = 0;

    public string $payment_date = '';

    public string $payment_notes = '';

    public array $payment_items = []; // [{purchase_invoice_id, amount_applied, invoice_ref, invoice_balance}]

    public function mount(): void
    {
        $this->date = now()->toDateString();
        $this->due_date = now()->addDays(30)->toDateString();
        $this->payment_date = now()->toDateString();
        $this->order_date = now()->toDateString();
        $this->order_expected_date = now()->addDays(15)->toDateString();
    }

    // ─── Órdenes de compra ──────────────────────────────────────────────────

    public function openCreateOrder(): void
    {
        $this->resetOrderForm();
        $this->showOrderForm = true;
    }

    public function openEditOrder(int $id): void
    {
        $order = PurchaseOrder::with('lines')->findOrFail($id);
        if ($order->status !== PurchaseOrderStatus::Pendiente) {
            return;
        }

        $this->editingOrderId = $id;
        $this->order_third_id = $order->third_id;
        $this->order_date = $order->date->toDateString();
        $this->order_expected_date = $order->expected_date?->toDateString() ?? '';
        $this->order_notes = $order->notes ?? '';
        $this->order_lines = $order->lines->map(fn ($l) => [
            'product_id' => $l->product_id,
            'description' => $l->description,
            'qty' => $l->qty,
            'unit_cost' => $l->unit_cost,
        ])->toArray();

        $this->showOrderForm = true;
    }

    public function addOrderLine(): void
    {
        $this->order_lines[] = ['product_id' => null, 'description' => '', 'qty' => 1, 'unit_cost' => 0];
    }

    public function removeOrderLine(int $index): void
    {
        array_splice($this->order_lines, $index, 1);
        $this->order_lines = array_values($this->order_lines);
    }

    public function updatedOrderLines(mixed $value, string $key): void
    {
        if (str_ends_with($key, '.product_id') && $value) {
            $idx = (int) explode('.', $key)[0];
            $product = Product::find($value);
            if ($product) {
                $this->order_lines[$idx]['description'] = $product->name;
                $this->order_lines[$idx]['unit_cost'] = $product->cost_price;
            }
        }
    }

    public function saveOrder(): void
    {
        $this->validate([
            'order_third_id' => ['required', 'integer', 'min:1'],
            'order_date' => ['required', 'date'],
            'order_expected_date' => ['nullable', 'date'],
            'order_lines' => ['required', 'array', 'min:1'],
            'order_lines.*.description' => ['required', 'string'],
            'order_lines.*.qty' => ['required', 'numeric', 'min:0.01'],
            'order_lines.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ], [
            'order_third_id.min' => 'Selecciona un proveedor.',
            'order_lines.min' => 'Agrega al menos una línea.',
        ]);

        DB::transaction(function () {
            $lineData = array_map(function (array $l): array {
                return [
                    'product_id' => $l['product_id'] ?: null,
                    'description' => $l['description'],
                    'qty' => $l['qty'],
                    'unit_cost' => $l['unit_cost'],
                    'line_total' => round($l['unit_cost'] * $l['qty'], 2),
                ];
            }, $this->order_lines);

            $total = array_sum(array_column($lineData, 'line_total'));

            $headerData = [
                'third_id' => $this->order_third_id,
                'date' => $this->order_date,
                'expected_date' => $this->order_expected_date ?: null,
                'notes' => $this->order_notes ?: null,
                'total' => $total,
                'status' => PurchaseOrderStatus::Pendiente->value,
            ];

            if ($this->editingOrderId) {
                $order = PurchaseOrder::findOrFail($this->editingOrderId);
                $order->update($headerData);
                $order->lines()->delete();
            } else {
                $order = PurchaseOrder::create($headerData);
            }

            foreach ($lineData as $ld) {
                $order->lines()->create($ld);
            }
        });

        $this->resetOrderForm();
        $this->dispatch('notify', type: 'success', message: 'Orden de compra guardada.');
    }

    public function receiveOrder(int $id): void
    {
        $order = PurchaseOrder::with('lines.product', 'third')->findOrFail($id);

        if ($order->status !== PurchaseOrderStatus::Pendiente) {
            $this->dispatch('notify', type: 'error', message: 'Solo se pueden recibir órdenes pendientes.');

            return;
        }

        DB::transaction(function () use ($order) {
            $order->update(['status' => PurchaseOrderStatus::Recibida->value]);

            // Crear factura de compra en borrador a partir de la orden
            $subtotal = 0.0;
            $tax = 0.0;

            $invoice = PurchaseInvoice::create([
                'third_id' => $order->third_id,
                'purchase_order_id' => $order->id,
                'supplier_invoice_number' => null,
                'date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'status' => 'borrador',
                'subtotal' => 0,
                'tax_amount' => 0,
                'total' => 0,
            ]);

            foreach ($order->lines as $ol) {
                $lineSub = round($ol->unit_cost * $ol->qty, 2);
                $lineTax = 0.0; // Las órdenes no tienen IVA directo; se ajusta en la factura
                $invoice->lines()->create([
                    'product_id' => $ol->product_id,
                    'description' => $ol->description,
                    'qty' => $ol->qty,
                    'unit_cost' => $ol->unit_cost,
                    'tax_rate' => 0,
                    'line_subtotal' => $lineSub,
                    'line_tax' => $lineTax,
                    'line_total' => $lineSub,
                ]);
                $subtotal += $lineSub;
            }

            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'total' => $subtotal + $tax,
            ]);
        });

        $this->view = 'invoices';
        $this->dispatch('notify', type: 'success', message: 'Mercancía recibida. Se creó una factura de compra en borrador para ajustar IVA y confirmar.');
    }

    public function cancelOrder(int $id): void
    {
        $order = PurchaseOrder::findOrFail($id);
        if ($order->status === PurchaseOrderStatus::Pendiente) {
            $order->update(['status' => PurchaseOrderStatus::Cancelada->value]);
        }
    }

    private function resetOrderForm(): void
    {
        $this->reset(['editingOrderId', 'order_third_id', 'order_notes', 'order_lines']);
        $this->showOrderForm = false;
        $this->order_date = now()->toDateString();
        $this->order_expected_date = now()->addDays(15)->toDateString();
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
        $this->editingId = $id;
        $this->third_id = $invoice->third_id;
        $this->supplier_invoice_number = $invoice->supplier_invoice_number ?? '';
        $this->date = $invoice->date->toDateString();
        $this->due_date = $invoice->due_date?->toDateString() ?? '';
        $this->notes = $invoice->notes ?? '';
        $this->lines = $invoice->lines->map(fn ($l) => [
            'product_id' => $l->product_id,
            'description' => $l->description,
            'qty' => $l->qty,
            'unit_cost' => $l->unit_cost,
            'tax_rate' => $l->tax_rate,
        ])->toArray();
        $this->showForm = true;
    }

    public function addLine(): void
    {
        $this->lines[] = ['product_id' => null, 'description' => '', 'qty' => 1, 'unit_cost' => 0, 'tax_rate' => 19];
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
                $this->lines[$idx]['unit_cost'] = $product->cost_price;
                $this->lines[$idx]['tax_rate'] = $product->tax_rate->value;
            }
        }
    }

    public function save(PurchaseService $service): void
    {
        $this->validate([
            'third_id' => ['required', 'integer', 'min:1'],
            'date' => ['required', 'date'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string'],
            'lines.*.qty' => ['required', 'numeric', 'min:0.01'],
            'lines.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ], [
            'third_id.min' => 'Selecciona un proveedor.',
            'lines.min' => 'Agrega al menos una línea.',
        ]);

        DB::transaction(function () {
            $lineData = array_map(function (array $l): array {
                $subtotal = round($l['unit_cost'] * $l['qty'], 2);
                $tax = round($subtotal * ($l['tax_rate'] / 100), 2);

                return [
                    'product_id' => $l['product_id'] ?: null,
                    'description' => $l['description'],
                    'qty' => $l['qty'],
                    'unit_cost' => $l['unit_cost'],
                    'tax_rate' => (int) $l['tax_rate'],
                    'line_subtotal' => $subtotal,
                    'line_tax' => $tax,
                    'line_total' => $subtotal + $tax,
                ];
            }, $this->lines);

            $subtotal = array_sum(array_column($lineData, 'line_subtotal'));
            $taxAmount = array_sum(array_column($lineData, 'line_tax'));

            $headerData = [
                'third_id' => $this->third_id,
                'supplier_invoice_number' => $this->supplier_invoice_number ?: null,
                'date' => $this->date,
                'due_date' => $this->due_date ?: null,
                'notes' => $this->notes ?: null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount,
                'status' => 'borrador',
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
        $this->dispatch('notify', type: 'success', message: 'Factura de compra guardada.');
    }

    /**
     * Abre el modal de confirmación para una factura de compra.
     * Pre-carga el tercero para mostrar su régimen en el formulario de retenciones.
     */
    public function openConfirm(int $id): void
    {
        $this->confirmingInvoiceId = $id;
        $this->retencion_concepto = '';
        $this->aplicar_reteiva = false;
        $this->reteica_porcentaje = 0.0;
        $this->retencionesSummary = [];
        $this->showConfirmModal = true;
    }

    /**
     * Recalcula el resumen de retenciones en tiempo real cuando el usuario
     * modifica los campos del modal de confirmación.
     */
    public function calcularRetenciones(RetencionService $service): void
    {
        if (! $this->confirmingInvoiceId) {
            return;
        }

        $invoice = PurchaseInvoice::with('third')->findOrFail($this->confirmingInvoiceId);

        $concepto = $this->retencion_concepto !== ''
            ? ConceptoRetencion::from($this->retencion_concepto)
            : null;

        $this->retencionesSummary = $service->calcular(
            $invoice,
            $concepto,
            $this->aplicar_reteiva,
            (float) $this->reteica_porcentaje,
        );
    }

    /**
     * Confirma la factura aplicando las retenciones calculadas.
     */
    public function confirmarConRetenciones(PurchaseService $purchaseService, RetencionService $retencionService): void
    {
        try {
            $invoice = PurchaseInvoice::with('lines.product', 'third')->findOrFail($this->confirmingInvoiceId);

            $concepto = $this->retencion_concepto !== ''
                ? ConceptoRetencion::from($this->retencion_concepto)
                : null;

            $retenciones = $retencionService->calcular(
                $invoice,
                $concepto,
                $this->aplicar_reteiva,
                (float) $this->reteica_porcentaje,
            );

            // Solo pasar retenciones si hay algo que retener
            $purchaseService->confirmInvoice(
                $invoice,
                $retenciones['total_retenciones'] > 0 ? $retenciones : null,
            );

            $this->showConfirmModal = false;
            $this->confirmingInvoiceId = null;
            $this->retencionesSummary = [];
            $this->dispatch('notify', type: 'success', message: 'Factura confirmada. Asiento de compra generado.');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
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
        $this->reset(['editingId', 'third_id', 'supplier_invoice_number', 'notes', 'lines']);
        $this->showForm = false;
        $this->date = now()->toDateString();
        $this->due_date = now()->addDays(30)->toDateString();
    }

    // ─── Pagos ──────────────────────────────────────────────────────────────

    public function openPayment(int $supplierId): void
    {
        $this->payment_third_id = $supplierId;
        $this->payment_date = now()->toDateString();
        $this->payment_notes = '';

        // Cargar facturas pendientes del proveedor
        $pending = PurchaseInvoice::where('third_id', $supplierId)
            ->where('status', 'pendiente')
            ->with('payments')
            ->get();

        $this->payment_items = $pending->map(fn ($inv) => [
            'purchase_invoice_id' => $inv->id,
            'invoice_ref' => ($inv->supplier_invoice_number ?? 'FC-'.str_pad($inv->id, 5, '0', STR_PAD_LEFT)),
            'invoice_balance' => $inv->balance(),
            'amount_applied' => $inv->balance(),
        ])->filter(fn ($i) => $i['invoice_balance'] > 0)->values()->toArray();

        $this->showPaymentForm = true;
    }

    public function applyPayment(PurchaseService $service): void
    {
        $this->validate([
            'payment_third_id' => ['required', 'integer', 'min:1'],
            'payment_date' => ['required', 'date'],
            'payment_items' => ['required', 'array', 'min:1'],
            'payment_items.*.amount_applied' => ['required', 'numeric', 'min:0.01'],
        ], ['payment_third_id.min' => 'Selecciona un proveedor.']);

        try {
            $total = array_sum(array_column($this->payment_items, 'amount_applied'));

            // Validar saldo si paga desde banco
            if ($this->payment_bank_account_id) {
                $cuentaBanco = BankAccount::find($this->payment_bank_account_id);
                if ($cuentaBanco) {
                    $gmf = \App\Services\BankService::calcularGmf('pago_proveedor', $total);
                    if ($cuentaBanco->saldoDisponible() < ($total + $gmf)) {
                        $this->dispatch('notify', type: 'error',
                            message: 'Saldo insuficiente en ' . $cuentaBanco->nombreBanco() . '. Disponible: $' . number_format($cuentaBanco->saldoDisponible(), 0, ',', '.'));
                        return;
                    }
                }
            }

            $payment = Payment::create([
                'third_id'        => $this->payment_third_id,
                'date'            => $this->payment_date,
                'total'           => $total,
                'notes'           => $this->payment_notes ?: null,
                'status'          => 'borrador',
                'bank_account_id' => $this->payment_bank_account_id,
            ]);

            $items = array_map(fn ($i) => [
                'purchase_invoice_id' => $i['purchase_invoice_id'],
                'amount_applied' => $i['amount_applied'],
            ], $this->payment_items);

            [$payment, $entry] = $service->applyPayment($payment, $items);

            // Registrar transacción bancaria si se pagó desde banco
            if ($this->payment_bank_account_id && $cuentaBanco = BankAccount::find($this->payment_bank_account_id)) {
                $gmf = \App\Services\BankService::calcularGmf('pago_proveedor', $total);
                $totalCargo = $total + $gmf;
                $cuentaBanco->decrement('saldo', $totalCargo);

                BankTransaction::create([
                    'bank_account_id'   => $cuentaBanco->id,
                    'tipo'              => 'pago_proveedor',
                    'valor'             => $total,
                    'gmf'               => $gmf,
                    'comision'          => 0,
                    'saldo_despues'     => $cuentaBanco->fresh()->saldo,
                    'descripcion'       => 'Pago proveedor PAG-' . str_pad($payment->id, 5, '0', STR_PAD_LEFT),
                    'journal_entry_id'  => $entry?->id,
                    'purchase_invoice_id' => $this->payment_items[0]['purchase_invoice_id'] ?? null,
                    'fecha_transaccion' => $this->payment_date,
                ]);
            }

            $this->showPaymentForm = false;
            $this->reset(['payment_third_id', 'payment_notes', 'payment_items', 'payment_bank_account_id']);
            $this->dispatch('notify', type: 'success', message: 'Pago aplicado y asiento generado.');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): mixed
    {
        $invoices = PurchaseInvoice::with('third')
            ->when($this->search, fn ($q) => $q->whereHas('third', fn ($q2) => $q2->where('name', 'ilike', "%{$this->search}%")))
            ->orderByDesc('date')->orderByDesc('id')
            ->paginate(15);

        $orders = PurchaseOrder::with('third')
            ->orderByDesc('date')->orderByDesc('id')
            ->paginate(15);

        $suppliers = Third::where('type', 'proveedor')->where('active', true)->orderBy('name')->get();
        $products = Product::where('active', true)->orderBy('name')->get();
        $statuses = PurchaseInvoiceStatus::cases();
        $payments = Payment::with('third')->orderByDesc('date')->orderByDesc('id')->limit(50)->get();

        $conceptosRetencion = ConceptoRetencion::cases();

        return view('livewire.tenant.compras.index', compact(
            'invoices', 'orders', 'suppliers', 'products', 'statuses', 'payments', 'conceptosRetencion'
        ))->title('Compras');
    }
}
