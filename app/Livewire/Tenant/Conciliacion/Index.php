<?php

namespace App\Livewire\Tenant\Conciliacion;

use App\Models\Tenant\BankReconciliation;
use App\Models\Tenant\BankReconciliationItem;
use App\Services\BankReconciliationService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('Conciliación bancaria')]
class Index extends Component
{
    // ── Formulario nueva conciliación ────────────────────────────────────────
    public bool $showNewForm = false;

    public int $rc_account_id = 0;

    public string $rc_period_from = '';

    public string $rc_period_to = '';

    public float $rc_statement = 0;

    public string $rc_notes = '';

    // ── Conciliación activa ──────────────────────────────────────────────────
    public ?int $activeId = null;

    // ── Partida bancaria (agregar al extracto) ───────────────────────────────
    public bool $showBankItemForm = false;

    public string $bi_date = '';

    public string $bi_description = '';

    public float $bi_debit = 0;

    public float $bi_credit = 0;

    public function mount(): void
    {
        $this->rc_period_from = now()->startOfMonth()->toDateString();
        $this->rc_period_to = now()->endOfMonth()->toDateString();
        $this->bi_date = now()->toDateString();
    }

    public function openNewForm(BankReconciliationService $service): void
    {
        $this->reset(['rc_account_id', 'rc_notes', 'rc_statement']);
        $this->rc_period_from = now()->startOfMonth()->toDateString();
        $this->rc_period_to = now()->endOfMonth()->toDateString();

        // Preseleccionar la primera cuenta bancaria disponible
        $accounts = $service->bankAccounts();
        if ($accounts->isNotEmpty()) {
            $this->rc_account_id = $accounts->first()->id;
        }

        $this->showNewForm = true;
    }

    public function createReconciliation(BankReconciliationService $service): void
    {
        $this->validate([
            'rc_account_id' => ['required', 'integer', 'min:1'],
            'rc_period_from' => ['required', 'date'],
            'rc_period_to' => ['required', 'date', 'after_or_equal:rc_period_from'],
            'rc_statement' => ['required', 'numeric'],
        ]);

        $reconciliation = $service->create([
            'account_id' => $this->rc_account_id,
            'period_start' => $this->rc_period_from,
            'period_end' => $this->rc_period_to,
            'statement_balance' => $this->rc_statement,
            'notes' => $this->rc_notes ?: null,
        ]);

        $this->showNewForm = false;
        $this->activeId = $reconciliation->id;
        $this->dispatch('notify', type: 'success', message: 'Conciliación creada con '.$reconciliation->items->count().' movimientos de libros.');
    }

    public function selectReconciliation(int $id): void
    {
        $this->activeId = $id;
        $this->showBankItemForm = false;
    }

    public function toggleReconciled(int $itemId, BankReconciliationService $service): void
    {
        $item = BankReconciliationItem::findOrFail($itemId);
        $service->toggleReconciled($item);
    }

    public function openBankItemForm(): void
    {
        $this->reset(['bi_description', 'bi_debit', 'bi_credit']);
        $this->bi_date = now()->toDateString();
        $this->showBankItemForm = true;
    }

    public function addBankItem(BankReconciliationService $service): void
    {
        $this->validate([
            'bi_date' => ['required', 'date'],
            'bi_description' => ['required', 'string', 'max:255'],
            'bi_debit' => ['nullable', 'numeric', 'min:0'],
            'bi_credit' => ['nullable', 'numeric', 'min:0'],
        ]);

        $reconciliation = BankReconciliation::findOrFail($this->activeId);

        $service->addBankItem($reconciliation, [
            'date' => $this->bi_date,
            'description' => $this->bi_description,
            'debit' => $this->bi_debit,
            'credit' => $this->bi_credit,
        ]);

        $this->showBankItemForm = false;
        $this->dispatch('notify', type: 'success', message: 'Partida bancaria agregada.');
    }

    public function removeBankItem(int $itemId, BankReconciliationService $service): void
    {
        $item = BankReconciliationItem::findOrFail($itemId);
        $service->removeBankItem($item);
        $this->dispatch('notify', type: 'info', message: 'Partida eliminada.');
    }

    public function finalize(BankReconciliationService $service): void
    {
        $reconciliation = BankReconciliation::with('items')->findOrFail($this->activeId);

        if (! $reconciliation->isBalanced()) {
            $this->dispatch('notify', type: 'error', message: 'La diferencia es $'.number_format(abs($reconciliation->difference()), 0, ',', '.').'. Ajusta las partidas antes de finalizar.');

            return;
        }

        $service->finalize($reconciliation);
        $this->dispatch('notify', type: 'success', message: 'Conciliación finalizada y bloqueada.');
    }

    public function render(BankReconciliationService $service): mixed
    {
        $reconciliations = BankReconciliation::with('account')
            ->orderByDesc('period_end')
            ->get();

        $activeReconciliation = $this->activeId
            ? BankReconciliation::with(['items' => fn ($q) => $q->orderBy('date'), 'account'])->find($this->activeId)
            : null;

        $bankAccounts = $service->bankAccounts();

        return view('livewire.tenant.conciliacion.index', compact(
            'reconciliations',
            'activeReconciliation',
            'bankAccounts',
        ))->title('Conciliación Bancaria');
    }
}
