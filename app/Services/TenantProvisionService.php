<?php

namespace App\Services;

use App\Models\Central\Tenant;
use Database\Seeders\TenantDatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantProvisionService
{
    /**
     * Crea una empresa de demostración para un docente.
     * Mismo pipeline que provision() pero sin credenciales de estudiante.
     */
    public function provisionDemo(array $data): Tenant
    {
        $teacherId = $data['teacher_id'];
        $demoId = 'demo_t'.$teacherId.'_'.substr(md5(uniqid('', true)), 0, 6);
        $schemaName = 'tenant_'.$demoId;

        $tenant = Tenant::withoutEvents(function () use ($data, $demoId, $schemaName) {
            return Tenant::create([
                'id' => $demoId,
                'type' => 'demo',
                'teacher_id' => $data['teacher_id'],
                'group_id' => null,
                'student_name' => $data['teacher_name'],
                'company_name' => $data['company_name'],
                'nit_empresa' => $data['nit_empresa'],
                'password' => '',
                'tenancy_db_name' => $schemaName,
                'sector' => $data['sector'],
                'published' => false,
                'active' => true,
            ]);
        });

        DB::statement("CREATE SCHEMA IF NOT EXISTS \"{$schemaName}\"");
        tenancy()->initialize($tenant);

        try {
            $migrator = app('migrator');
            if (! $migrator->repositoryExists()) {
                $migrator->getRepository()->createRepository();
            }
            $migrator->run(database_path('migrations/tenant'));
            app(TenantDatabaseSeeder::class)->run();
        } finally {
            tenancy()->end();
        }

        return $tenant;
    }

    /**
     * Crea un nuevo tenant (empresa estudiantil) y provisiona su schema:
     * CREATE SCHEMA → migrar → sembrar PUC, todo en PHP directo sin overhead de Artisan.
     */
    public function provision(array $data): Tenant
    {
        // 1. Crear el registro en la tabla central (sin disparar el pipeline de stancl,
        //    ya que lo reemplazamos con provisión directa más abajo).
        $tenant = Tenant::withoutEvents(function () use ($data) {
            return Tenant::create([
                'id' => $data['cedula'],
                'student_name' => $data['student_name'],
                'company_name' => $data['company_name'],
                'nit_empresa' => $data['nit_empresa'],
                'group_id' => $data['group_id'],
                'password' => Hash::make($data['password']),
                'tenancy_db_name' => 'tenant_'.$data['cedula'],
                'active' => true,
            ]);
        });

        // 2. Crear schema en PostgreSQL directamente.
        $schemaName = 'tenant_'.$data['cedula'];
        DB::statement(
            "CREATE SCHEMA IF NOT EXISTS \"{$schemaName}\""
        );

        // 3. Inicializar tenancy (cambia search_path al nuevo schema).
        tenancy()->initialize($tenant);

        try {
            // 4. Ejecutar migraciones del tenant directamente (sin Artisan).
            $migrator = app('migrator');

            // Crear tabla migrations si no existe en este schema.
            if (! $migrator->repositoryExists()) {
                $migrator->getRepository()->createRepository();
            }

            $migrator->run(database_path('migrations/tenant'));

            // 5. Sembrar PUC directamente (sin Artisan, un solo INSERT en lote).
            app(TenantDatabaseSeeder::class)->run();
        } finally {
            // 6. Siempre revertir al contexto central.
            tenancy()->end();
        }

        return $tenant;
    }
}
