<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PucSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $json = file_get_contents(database_path('data/puc_combined.json'));
        $accounts = json_decode($json, true);
        unset($json);

        foreach (array_chunk($accounts, 50) as $chunk) {
            DB::table('accounts')->insertOrIgnore(
                array_map(fn ($a) => array_merge($a, ['active' => true, 'parent_id' => null, 'created_at' => $now, 'updated_at' => $now]), $chunk)
            );
        }

        unset($accounts);
    }
}
