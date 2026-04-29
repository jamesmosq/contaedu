<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Idempotente: seguro de ejecutar aunque _000001 haya fallado o no haya corrido.
// Inserta cuentas correctas, redirige todas las FKs y elimina códigos incorrectos.
return new class extends Migration
{
    public function up(): void
    {
        ini_set('memory_limit', '256M');

        // 1. Insertar catálogo completo de cuentas correctas
        $json = file_get_contents(database_path('data/puc_combined.json'));
        $accounts = json_decode($json, true);
        unset($json);

        $now = now();
        foreach (array_chunk($accounts, 50) as $chunk) {
            DB::table('accounts')->insertOrIgnore(
                array_map(
                    fn ($a) => array_merge($a, ['active' => true, 'parent_id' => null, 'created_at' => $now, 'updated_at' => $now]),
                    $chunk
                )
            );
        }
        unset($accounts);

        // 2. Redirigir FKs: código correcto = substr(código_incorrecto, 1)
        $wrongAccounts = DB::table('accounts')
            ->whereRaw('LENGTH(code) IN (3, 5, 7)')
            ->get(['id', 'code']);

        foreach ($wrongAccounts as $wrong) {
            $correctCode = substr($wrong->code, 1);
            $correctId = DB::table('accounts')->where('code', $correctCode)->value('id');
            if (! $correctId) {
                continue;
            }

            DB::table('journal_lines')->where('account_id', $wrong->id)->update(['account_id' => $correctId]);
            DB::table('bank_reconciliations')->where('account_id', $wrong->id)->update(['account_id' => $correctId]);
        }

        unset($wrongAccounts);

        // 3. Eliminar cuentas con código incorrecto (ya sin referencias)
        DB::table('accounts')
            ->whereRaw('LENGTH(code) IN (3, 5, 7)')
            ->delete();
    }

    public function down(): void {}
};
