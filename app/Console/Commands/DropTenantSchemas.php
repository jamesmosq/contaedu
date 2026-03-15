<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DropTenantSchemas extends Command
{
    protected $signature = 'tenants:drop-schemas';
    protected $description = 'Elimina todos los schemas de tenants de PostgreSQL (usar antes de migrate:fresh)';

    public function handle(): void
    {
        $schemas = DB::select("
            SELECT schema_name
            FROM information_schema.schemata
            WHERE schema_name LIKE 'tenant_%'
        ");

        if (empty($schemas)) {
            $this->info('No hay schemas de tenants para eliminar.');
            return;
        }

        foreach ($schemas as $schema) {
            DB::statement("DROP SCHEMA IF EXISTS \"{$schema->schema_name}\" CASCADE");
            $this->line("  Eliminado: {$schema->schema_name}");
        }

        $this->info('Schemas eliminados correctamente.');
    }
}
