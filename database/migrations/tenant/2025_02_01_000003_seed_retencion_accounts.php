<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Siembra las cuentas de retención en el PUC de los tenants existentes.
 * Para tenants nuevos, el PucSeeder ya las incluye desde esta versión.
 */
return new class extends Migration
{
    private array $accounts = [
        [
            'code' => '2365',
            'name' => 'Retención en la fuente a título de renta',
            'type' => 'pasivo',
            'nature' => 'credito',
            'parent_id' => null,
            'level' => 3,
            'active' => true,
        ],
        [
            'code' => '2367',
            'name' => 'Retención impuesto sobre las ventas (Reteiva)',
            'type' => 'pasivo',
            'nature' => 'credito',
            'parent_id' => null,
            'level' => 3,
            'active' => true,
        ],
        [
            'code' => '2368',
            'name' => 'Retención de industria y comercio (Reteica)',
            'type' => 'pasivo',
            'nature' => 'credito',
            'parent_id' => null,
            'level' => 3,
            'active' => true,
        ],
    ];

    public function up(): void
    {
        $now = now();

        foreach ($this->accounts as $account) {
            // Insertar solo si el código no existe (idempotente)
            $exists = DB::table('accounts')->where('code', $account['code'])->exists();

            if (! $exists) {
                DB::table('accounts')->insert(array_merge($account, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }
    }

    public function down(): void
    {
        DB::table('accounts')->whereIn('code', ['2365', '2367', '2368'])->delete();
    }
};
