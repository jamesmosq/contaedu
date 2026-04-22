<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $json = file_get_contents(database_path('data/puc_codes.json'));
        $data = json_decode($json, true);

        $accounts = [];
        foreach ($data['cuentas'] as $clase) {
            $this->walk($clase, '', $accounts);
        }

        $now = now();
        foreach (array_chunk($accounts, 200) as $chunk) {
            $rows = array_map(
                fn ($a) => array_merge($a, ['active' => true, 'parent_id' => null, 'created_at' => $now, 'updated_at' => $now]),
                $chunk
            );
            DB::table('accounts')->insertOrIgnore($rows);
        }
    }

    public function down(): void {}

    private function walk(array $node, string $parentCode, array &$out): void
    {
        $code   = $parentCode . trim($node['codigo']);
        $level  = match (strlen($code)) { 1 => 1, 2 => 2, default => strlen($code) <= 4 ? 3 : 4 };
        $type   = match ($code[0]) {
            '1' => 'activo', '2' => 'pasivo', '3' => 'patrimonio',
            '4' => 'ingreso', '5' => 'gasto', '6', '7' => 'costo', default => 'orden',
        };
        $nature = strtolower(trim($node['tipo'])) === 'credito' ? 'credito' : 'debito';

        $out[] = ['code' => $code, 'name' => trim($node['nombre']), 'type' => $type, 'nature' => $nature, 'level' => $level];

        foreach ($node['hijos'] ?? [] as $hijo) {
            $this->walk($hijo, $code, $out);
        }
    }
};
