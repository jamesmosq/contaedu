<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FeResolucionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('fe_resoluciones')->insert([
            'numero_resolucion' => '18760000001',
            'prefijo' => 'SEDU',
            'numero_desde' => 1,
            'numero_hasta' => 1000,
            'numero_actual' => 1,
            'fecha_desde' => '2024-01-01',
            'fecha_hasta' => '2026-12-31',
            'clave_tecnica' => Str::uuid()->toString(),
            'ambiente' => '02',
            'activa' => true,
            'notas' => 'Resolución educativa simulada — ContaEdu',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
