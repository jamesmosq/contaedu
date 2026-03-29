<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use Illuminate\Console\Command;

class TenantsMigrateAll extends Command
{
    protected $signature = 'app:tenants-migrate-all
                            {--tenant= : Migrar solo un tenant específico (por ID/cédula)}';

    protected $description = 'Ejecuta migraciones pendientes en todos los schemas tenant';

    public function handle(): int
    {
        $migrationPath = database_path('migrations/tenant');

        $query = Tenant::query();

        if ($this->option('tenant')) {
            $query->where('id', $this->option('tenant'));
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->warn('No se encontraron tenants.');

            return self::SUCCESS;
        }

        $migrator = app('migrator');

        foreach ($tenants as $tenant) {
            $this->line("→ Tenant: <info>{$tenant->id}</info> ({$tenant->company_name})");

            tenancy()->initialize($tenant);
            $migrator->setConnection('tenant');

            try {
                if (! $migrator->repositoryExists()) {
                    $migrator->getRepository()->createRepository();
                }

                $ran = $migrator->getRepository()->getRan();
                $allFiles = array_keys($migrator->getMigrationFiles($migrationPath));
                $pending = array_diff($allFiles, $ran);

                if (empty($pending)) {
                    $this->line('   <comment>Sin pendientes.</comment>');
                } else {
                    $migrator->run($migrationPath);
                    $this->line('   <info>✓ '.count($pending).' migración(es) aplicada(s).</info>');
                }
            } finally {
                $migrator->setConnection(config('database.default'));
                tenancy()->end();
            }
        }

        $this->newLine();
        $this->info('Listo.');

        return self::SUCCESS;
    }
}
