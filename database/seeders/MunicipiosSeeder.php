<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MunicipiosSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/municipios_dian.json');

        if (! file_exists($path)) {
            $this->command?->warn("Archivo no encontrado: {$path}");
            return;
        }

        $municipios = json_decode(file_get_contents($path), true);

        if (empty($municipios)) {
            $this->command?->warn('El JSON de municipios está vacío o mal formado.');
            return;
        }

        // Limpiar e insertar en batches de 100
        DB::table('municipios')->truncate();

        $chunks = array_chunk($municipios, 100);

        foreach ($chunks as $chunk) {
            DB::table('municipios')->insert(array_map(fn ($m) => [
                'codigo'              => $m['codigo'],
                'codigo_departamento' => $m['codigo_departamento'],
                'departamento'        => $m['departamento'],
                'codigo_municipio'    => $m['codigo_municipio'],
                'municipio'           => $m['municipio'],
                'label'               => $m['label'],
            ], $chunk));
        }

        $total = count($municipios);
        $this->command?->info("✓ {$total} municipios cargados.");
    }
}
