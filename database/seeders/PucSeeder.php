<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PucSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = $this->buildAccounts();
        $now = now();

        $chunks = array_chunk($accounts, 200);
        foreach ($chunks as $chunk) {
            $rows = array_map(
                fn ($a) => array_merge($a, ['active' => true, 'parent_id' => null, 'created_at' => $now, 'updated_at' => $now]),
                $chunk
            );
            DB::table('accounts')->insertOrIgnore($rows);
        }
    }

    private function buildAccounts(): array
    {
        $json = file_get_contents(database_path('data/puc_codes.json'));
        $data = json_decode($json, true);

        $accounts = [];
        foreach ($data['cuentas'] as $clase) {
            $this->walk($clase, '', $accounts);
        }

        return $accounts;
    }

    private function walk(array $node, string $parentCode, array &$out): void
    {
        $code = $parentCode . trim($node['codigo']);
        $level = $this->level($code);
        $type = $this->type($code);
        $nature = strtolower(trim($node['tipo'])) === 'credito' ? 'credito' : 'debito';

        $out[] = [
            'code'   => $code,
            'name'   => trim($node['nombre']),
            'type'   => $type,
            'nature' => $nature,
            'level'  => $level,
        ];

        foreach ($node['hijos'] ?? [] as $hijo) {
            $this->walk($hijo, $code, $out);
        }
    }

    private function level(string $code): int
    {
        return match (strlen($code)) {
            1       => 1,
            2       => 2,
            default => strlen($code) <= 4 ? 3 : 4,
        };
    }

    private function type(string $code): string
    {
        return match ($code[0]) {
            '1'     => 'activo',
            '2'     => 'pasivo',
            '3'     => 'patrimonio',
            '4'     => 'ingreso',
            '5'     => 'gasto',
            '6', '7' => 'costo',
            default => 'orden',
        };
    }
}
