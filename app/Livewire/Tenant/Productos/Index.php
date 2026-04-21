<?php

namespace App\Livewire\Tenant\Productos;

use App\Enums\ProductUnit;
use App\Enums\TaxRate;
use App\Models\Tenant\Account;
use App\Models\Tenant\Product;
use App\Models\Tenant\StockMovement;
use App\Services\AccountingService;
use App\Services\StockService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.tenant')]
#[Title('Productos')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public bool $showKardex = false;

    public ?int $kardexProductId = null;

    public string $kardexProductName = '';

    // Form
    public string $code = '';

    public string $name = '';

    public string $description = '';

    public string $unit = 'und';

    public string $sale_price = '0';

    public string $cost_price = '0';

    public string $tax_rate = '19';

    public ?int $inventory_account_id = null;

    public ?int $revenue_account_id = null;

    public ?int $cogs_account_id = null;

    public string $initial_stock = '0';

    public string $initial_cost = '0';

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('products', 'code')->ignore($this->editingId)->where('modo', modoContable())],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'unit' => ['required', 'in:und,kg,lt,m,caja,par,otro'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['required', 'in:0,5,19'],
            'inventory_account_id' => ['nullable', 'exists:accounts,id'],
            'revenue_account_id' => ['nullable', 'exists:accounts,id'],
            'cogs_account_id' => ['nullable', 'exists:accounts,id'],
            'initial_stock' => ['nullable', 'numeric', 'min:0'],
            'initial_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'code', 'name', 'description', 'sale_price', 'cost_price', 'inventory_account_id', 'revenue_account_id', 'cogs_account_id', 'initial_stock', 'initial_cost']);
        $this->unit = 'und';
        $this->tax_rate = '19';
        $this->sale_price = '0';
        $this->cost_price = '0';
        $this->initial_stock = '0';
        $this->initial_cost = '0';

        // Preseleccionar cuentas por defecto del PUC
        $this->inventory_account_id = Account::where('code', '1435')->value('id');
        $this->revenue_account_id = Account::where('code', '4135')->value('id');
        $this->cogs_account_id = Account::where('code', '6135')->value('id');

        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->editingId = $id;
        $this->code = $product->code;
        $this->name = $product->name;
        $this->description = $product->description ?? '';
        $this->unit = $product->unit->value;
        $this->sale_price = $product->sale_price;
        $this->cost_price = $product->cost_price;
        $this->tax_rate = (string) $product->tax_rate->value;
        $this->inventory_account_id = $product->inventory_account_id;
        $this->revenue_account_id = $product->revenue_account_id;
        $this->cogs_account_id = $product->cogs_account_id;
        $this->showForm = true;
    }

    public function save(AccountingService $accounting): void
    {
        $this->validate();

        $isNew = ! $this->editingId;

        $product = Product::updateOrCreate(
            ['id' => $this->editingId],
            [
                'modo' => modoContable(),
                'code' => $this->code,
                'name' => $this->name,
                'description' => $this->description ?: null,
                'unit' => $this->unit,
                'sale_price' => $this->sale_price,
                'cost_price' => $this->cost_price,
                'tax_rate' => (int) $this->tax_rate,
                'inventory_account_id' => $this->inventory_account_id,
                'revenue_account_id' => $this->revenue_account_id,
                'cogs_account_id' => $this->cogs_account_id,
                'active' => true,
            ]
        );

        // Registrar stock inicial solo en creación y si la cantidad es mayor a 0
        $qty = (float) $this->initial_stock;
        if ($isNew && $qty > 0 && $product->inventory_account_id) {
            $costo = (float) $this->initial_cost ?: (float) $this->cost_price;
            StockService::registrarEntrada(
                product: $product,
                qty: $qty,
                costoUnitario: $costo,
                referenciaTipo: 'apertura',
                referenciaId: null,
                fecha: now()->toDateString(),
                descripcion: 'Stock inicial — '.$product->name,
            );
            $accounting->generateInitialStockEntry($product, $qty, $costo);
        }

        $label = $isNew ? 'guardado' : 'actualizado';

        $this->reset([
            'showForm', 'editingId', 'code', 'name', 'description',
            'unit', 'sale_price', 'cost_price', 'tax_rate',
            'inventory_account_id', 'revenue_account_id', 'cogs_account_id',
            'initial_stock', 'initial_cost',
        ]);
        $this->resetPage();
        $this->dispatch('notify', type: 'success', message: "Producto {$label} correctamente.");
    }

    public function delete(int $id): void
    {
        abort_if(session('audit_mode') || session('reference_mode'), 403);
        Product::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Producto eliminado.');
    }

    public function cancelForm(): void
    {
        $this->reset([
            'showForm', 'editingId', 'code', 'name', 'description',
            'unit', 'sale_price', 'cost_price', 'tax_rate',
            'inventory_account_id', 'revenue_account_id', 'cogs_account_id',
        ]);
    }

    public function openKardex(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->kardexProductId = $id;
        $this->kardexProductName = $product->name;
        $this->showKardex = true;
    }

    public function closeKardex(): void
    {
        $this->reset(['showKardex', 'kardexProductId', 'kardexProductName']);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): mixed
    {
        $products = Product::modoActual()
            ->when($this->search, fn ($q) => $q
                ->where('name', 'ilike', "%{$this->search}%")
                ->orWhere('code', 'ilike', "%{$this->search}%")
            )
            ->orderBy('name')
            ->paginate(15);

        $accounts = Account::orderBy('code')->get();
        $units = ProductUnit::cases();
        $taxRates = TaxRate::cases();

        $kardexMovements = collect();
        $kardexStock = 0.0;
        if ($this->showKardex && $this->kardexProductId) {
            $kardexMovements = StockMovement::with('third')
                ->where('product_id', $this->kardexProductId)
                ->where('modo', modoContable())
                ->orderByDesc('fecha')
                ->orderByDesc('id')
                ->limit(20)
                ->get();
            $kardexStock = $kardexMovements->first()?->saldo_qty ?? 0.0;
        }

        return view('livewire.tenant.productos.index', compact('products', 'accounts', 'units', 'taxRates', 'kardexMovements', 'kardexStock'))
            ->title('Productos');
    }
}
