<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PucSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = $this->getPucAccounts();

        foreach ($accounts as $account) {
            DB::table('accounts')->insert(array_merge($account, [
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function getPucAccounts(): array
    {
        return [
            // ─── CLASE 1 ACTIVO ───────────────────────────────────────────
            ['code' => '1',    'name' => 'ACTIVO',                               'type' => 'activo',    'nature' => 'debito',  'parent_id' => null, 'level' => 1],
            ['code' => '11',   'name' => 'DISPONIBLE',                           'type' => 'activo',    'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '1105', 'name' => 'Caja',                                 'type' => 'activo',    'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1110', 'name' => 'Bancos',                               'type' => 'activo',    'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '12',   'name' => 'INVERSIONES',                          'type' => 'activo',    'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '1205', 'name' => 'Acciones',                             'type' => 'activo',    'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '13',   'name' => 'DEUDORES',                             'type' => 'activo',    'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '1305', 'name' => 'Clientes',                             'type' => 'activo',    'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1330', 'name' => 'Anticipos y avances',                  'type' => 'activo',    'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '14',   'name' => 'INVENTARIOS',                          'type' => 'activo',    'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '1435', 'name' => 'Mercancías no fabricadas por la empresa','type' => 'activo',  'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '15',   'name' => 'PROPIEDADES PLANTA Y EQUIPO',          'type' => 'activo',    'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '1504', 'name' => 'Terrenos',                             'type' => 'activo',    'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1520', 'name' => 'Construcciones y edificaciones',       'type' => 'activo',    'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1524', 'name' => 'Equipo de oficina',                    'type' => 'activo',    'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1528', 'name' => 'Equipo de cómputo y comunicación',     'type' => 'activo',    'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '16',   'name' => 'INTANGIBLES',                          'type' => 'activo',    'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '1605', 'name' => 'Crédito mercantil',                    'type' => 'activo',    'nature' => 'debito',  'parent_id' => null, 'level' => 3],

            // ─── CLASE 2 PASIVO ───────────────────────────────────────────
            ['code' => '2',    'name' => 'PASIVO',                               'type' => 'pasivo',    'nature' => 'credito', 'parent_id' => null, 'level' => 1],
            ['code' => '21',   'name' => 'OBLIGACIONES FINANCIERAS',             'type' => 'pasivo',    'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '2105', 'name' => 'Bancos nacionales',                    'type' => 'pasivo',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '22',   'name' => 'PROVEEDORES',                          'type' => 'pasivo',    'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '2205', 'name' => 'Nacionales',                           'type' => 'pasivo',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '23',   'name' => 'CUENTAS POR PAGAR',                    'type' => 'pasivo',    'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '2335', 'name' => 'Costos y gastos por pagar',            'type' => 'pasivo',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '24',   'name' => 'IMPUESTOS, GRAVÁMENES Y TASAS',        'type' => 'pasivo',    'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '2408', 'name' => 'IVA por pagar',                        'type' => 'pasivo',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2404', 'name' => 'Retención en la fuente',               'type' => 'pasivo',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ─── CLASE 3 PATRIMONIO ───────────────────────────────────────
            ['code' => '3',    'name' => 'PATRIMONIO',                           'type' => 'patrimonio','nature' => 'credito', 'parent_id' => null, 'level' => 1],
            ['code' => '31',   'name' => 'CAPITAL SOCIAL',                       'type' => 'patrimonio','nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '3105', 'name' => 'Capital suscrito y pagado',            'type' => 'patrimonio','nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '33',   'name' => 'RESERVAS',                             'type' => 'patrimonio','nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '3305', 'name' => 'Reserva legal',                        'type' => 'patrimonio','nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '36',   'name' => 'RESULTADOS DEL EJERCICIO',             'type' => 'patrimonio','nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '3605', 'name' => 'Utilidad del ejercicio',               'type' => 'patrimonio','nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '3610', 'name' => 'Pérdida del ejercicio',                'type' => 'patrimonio','nature' => 'debito',  'parent_id' => null, 'level' => 3],

            // ─── CLASE 4 INGRESOS ─────────────────────────────────────────
            ['code' => '4',    'name' => 'INGRESOS',                             'type' => 'ingreso',   'nature' => 'credito', 'parent_id' => null, 'level' => 1],
            ['code' => '41',   'name' => 'OPERACIONALES',                        'type' => 'ingreso',   'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '4135', 'name' => 'Comercio al por mayor y al por menor', 'type' => 'ingreso',   'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '42',   'name' => 'NO OPERACIONALES',                     'type' => 'ingreso',   'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '4210', 'name' => 'Intereses',                            'type' => 'ingreso',   'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ─── CLASE 5 GASTOS ───────────────────────────────────────────
            ['code' => '5',    'name' => 'GASTOS',                               'type' => 'gasto',     'nature' => 'debito',  'parent_id' => null, 'level' => 1],
            ['code' => '51',   'name' => 'OPERACIONALES DE ADMINISTRACIÓN',      'type' => 'gasto',     'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '5105', 'name' => 'Gastos de personal',                   'type' => 'gasto',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5110', 'name' => 'Honorarios',                           'type' => 'gasto',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5115', 'name' => 'Impuestos',                            'type' => 'gasto',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5120', 'name' => 'Arrendamientos',                       'type' => 'gasto',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5135', 'name' => 'Servicios',                            'type' => 'gasto',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5145', 'name' => 'Mantenimiento y reparaciones',         'type' => 'gasto',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5195', 'name' => 'Diversos',                             'type' => 'gasto',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '52',   'name' => 'OPERACIONALES DE VENTAS',              'type' => 'gasto',     'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '5205', 'name' => 'Gastos de personal ventas',            'type' => 'gasto',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5245', 'name' => 'Mantenimiento y reparaciones ventas',  'type' => 'gasto',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5295', 'name' => 'Diversos ventas',                      'type' => 'gasto',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],

            // ─── CLASE 6 COSTOS DE VENTA ──────────────────────────────────
            ['code' => '6',    'name' => 'COSTOS DE VENTA',                      'type' => 'costo',     'nature' => 'debito',  'parent_id' => null, 'level' => 1],
            ['code' => '61',   'name' => 'COSTO DE VENTAS',                      'type' => 'costo',     'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '6135', 'name' => 'Comercio al por mayor y al por menor', 'type' => 'costo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
        ];
    }
}
