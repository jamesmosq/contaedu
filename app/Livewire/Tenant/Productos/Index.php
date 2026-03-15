<?php

namespace App\Livewire\Tenant\Productos;

use App\Enums\ProductUnit;
use App\Enums\TaxRate;
use App\Models\Tenant\Account;
use App\Models\Tenant\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showForm = false;
    public ?int $editingId = null;

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

    public function rules(): array
    {
        return [
            'code'                 => ['required', 'string', 'max:20'],
            'name'                 => ['required', 'string', 'max:150'],
            'description'          => ['nullable', 'string'],
            'unit'                 => ['required', 'in:und,kg,lt,m,caja,par,otro'],
            'sale_price'           => ['required', 'numeric', 'min:0'],
            'cost_price'           => ['required', 'numeric', 'min:0'],
            'tax_rate'             => ['required', 'in:0,5,19'],
            'inventory_account_id' => ['nullable', 'exists:accounts,id'],
            'revenue_account_id'   => ['nullable', 'exists:accounts,id'],
            'cogs_account_id'      => ['nullable', 'exists:accounts,id'],
        ];
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'code', 'name', 'description', 'sale_price', 'cost_price', 'inventory_account_id', 'revenue_account_id', 'cogs_account_id']);
        $this->unit = 'und';
        $this->tax_rate = '19';
        $this->sale_price = '0';
        $this->cost_price = '0';

        // Preseleccionar cuentas por defecto del PUC
        $this->inventory_account_id = Account::where('code', '1435')->value('id');
        $this->revenue_account_id   = Account::where('code', '4135')->value('id');
        $this->cogs_account_id      = Account::where('code', '6135')->value('id');

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

    public function save(): void
    {
        $this->validate();

        Product::updateOrCreate(
            ['id' => $this->editingId],
            [
                'code'                 => $this->code,
                'name'                 => $this->name,
                'description'          => $this->description ?: null,
                'unit'                 => $this->unit,
                'sale_price'           => $this->sale_price,
                'cost_price'           => $this->cost_price,
                'tax_rate'             => (int) $this->tax_rate,
                'inventory_account_id' => $this->inventory_account_id,
                'revenue_account_id'   => $this->revenue_account_id,
                'cogs_account_id'      => $this->cogs_account_id,
                'active'               => true,
            ]
        );

        $this->reset(['showForm', 'editingId', 'code', 'name', 'description']);
    }

    public function delete(int $id): void
    {
        Product::findOrFail($id)->delete();
    }

    public function cancelForm(): void
    {
        $this->reset(['showForm', 'editingId', 'code', 'name', 'description']);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): mixed
    {
        $products = Product::query()
            ->when($this->search, fn ($q) => $q
                ->where('name', 'ilike', "%{$this->search}%")
                ->orWhere('code', 'ilike', "%{$this->search}%")
            )
            ->orderBy('name')
            ->paginate(15);

        $accounts = Account::where('level', '>=', 3)->orderBy('code')->get();
        $units = ProductUnit::cases();
        $taxRates = TaxRate::cases();

        return view('livewire.tenant.productos.index', compact('products', 'accounts', 'units', 'taxRates'))
            ->title('Productos');
    }
}
