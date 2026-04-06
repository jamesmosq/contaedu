<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CiiuSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/ciiu_codes.json');

        if (! file_exists($path)) {
            $this->command->error("Archivo no encontrado: {$path}");
            return;
        }

        $raw = json_decode(file_get_contents($path), true);

        if (! $raw) {
            $this->command->error('El JSON de códigos CIIU está vacío o mal formado.');
            return;
        }

        $now   = now();
        $batch = [];

        foreach ($raw as $item) {
            $codigo = trim($item['codigo'] ?? '');
            if (! $codigo) continue;

            // Los primeros 2 dígitos del código son la división
            $division = substr($codigo, 0, 2);

            $batch[] = [
                'code'       => $codigo,
                'name'       => trim($item['descripcion'] ?? ''),
                'section'    => trim($item['seccion'] ?? ''),
                'division'   => $division,
                'active'     => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insertar en lotes de 100 usando upsert para evitar duplicados
        foreach (array_chunk($batch, 100) as $chunk) {
            DB::table('ciiu_codes')->upsert(
                $chunk,
                ['code'],                           // columna única
                ['name', 'section', 'division', 'active', 'updated_at']
            );
        }

        $this->command->info('✓ '.count($batch).' códigos CIIU cargados correctamente.');
    }
}
