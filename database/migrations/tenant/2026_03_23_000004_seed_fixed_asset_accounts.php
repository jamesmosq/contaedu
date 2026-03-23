<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Siembra las cuentas PUC que necesitan los activos fijos si no existen.
 * Idempotente: usa updateOrInsert.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $accounts = [
            ['code' => '1516', 'name' => 'Maquinaria y equipo', 'type' => 'activo', 'nature' => 'debito', 'level' => 3],
            ['code' => '1532', 'name' => 'Equipo de transporte', 'type' => 'activo', 'nature' => 'debito', 'level' => 3],
            ['code' => '1592', 'name' => 'Depreciación acumulada (propiedades, planta y equipo)', 'type' => 'activo', 'nature' => 'credito', 'level' => 3],
            ['code' => '5160', 'name' => 'Depreciación propiedades, planta y equipo', 'type' => 'gasto', 'nature' => 'debito', 'level' => 3],
        ];

        foreach ($accounts as $account) {
            DB::table('accounts')->updateOrInsert(
                ['code' => $account['code']],
                array_merge($account, [
                    'parent_id' => null,
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }

    public function down(): void {}
};
