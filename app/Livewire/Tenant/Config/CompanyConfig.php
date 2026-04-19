<?php

namespace App\Livewire\Tenant\Config;

use App\Models\Central\CiiuCode;
use App\Models\Tenant\Account;
use App\Models\Tenant\BankAccount;
use App\Models\Tenant\BankTransaction;
use App\Models\Tenant\CompanyConfig as CompanyConfigModel;
use App\Models\Tenant\JournalEntry;
use App\Models\Tenant\JournalLine;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('Configuración')]
class CompanyConfig extends Component
{
    public string $nit = '';

    public string $razon_social = '';

    public string $regimen = 'no_responsable_iva';

    public string $ciiu_code = '';

    public string $ciiu_description = '';

    public string $direccion = '';

    public string $telefono = '';

    public string $email = '';

    public string $prefijo_factura = 'FV';

    public string $resolucion_dian = '';

    public string $sector_empresarial = 'comercial';

    public bool $fe_habilitada = false;

    public bool $isEditing = false;

    // ── Constitución de la empresa ───────────────────────────────────────────
    public array $constitutionSources = [];

    public bool $editingConstitution = false;

    public function mount(): void
    {
        $config = CompanyConfigModel::first();
        if ($config) {
            $this->fill($config->only(['nit', 'razon_social', 'regimen', 'prefijo_factura']));
            $this->ciiu_code = $config->ciiu_code ?? '';
            $this->ciiu_description = $config->ciiu_description ?? '';
            $this->direccion = $config->direccion ?? '';
            $this->telefono = $config->telefono ?? '';
            $this->email = $config->email ?? '';
            $this->resolucion_dian = $config->resolucion_dian ?? '';
            $this->sector_empresarial = $config->sector_empresarial ?? 'comercial';
            $this->fe_habilitada = (bool) ($config->fe_habilitada ?? false);
            $this->isEditing = false;
        } else {
            $tenant = tenancy()->tenant;
            $this->nit = $tenant?->nit_empresa ?? '';
            $this->razon_social = $tenant?->company_name ?? '';
            $this->isEditing = true;
        }

        $this->loadConstitution();
    }

    public function edit(): void
    {
        $this->isEditing = true;
    }

    public function updatedRegimen(string $value): void
    {
        $this->fe_habilitada = ($value === 'responsable_iva');
    }

    public function updatedCiiuCode(string $value): void
    {
        if (! $value) {
            $this->ciiu_description = '';

            return;
        }

        $ciiu = CiiuCode::where('code', $value)->first();
        $this->ciiu_description = $ciiu?->name ?? '';
    }

    public function rules(): array
    {
        return [
            'nit' => ['required', 'string', 'max:20'],
            'razon_social' => ['required', 'string', 'max:150'],
            'regimen' => ['required', 'in:responsable_iva,no_responsable_iva'],
            'ciiu_code' => ['nullable', 'string', 'max:6'],
            'ciiu_description' => ['nullable', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'prefijo_factura' => ['required', 'string', 'max:5'],
            'resolucion_dian' => ['nullable', 'string', 'max:100'],
            'sector_empresarial' => ['required', 'in:industrial,comercial,servicios,avicola,ganadera,otros'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        // La habilitación de F.E. se determina exclusivamente por el régimen
        $feHabilitada = ($this->regimen === 'responsable_iva');

        CompanyConfigModel::updateOrCreate(
            ['id' => CompanyConfigModel::first()?->id],
            [
                'nit' => $this->nit,
                'razon_social' => $this->razon_social,
                'regimen' => $this->regimen,
                'ciiu_code' => $this->ciiu_code ?: null,
                'ciiu_description' => $this->ciiu_description ?: null,
                'direccion' => $this->direccion ?: null,
                'telefono' => $this->telefono ?: null,
                'email' => $this->email ?: null,
                'prefijo_factura' => $this->prefijo_factura,
                'resolucion_dian' => $this->resolucion_dian ?: null,
                'sector_empresarial' => $this->sector_empresarial,
                'fe_habilitada' => $feHabilitada,
            ]
        );

        $this->fe_habilitada = $feHabilitada;
        $this->isEditing = false;
        $this->dispatch('notify', type: 'success', message: 'Configuración guardada correctamente.');
    }

    // ── Constitución ─────────────────────────────────────────────────────────

    public function sourceTypeMap(): array
    {
        return [
            'capital_socios' => ['label' => 'Capital de socios',     'code' => '3105'],
            'prestamo_bancario' => ['label' => 'Préstamo bancario',     'code' => '2105'],
            'prestamo_socio' => ['label' => 'Préstamo de socio',     'code' => '2355'],
            'otro_patrimonio' => ['label' => 'Otro patrimonio',       'code' => '3115'],
            'otro' => ['label' => 'Otra cuenta (manual)',   'code' => null],
        ];
    }

    private function loadConstitution(): void
    {
        $entry = JournalEntry::where('document_type', 'capital_inicial')
            ->with('lines.account')
            ->first();

        if ($entry) {
            $sources = $entry->lines
                ->where('credit', '>', 0)
                ->map(function ($line) {
                    return [
                        'tipo' => $this->detectSourceTipo($line->account),
                        'monto' => (string) intval($line->credit),
                        'account_id' => $line->account_id,
                    ];
                })->values()->all();

            $this->constitutionSources = $sources ?: $this->defaultSources();
        } else {
            $this->constitutionSources = $this->defaultSources();
        }
    }

    private function detectSourceTipo(?Account $account): string
    {
        if (! $account) {
            return 'otro';
        }

        foreach ($this->sourceTypeMap() as $tipo => $config) {
            if ($config['code'] && str_starts_with($account->code, $config['code'])) {
                return $tipo;
            }
        }

        return 'otro';
    }

    private function defaultSources(): array
    {
        $banco = BankAccount::where('activa', true)->where('es_principal', true)->first();
        $capitalAcc = Account::where('code', 'like', '3105%')->where('level', '>=', 3)->first();

        return [[
            'tipo' => 'capital_socios',
            'monto' => (string) intval($banco?->saldo ?? 100_000_000),
            'account_id' => $capitalAcc?->id,
        ]];
    }

    public function addConstitutionSource(): void
    {
        $acc = Account::where('code', 'like', '3105%')->where('level', '>=', 3)->first();
        $this->constitutionSources[] = [
            'tipo' => 'capital_socios',
            'monto' => '0',
            'account_id' => $acc?->id,
        ];
    }

    public function removeConstitutionSource(int $index): void
    {
        array_splice($this->constitutionSources, $index, 1);
        $this->constitutionSources = array_values($this->constitutionSources);
    }

    public function updatedConstitutionSources(mixed $value, string $key): void
    {
        if (str_ends_with($key, '.tipo')) {
            $index = (int) explode('.', $key)[0];
            $tipo = $this->constitutionSources[$index]['tipo'] ?? 'capital_socios';
            $code = $this->sourceTypeMap()[$tipo]['code'] ?? null;

            $this->constitutionSources[$index]['account_id'] = $code
                ? Account::where('code', 'like', $code.'%')->where('level', '>=', 3)->value('id')
                : null;
        }
    }

    public function saveConstitution(): void
    {
        $total = collect($this->constitutionSources)->sum(fn ($s) => (float) ($s['monto'] ?? 0));

        if ($total <= 0) {
            $this->dispatch('notify', type: 'error', message: 'El total de fuentes debe ser mayor a $0.');

            return;
        }

        foreach ($this->constitutionSources as $i => $source) {
            if ((float) ($source['monto'] ?? 0) <= 0) {
                $this->dispatch('notify', type: 'error', message: 'Cada fuente debe tener un monto mayor a $0.');

                return;
            }

            if (empty($source['account_id'])) {
                $this->dispatch('notify', type: 'error', message: 'La fuente '.($i + 1).' no tiene una cuenta PUC asignada.');

                return;
            }
        }

        DB::transaction(function () use ($total) {
            $bancosAcc = Account::where('code', 'like', '1110%')->where('level', '>=', 3)->first();

            if (! $bancosAcc) {
                return;
            }

            $entry = JournalEntry::where('document_type', 'capital_inicial')->first();

            if ($entry) {
                $entry->lines()->delete();
            } else {
                $entry = JournalEntry::create([
                    'date' => now()->toDateString(),
                    'reference' => 'CAP-001',
                    'description' => 'Capital inicial de constitución',
                    'document_type' => 'capital_inicial',
                    'auto_generated' => true,
                ]);
            }

            // DR: total va a Bancos
            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $bancosAcc->id,
                'debit' => $total,
                'credit' => 0,
                'description' => 'Capital inicial — Bancos',
            ]);

            // CR: una línea por fuente
            foreach ($this->constitutionSources as $source) {
                $acc = Account::find($source['account_id']);
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $source['account_id'],
                    'debit' => 0,
                    'credit' => (float) $source['monto'],
                    'description' => 'Capital inicial — '.($acc?->name ?? 'Fuente'),
                ]);
            }

            // Actualizar saldo bancario y transacción inicial
            $banco = BankAccount::where('activa', true)->where('es_principal', true)->first();

            if ($banco) {
                $banco->update(['saldo' => $total]);

                BankTransaction::where('bank_account_id', $banco->id)
                    ->where('tipo', 'consignacion')
                    ->orderBy('id')
                    ->first()
                    ?->update([
                        'valor' => $total,
                        'saldo_despues' => $total,
                        'journal_entry_id' => $entry->id,
                    ]);
            }
        });

        $this->editingConstitution = false;
        $this->loadConstitution();
        $this->dispatch('notify', type: 'success', message: 'Constitución de la empresa actualizada. Asiento CAP-001 regenerado.');
    }

    public function render(): mixed
    {
        $ciiuCodes = CiiuCode::where('active', true)->orderBy('code')->get();

        $bankAccount = BankAccount::where('activa', true)->where('es_principal', true)->first();

        $cuentasPasivoPatrimonio = Account::whereIn('type', ['pasivo', 'patrimonio'])
            ->where('level', '>=', 3)
            ->orderBy('code')
            ->get();

        return view('livewire.tenant.config.company-config', compact(
            'ciiuCodes',
            'bankAccount',
            'cuentasPasivoPatrimonio'
        ))->title('Configuración de empresa');
    }
}
