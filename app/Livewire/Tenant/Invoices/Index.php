<?php
namespace App\Livewire\Tenant\Invoices;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\Product;
use App\Models\Tenant\Third;
use App\Services\InvoiceService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $filterStatus = '';
    public bool   $showForm     = false;
    public ?int   $editingId    = null;

    // Cabecera de la factura
    public int    $third_id  = 0;
    public string $date      = '';
    public string $due_date  = '';
    public string $series    = 'FV';
    public string $notes     = '';

    // Líneas de la factura (array de arrays)
    public array $lines = [];

    public function mount(): void
    {
        $this->date     = now()->toDateString();
        $this->due_date = now()->addDays(30)->toDateString();
    }

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
        $this->third_id  = $invoice->third_id;
        $this->date      = $invoice->date->toDateString();
        $this->due_date  = $invoice->due_date?->toDateString() ?? '';
        $this->series    = $invoice->series;
        $this->notes     = $invoice->notes ?? '';
        $this->lines     = $invoice->lines->map(fn($l) => [
            'product_id'   => $l->product_id,
            'description'  => $l->description,
            'qty'          => $l->qty,
            'unit_price'   => $l->unit_price,
            'discount_pct' => $l->discount_pct,
            'tax_rate'     => $l->tax_rate,
        ])->toArray();

        $this->showForm = true;
    }

    public function addLine(): void
    {
        $this->lines[] = [
            'product_id'   => null,
            'description'  => '',
            'qty'          => 1,
            'unit_price'   => 0,
            'discount_pct' => 0,
            'tax_rate'     => 19,
        ];
    }

    public function removeLine(int $index): void
    {
        array_splice($this->lines, $index, 1);
        $this->lines = array_values($this->lines);
    }

    public function updatedLines(mixed $value, string $key): void
    {
        // Cuando cambia product_id en una línea, rellenar precio y descripción
        if (str_ends_with($key, '.product_id') && $value) {
            $idx     = (int) explode('.', $key)[0];
            $product = Product::find($value);
            if ($product) {
                $this->lines[$idx]['description'] = $product->name;
                $this->lines[$idx]['unit_price']  = $product->sale_price;
                $this->lines[$idx]['tax_rate']    = $product->tax_rate->value;
            }
        }
    }

    public function save(InvoiceService $service): void
    {
        $this->validate([
            'third_id'              => ['required', 'integer', 'min:1'],
            'date'                  => ['required', 'date'],
            'due_date'              => ['nullable', 'date'],
            'series'                => ['required', 'string', 'max:5'],
            'lines'                 => ['required', 'array', 'min:1'],
            'lines.*.description'   => ['required', 'string'],
            'lines.*.qty'           => ['required', 'numeric', 'min:0.01'],
            'lines.*.unit_price'    => ['required', 'numeric', 'min:0'],
        ], [
            'third_id.min'                         => 'Selecciona un cliente.',
            'lines.min'                            => 'Agrega al menos una línea.',
            'lines.*.description.required'         => 'La descripción es requerida.',
            'lines.*.qty.min'                      => 'La cantidad debe ser mayor a 0.',
        ]);

        $lineData = array_map(function (array $l): array {
            $base     = $l['unit_price'] * $l['qty'];
            $discount = $base * ($l['discount_pct'] / 100);
            $subtotal = round($base - $discount, 2);
            $tax      = round($subtotal * ($l['tax_rate'] / 100), 2);
            return [
                'product_id'    => $l['product_id'] ?: null,
                'description'   => $l['description'],
                'qty'           => $l['qty'],
                'unit_price'    => $l['unit_price'],
                'discount_pct'  => $l['discount_pct'],
                'tax_rate'      => (int) $l['tax_rate'],
                'line_subtotal' => $subtotal,
                'line_tax'      => $tax,
                'line_total'    => $subtotal + $tax,
            ];
        }, $this->lines);

        $headerData = [
            'type'     => InvoiceType::Venta->value,
            'third_id' => $this->third_id,
            'date'     => $this->date,
            'due_date' => $this->due_date ?: null,
            'series'   => $this->series,
            'notes'    => $this->notes ?: null,
            'status'   => InvoiceStatus::Borrador->value,
        ];

        $service->saveDraft($headerData, $lineData, $this->editingId);
        $this->resetForm();
    }

    public function confirm(int $id, InvoiceService $service): void
    {
        try {
            $service->confirm(Invoice::with('lines.product', 'third')->findOrFail($id));
            session()->flash('success', 'Factura confirmada y asiento contable generado.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function annul(int $id, InvoiceService $service): void
    {
        try {
            $service->annul(Invoice::with('lines.product', 'third')->findOrFail($id));
            session()->flash('success', 'Factura anulada. Asiento de reverso generado.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
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
        $this->date     = now()->toDateString();
        $this->due_date = now()->addDays(30)->toDateString();
        $this->series   = 'FV';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): mixed
    {
        $invoices = Invoice::with('third')
            ->when($this->search, fn($q) => $q->whereHas('third', fn($q2) => $q2->where('name', 'ilike', "%{$this->search}%"))
                ->orWhere('series', 'ilike', "%{$this->search}%"))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(15);

        $thirds   = Third::where('type', '!=', 'proveedor')->where('active', true)->orderBy('name')->get();
        $products = Product::where('active', true)->orderBy('name')->get();
        $statuses = InvoiceStatus::cases();

        return view('livewire.tenant.invoices.index', compact('invoices', 'thirds', 'products', 'statuses'))
            ->title('Facturación');
    }
}
