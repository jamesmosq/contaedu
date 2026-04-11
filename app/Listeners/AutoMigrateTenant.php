<?php

namespace App\Listeners;

use App\Models\Tenant\Account;
use App\Models\Tenant\BankTransaction;
use App\Models\Tenant\JournalEntry;
use App\Models\Tenant\JournalLine;
use App\Services\BankService;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Events\TenancyBootstrapped;

class AutoMigrateTenant
{
    /**
     * Ejecuta migraciones pendientes del tenant recién inicializado.
     *
     * Escucha TenancyBootstrapped (no TenancyInitialized) para garantizar
     * que DatabaseTenancyBootstrapper ya configuró la conexión 'tenant'.
     */
    public function handle(TenancyBootstrapped $event): void
    {
        $migrationPath = database_path('migrations/tenant');
        $migrator = app('migrator');
        $migrator->setConnection('tenant');

        try {
            if (! $migrator->repositoryExists()) {
                $migrator->getRepository()->createRepository();
                $migrator->run($migrationPath);
                $this->seedCapitalInicial();

                return;
            }

            $ran = $migrator->getRepository()->getRan();
            $allFiles = array_keys($migrator->getMigrationFiles($migrationPath));
            $pending = array_diff($allFiles, $ran);

            if (! empty($pending)) {
                $migrator->run($migrationPath);
            }
        } finally {
            $migrator->setConnection(config('database.default'));
        }
    }

    /**
     * Registra el asiento de constitución (capital inicial $100M) si no existe.
     * Solo se ejecuta en tenants nuevos (primera vez que corren migraciones).
     */
    private function seedCapitalInicial(): void
    {
        // Guard: no duplicar si ya hay asientos (tenant existente reconfigurado)
        if (JournalEntry::exists()) {
            return;
        }

        $bancos  = Account::where('code', 'like', '1110%')->where('level', '>=', 3)->first();
        $capital = Account::where('code', 'like', '3105%')->where('level', '>=', 3)->first();

        if (! $bancos || ! $capital) {
            return;
        }

        $entry = null;

        DB::transaction(function () use ($bancos, $capital, &$entry) {
            $entry = JournalEntry::create([
                'date'           => now()->toDateString(),
                'reference'      => 'CAP-001',
                'description'    => 'Capital inicial de constitución',
                'document_type'  => 'capital_inicial',
                'auto_generated' => true,
            ]);

            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $bancos->id,
                'debit'            => 100_000_000,
                'credit'           => 0,
                'description'      => 'Capital inicial — Bancos',
            ]);

            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $capital->id,
                'debit'            => 0,
                'credit'           => 100_000_000,
                'description'      => 'Capital inicial — Capital suscrito y pagado',
            ]);
        });

        // Crear cuenta bancaria principal y registrar la consignación inicial
        $cuenta = BankService::crearCuentaPrincipal(100_000_000);

        BankTransaction::create([
            'bank_account_id'   => $cuenta->id,
            'tipo'              => 'consignacion',
            'valor'             => 100_000_000,
            'gmf'               => 0,
            'comision'          => 0,
            'saldo_despues'     => 100_000_000,
            'descripcion'       => 'Capital inicial de constitución',
            'journal_entry_id'  => $entry->id,
            'fecha_transaccion' => now()->toDateString(),
        ]);
    }
}
