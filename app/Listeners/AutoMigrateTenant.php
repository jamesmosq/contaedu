<?php

namespace App\Listeners;

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
}
