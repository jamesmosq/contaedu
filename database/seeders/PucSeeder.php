<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PucSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $accounts = array_map(
            fn ($a) => array_merge($a, ['active' => true, 'created_at' => $now, 'updated_at' => $now]),
            $this->getPucAccounts()
        );

        // insertOrIgnore para que las cuentas ya sembradas por migraciones
        // (seed_retencion_accounts, seed_fixed_asset_accounts) no rompan en tenants nuevos.
        DB::table('accounts')->insertOrIgnore($accounts);
    }

    private function getPucAccounts(): array
    {
        return [

            // ══════════════════════════════════════════════════════════════
            // CLASE 1 · ACTIVO
            // ══════════════════════════════════════════════════════════════
            ['code' => '1',      'name' => 'ACTIVO',                                          'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 1],

            // ─── 11 Disponible ────────────────────────────────────────────
            ['code' => '11',     'name' => 'DISPONIBLE',                                      'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '1105',   'name' => 'Caja',                                            'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '110505', 'name' => 'Caja general',                                    'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '110510', 'name' => 'Cajas menores',                                   'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '1110',   'name' => 'Bancos',                                          'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '111005', 'name' => 'Moneda nacional',                                 'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '111010', 'name' => 'Moneda extranjera',                               'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '1115',   'name' => 'Remesas en tránsito',                             'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '111505', 'name' => 'Moneda nacional',                                 'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '111510', 'name' => 'Moneda extranjera',                               'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],

            // ─── 12 Inversiones ───────────────────────────────────────────
            ['code' => '12',     'name' => 'INVERSIONES',                                     'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '1205',   'name' => 'Acciones',                                        'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '120505', 'name' => 'Acciones ordinarias',                             'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '1210',   'name' => 'Cuotas o partes de interés social',               'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1215',   'name' => 'Bonos',                                           'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1255',   'name' => 'Inversiones en CDT',                              'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1298',   'name' => 'Provisión para protección de inversiones (Cr)',   'type' => 'activo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ─── 13 Deudores ──────────────────────────────────────────────
            ['code' => '13',     'name' => 'DEUDORES',                                        'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '1305',   'name' => 'Clientes',                                        'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '130505', 'name' => 'Nacionales',                                      'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '130510', 'name' => 'Del exterior',                                    'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '1310',   'name' => 'Cuentas corrientes comerciales',                  'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1320',   'name' => 'Deudores varios',                                 'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1325',   'name' => 'Cuentas por cobrar a socios y accionistas',       'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1330',   'name' => 'Anticipos y avances',                             'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '133005', 'name' => 'A proveedores',                                   'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '133010', 'name' => 'A contratistas',                                  'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '133015', 'name' => 'A empleados',                                     'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '1335',   'name' => 'Depósitos',                                       'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1340',   'name' => 'Promesas de compraventa',                         'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1345',   'name' => 'Ingresos por cobrar',                             'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1355',   'name' => 'Anticipo de impuestos y contribuciones',          'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '135505', 'name' => 'Anticipo renta y complementarios',                'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '135510', 'name' => 'Retención en la fuente practicada',               'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '135515', 'name' => 'Retención IVA practicada',                        'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '135520', 'name' => 'Retención ICA practicada',                        'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '1360',   'name' => 'Reclamaciones (seguros)',                         'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1365',   'name' => 'Cuentas por cobrar a trabajadores',               'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1370',   'name' => 'Préstamos a particulares',                        'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1380',   'name' => 'Deudores de difícil cobro',                       'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1399',   'name' => 'Provisiones (deudores) (Cr)',                     'type' => 'activo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ─── 14 Inventarios ───────────────────────────────────────────
            ['code' => '14',     'name' => 'INVENTARIOS',                                     'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '1405',   'name' => 'Materias primas',                                 'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1410',   'name' => 'Productos en proceso',                            'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1415',   'name' => 'Obras de construcción en curso',                  'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1420',   'name' => 'Obras de urbanismo',                              'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1425',   'name' => 'Contratos en ejecución',                          'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1430',   'name' => 'Productos terminados',                            'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1435',   'name' => 'Mercancías no fabricadas por la empresa',         'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '143505', 'name' => 'En almacén',                                      'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '143510', 'name' => 'En tránsito',                                     'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '1440',   'name' => 'Terrenos',                                        'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1445',   'name' => 'Semovientes',                                     'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1455',   'name' => 'Materiales, repuestos y accesorios',              'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1460',   'name' => 'Envases y empaques',                              'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1465',   'name' => 'Inventarios en tránsito',                         'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1499',   'name' => 'Provisiones (inventarios) (Cr)',                  'type' => 'activo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ─── 15 Propiedades, planta y equipo ──────────────────────────
            ['code' => '15',     'name' => 'PROPIEDADES, PLANTA Y EQUIPO',                    'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '1504',   'name' => 'Terrenos',                                        'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '150405', 'name' => 'Urbanos',                                         'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '150410', 'name' => 'Rurales',                                         'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '1508',   'name' => 'Construcciones en curso',                         'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1516',   'name' => 'Construcciones y edificaciones',                  'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1520',   'name' => 'Maquinaria y equipo',                             'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1524',   'name' => 'Equipo de oficina',                               'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1528',   'name' => 'Equipo de cómputo y comunicación',                'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1532',   'name' => 'Equipo médico científico',                        'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1536',   'name' => 'Equipo de hoteles y restaurantes',                'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1540',   'name' => 'Flota y equipo de transporte',                    'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1548',   'name' => 'Flota y equipo aéreo',                            'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1560',   'name' => 'Acueducto, planta y redes',                       'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1564',   'name' => 'Plantas, ductos y túneles',                       'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1568',   'name' => 'Redes, líneas y cables',                          'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1572',   'name' => 'Armamento',                                       'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1576',   'name' => 'Semovientes',                                     'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1580',   'name' => 'Plantaciones agrícolas y forestales',             'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1592',   'name' => 'Depreciación acumulada (Cr)',                      'type' => 'activo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '159205', 'name' => 'Construcciones y edificaciones',                  'type' => 'activo',     'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '159210', 'name' => 'Maquinaria y equipo',                             'type' => 'activo',     'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '159215', 'name' => 'Equipo de oficina',                               'type' => 'activo',     'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '159220', 'name' => 'Equipo de cómputo y comunicación',                'type' => 'activo',     'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '159225', 'name' => 'Flota y equipo de transporte',                    'type' => 'activo',     'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '1599',   'name' => 'Provisión propiedades, planta y equipo (Cr)',     'type' => 'activo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ─── 16 Intangibles ───────────────────────────────────────────
            ['code' => '16',     'name' => 'INTANGIBLES',                                     'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '1605',   'name' => 'Crédito mercantil',                               'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1610',   'name' => 'Marcas',                                          'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1615',   'name' => 'Patentes',                                        'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1620',   'name' => 'Concesiones y franquicias',                       'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1625',   'name' => 'Derechos',                                        'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1628',   'name' => 'Licencias',                                       'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1635',   'name' => 'Know-how',                                        'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1698',   'name' => 'Amortización acumulada intangibles (Cr)',          'type' => 'activo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ─── 17 Diferidos ─────────────────────────────────────────────
            ['code' => '17',     'name' => 'DIFERIDOS',                                       'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '1705',   'name' => 'Gastos pagados por anticipado',                   'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '170505', 'name' => 'Intereses',                                       'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '170510', 'name' => 'Seguros',                                         'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '170515', 'name' => 'Arrendamientos',                                  'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '1710',   'name' => 'Cargos diferidos',                                'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '171005', 'name' => 'Costos de exploración',                           'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '171010', 'name' => 'Programas para computador (software)',             'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '171015', 'name' => 'Remodelaciones',                                  'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '1798',   'name' => 'Amortización acumulada diferidos (Cr)',            'type' => 'activo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ─── 18 Otros activos ─────────────────────────────────────────
            ['code' => '18',     'name' => 'OTROS ACTIVOS',                                   'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '1805',   'name' => 'Bienes de arte y cultura',                        'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1810',   'name' => 'Bienes recibidos en dación de pago',              'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1815',   'name' => 'Depósitos',                                       'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '1820',   'name' => 'Títulos judiciales',                              'type' => 'activo',     'nature' => 'debito',  'parent_id' => null, 'level' => 3],

            // ══════════════════════════════════════════════════════════════
            // CLASE 2 · PASIVO
            // ══════════════════════════════════════════════════════════════
            ['code' => '2',      'name' => 'PASIVO',                                          'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 1],

            // ─── 21 Obligaciones financieras ──────────────────────────────
            ['code' => '21',     'name' => 'OBLIGACIONES FINANCIERAS',                        'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '2105',   'name' => 'Bancos nacionales',                               'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2110',   'name' => 'Bancos del exterior',                             'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2115',   'name' => 'Corporaciones financieras',                       'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2120',   'name' => 'Compañías de financiamiento',                     'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2135',   'name' => 'Leasing financiero',                              'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2145',   'name' => 'Crédito rotativo',                                'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2195',   'name' => 'Otros',                                           'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ─── 22 Proveedores ───────────────────────────────────────────
            ['code' => '22',     'name' => 'PROVEEDORES',                                     'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '2205',   'name' => 'Nacionales',                                      'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2210',   'name' => 'Del exterior',                                    'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ─── 23 Cuentas por pagar ─────────────────────────────────────
            ['code' => '23',     'name' => 'CUENTAS POR PAGAR',                               'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '2305',   'name' => 'Cuentas corrientes comerciales',                  'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2315',   'name' => 'A casa matriz',                                   'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2320',   'name' => 'A vinculados económicos',                         'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2325',   'name' => 'A socios y accionistas',                          'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2330',   'name' => 'Dividendos y participaciones por pagar',          'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2335',   'name' => 'Costos y gastos por pagar',                       'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2340',   'name' => 'Instalamentos por pagar',                         'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2345',   'name' => 'Acreedores oficiales',                            'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2350',   'name' => 'Regalías por pagar',                              'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2355',   'name' => 'Deudas con accionistas y socios',                 'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2360',   'name' => 'Importaciones por pagar',                         'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2365',   'name' => 'Retención en la fuente',                          'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '236505', 'name' => 'Por sueldos y salarios',                          'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '236510', 'name' => 'Por honorarios',                                  'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '236515', 'name' => 'Por servicios',                                   'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '236520', 'name' => 'Por arrendamientos',                              'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '236525', 'name' => 'Por compras',                                     'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '2367',   'name' => 'Retención de IVA (Reteiva)',                      'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2368',   'name' => 'Retención de industria y comercio (Reteica)',     'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2370',   'name' => 'Retenciones y aportes de nómina',                 'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '237005', 'name' => 'Aportes a EPS (salud)',                           'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '237010', 'name' => 'Aportes a fondos de pensiones',                   'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '237015', 'name' => 'Aportes ARL',                                     'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '237020', 'name' => 'Aportes SENA',                                    'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '237025', 'name' => 'Aportes ICBF',                                    'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '237030', 'name' => 'Aportes caja de compensación familiar',           'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '2380',   'name' => 'Acreedores varios',                               'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ─── 24 Impuestos, gravámenes y tasas ─────────────────────────
            ['code' => '24',     'name' => 'IMPUESTOS, GRAVÁMENES Y TASAS',                   'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '2404',   'name' => 'Impuesto de renta y complementarios',             'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2408',   'name' => 'IVA por pagar',                                   'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '240805', 'name' => 'IVA generado',                                    'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '240810', 'name' => 'IVA descontable (Db)',                            'type' => 'pasivo',     'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '2412',   'name' => 'Impuesto de industria y comercio',                'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2416',   'name' => 'Impuesto predial unificado',                      'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2420',   'name' => 'Impuesto sobre vehículos automotores',            'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2424',   'name' => 'Impuesto de timbre nacional',                     'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2480',   'name' => 'Impuesto al patrimonio',                          'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2495',   'name' => 'Otros impuestos',                                 'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ─── 25 Obligaciones laborales ────────────────────────────────
            ['code' => '25',     'name' => 'OBLIGACIONES LABORALES',                          'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '2505',   'name' => 'Salarios por pagar',                              'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2510',   'name' => 'Cesantías consolidadas',                          'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2515',   'name' => 'Intereses sobre cesantías',                       'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2520',   'name' => 'Prima de servicios',                              'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2525',   'name' => 'Vacaciones consolidadas',                         'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2530',   'name' => 'Prestaciones extralegales',                       'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2535',   'name' => 'Pensiones de jubilación',                         'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2540',   'name' => 'Cuotas partes pensiones de jubilación',           'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2545',   'name' => 'Bonificaciones por pagar',                        'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2550',   'name' => 'Indemnizaciones laborales',                       'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2555',   'name' => 'Auxilio de transporte',                           'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2560',   'name' => 'Auxilio de cesantías — fondo',                    'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2595',   'name' => 'Otras obligaciones laborales',                    'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ─── 26 Pasivos estimados y provisiones ───────────────────────
            ['code' => '26',     'name' => 'PASIVOS ESTIMADOS Y PROVISIONES',                 'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '2605',   'name' => 'Para costos y gastos',                            'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2610',   'name' => 'Para obligaciones fiscales',                      'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2615',   'name' => 'Para obligaciones laborales',                     'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2620',   'name' => 'Para reparaciones garantizadas',                  'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2625',   'name' => 'Para contingencias',                              'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2695',   'name' => 'Otras provisiones',                               'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ─── 27 Diferidos (pasivo) ────────────────────────────────────
            ['code' => '27',     'name' => 'DIFERIDOS (PASIVO)',                              'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '2705',   'name' => 'Ingresos recibidos para terceros',                'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2710',   'name' => 'Ingresos recibidos por anticipado',               'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '271005', 'name' => 'Intereses recibidos por anticipado',              'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '271010', 'name' => 'Arrendamientos recibidos por anticipado',         'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '271015', 'name' => 'Seguros recibidos por anticipado',                'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '2715',   'name' => 'Créditos diferidos',                              'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ─── 28 Otros pasivos ─────────────────────────────────────────
            ['code' => '28',     'name' => 'OTROS PASIVOS',                                   'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '2805',   'name' => 'Anticipos y avances recibidos',                   'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2810',   'name' => 'Depósitos recibidos',                             'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2815',   'name' => 'Ingresos recibidos para terceros',                'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '2895',   'name' => 'Otros',                                           'type' => 'pasivo',     'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ══════════════════════════════════════════════════════════════
            // CLASE 3 · PATRIMONIO
            // ══════════════════════════════════════════════════════════════
            ['code' => '3',      'name' => 'PATRIMONIO',                                      'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 1],

            // ─── 31 Capital social ────────────────────────────────────────
            ['code' => '31',     'name' => 'CAPITAL SOCIAL',                                  'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '3105',   'name' => 'Capital suscrito y pagado',                       'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '310505', 'name' => 'Capital autorizado',                              'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '310510', 'name' => 'Capital por suscribir (DB)',                      'type' => 'patrimonio', 'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '310515', 'name' => 'Capital suscrito por cobrar (DB)',                'type' => 'patrimonio', 'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '3110',   'name' => 'Aportes sociales',                                'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '311005', 'name' => 'Cuotas o partes de interés social',              'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '311010', 'name' => 'Aportes de socios',                              'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '3115',   'name' => 'Capital de personas naturales',                   'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '311505', 'name' => 'Cuotas o partes de interés social',              'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '311510', 'name' => 'Aportes de socios — fondo mutuo de inversión',   'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '311515', 'name' => 'Contribución de la empresa — fondo mutuo',       'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '311520', 'name' => 'Suscripciones del público',                      'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '3120',   'name' => 'Capital asignado',                                'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '312005', 'name' => 'Principal',                                      'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '312010', 'name' => 'Sucursales y agencias',                          'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '3125',   'name' => 'Capital por suscribir (Db)',                      'type' => 'patrimonio', 'nature' => 'debito',  'parent_id' => null, 'level' => 3],

            // ─── 32 Superávit de capital ──────────────────────────────────
            ['code' => '32',     'name' => 'SUPERÁVIT DE CAPITAL',                            'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '3205',   'name' => 'Prima en colocación de acciones',                 'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '3210',   'name' => 'Donaciones',                                      'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '3215',   'name' => 'Crédito mercantil donado',                        'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '3225',   'name' => 'Superávit método de participación',               'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ─── 33 Reservas ──────────────────────────────────────────────
            ['code' => '33',     'name' => 'RESERVAS',                                        'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '3305',   'name' => 'Reserva legal',                                   'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '3310',   'name' => 'Reservas estatutarias',                           'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '3315',   'name' => 'Reservas ocasionales',                            'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '331505', 'name' => 'Para futuros ensanches',                          'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '331510', 'name' => 'Para readquisición de acciones propias',          'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '3320',   'name' => 'Reservas para protección de inversiones',         'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '3325',   'name' => 'Reservas por donaciones',                         'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ─── 34 Revalorización del patrimonio ─────────────────────────
            ['code' => '34',     'name' => 'REVALORIZACIÓN DEL PATRIMONIO',                   'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '3405',   'name' => 'Revalorización del patrimonio',                   'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ─── 35 Dividendos o participaciones decretados en acciones ───
            ['code' => '35',     'name' => 'DIVIDENDOS O PARTICIPACIONES DECRETADOS EN ACCIONES', 'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '3505',   'name' => 'Dividendos decretados en acciones',               'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '3510',   'name' => 'Participaciones decretadas en cuotas',            'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ─── 36 Resultados del ejercicio ──────────────────────────────
            ['code' => '36',     'name' => 'RESULTADOS DEL EJERCICIO',                        'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '3605',   'name' => 'Utilidad del ejercicio',                          'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '3610',   'name' => 'Pérdida del ejercicio (Db)',                      'type' => 'patrimonio', 'nature' => 'debito',  'parent_id' => null, 'level' => 3],

            // ─── 37 Resultados de ejercicios anteriores ───────────────────
            ['code' => '37',     'name' => 'RESULTADOS DE EJERCICIOS ANTERIORES',             'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '3705',   'name' => 'Utilidades acumuladas',                           'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '3710',   'name' => 'Pérdidas acumuladas (Db)',                        'type' => 'patrimonio', 'nature' => 'debito',  'parent_id' => null, 'level' => 3],

            // ─── 38 Superávit por valorizaciones ──────────────────────────
            ['code' => '38',     'name' => 'SUPERÁVIT POR VALORIZACIONES',                    'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '3805',   'name' => 'De inversiones',                                  'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '3810',   'name' => 'De propiedades, planta y equipo',                 'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '3815',   'name' => 'De otros activos',                                'type' => 'patrimonio', 'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ══════════════════════════════════════════════════════════════
            // CLASE 4 · INGRESOS
            // ══════════════════════════════════════════════════════════════
            ['code' => '4',      'name' => 'INGRESOS',                                        'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 1],

            // ─── 41 Operacionales ─────────────────────────────────────────
            ['code' => '41',     'name' => 'OPERACIONALES',                                   'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '4105',   'name' => 'Agricultura, ganadería, caza y silvicultura',     'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4110',   'name' => 'Pesca',                                           'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4115',   'name' => 'Explotación de minas y canteras',                 'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4120',   'name' => 'Industrias manufactureras',                       'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4125',   'name' => 'Construcción',                                    'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4130',   'name' => 'Comercio al por mayor',                           'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4135',   'name' => 'Comercio al por mayor y al por menor',            'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '413505', 'name' => 'Ventas brutas',                                   'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '413510', 'name' => 'Devoluciones en ventas (Db)',                     'type' => 'ingreso',    'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '413515', 'name' => 'Descuentos en ventas (Db)',                       'type' => 'ingreso',    'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '4140',   'name' => 'Hoteles, restaurantes y similares',               'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4145',   'name' => 'Transporte, almacenamiento y comunicaciones',     'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4150',   'name' => 'Financieros y seguros',                           'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4155',   'name' => 'Actividades inmobiliarias',                       'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4160',   'name' => 'Servicios',                                       'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4165',   'name' => 'Honorarios',                                      'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4170',   'name' => 'Arrendamientos',                                  'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4175',   'name' => 'Regalías',                                        'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4195',   'name' => 'Devoluciones en ventas (Db)',                     'type' => 'ingreso',    'nature' => 'debito',  'parent_id' => null, 'level' => 3],

            // ─── 42 No operacionales ──────────────────────────────────────
            ['code' => '42',     'name' => 'NO OPERACIONALES',                                'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '4205',   'name' => 'Financieros',                                     'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4210',   'name' => 'Intereses',                                       'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4215',   'name' => 'Descuentos comerciales',                          'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4220',   'name' => 'Dividendos y participaciones',                    'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4225',   'name' => 'Indemnizaciones',                                 'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4230',   'name' => 'Utilidad en venta de propiedades planta y equipo','type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4235',   'name' => 'Utilidad en venta de inversiones',                'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4240',   'name' => 'Ingresos de ejercicios anteriores',               'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4245',   'name' => 'Recuperaciones',                                  'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4250',   'name' => 'Ingresos método de participación',                'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4255',   'name' => 'Devolución de impuestos',                         'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '4295',   'name' => 'Diversos',                                        'type' => 'ingreso',    'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ══════════════════════════════════════════════════════════════
            // CLASE 5 · GASTOS
            // ══════════════════════════════════════════════════════════════
            ['code' => '5',      'name' => 'GASTOS',                                          'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 1],

            // ─── 51 Operacionales de administración ───────────────────────
            ['code' => '51',     'name' => 'OPERACIONALES DE ADMINISTRACIÓN',                 'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '5105',   'name' => 'Gastos de personal',                              'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '510505', 'name' => 'Sueldos',                                         'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '510510', 'name' => 'Horas extras y recargos',                         'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '510515', 'name' => 'Comisiones',                                      'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '510520', 'name' => 'Auxilio de transporte',                           'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '510525', 'name' => 'Cesantías',                                       'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '510530', 'name' => 'Intereses sobre cesantías',                       'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '510535', 'name' => 'Prima de servicios',                              'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '510540', 'name' => 'Vacaciones',                                      'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '510545', 'name' => 'Aportes a EPS',                                   'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '510550', 'name' => 'Aportes a fondos de pensiones',                   'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '510555', 'name' => 'Aportes ARL',                                     'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '510560', 'name' => 'Aportes caja de compensación familiar',           'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '510565', 'name' => 'Aportes SENA',                                    'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '510570', 'name' => 'Aportes ICBF',                                    'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '5110',   'name' => 'Honorarios',                                      'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5115',   'name' => 'Impuestos',                                       'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '511505', 'name' => 'De industria y comercio',                         'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '511510', 'name' => 'Predial unificado',                               'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '511515', 'name' => 'De vehículos',                                    'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '511520', 'name' => 'Timbre nacional',                                 'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '5120',   'name' => 'Arrendamientos',                                  'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5125',   'name' => 'Contribuciones y afiliaciones',                   'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5130',   'name' => 'Seguros',                                         'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5135',   'name' => 'Servicios',                                       'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '513505', 'name' => 'Aseo y vigilancia',                               'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '513510', 'name' => 'Temporales',                                      'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '513515', 'name' => 'Acueducto y alcantarillado',                      'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '513520', 'name' => 'Energía eléctrica',                               'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '513525', 'name' => 'Teléfono',                                        'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '513530', 'name' => 'Correo, portes y telegramas',                     'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '513535', 'name' => 'Internet y conectividad',                         'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '5140',   'name' => 'Gastos legales',                                  'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5145',   'name' => 'Mantenimiento y reparaciones',                    'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5150',   'name' => 'Adecuación e instalación',                        'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5155',   'name' => 'Gastos de viaje',                                 'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5160',   'name' => 'Depreciaciones',                                  'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '516005', 'name' => 'Construcciones y edificaciones',                  'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '516010', 'name' => 'Maquinaria y equipo',                             'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '516015', 'name' => 'Equipo de oficina',                               'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '516020', 'name' => 'Equipo de cómputo y comunicación',                'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '516025', 'name' => 'Flota y equipo de transporte',                    'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '5165',   'name' => 'Amortizaciones',                                  'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '516505', 'name' => 'De intangibles',                                  'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '516510', 'name' => 'De diferidos',                                    'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '5170',   'name' => 'Provisiones',                                     'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '517005', 'name' => 'Para deudores — clientes',                        'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '517010', 'name' => 'Para deudores de difícil cobro',                  'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '517015', 'name' => 'Para inventarios',                                'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '517020', 'name' => 'Para propiedades planta y equipo',                'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '5175',   'name' => 'Diversos',                                        'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '517505', 'name' => 'Útiles y papelería',                              'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '517510', 'name' => 'Combustibles y lubricantes',                      'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '517515', 'name' => 'Elementos de aseo y cafetería',                   'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '5195',   'name' => 'Gastos de administración diversos',               'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],

            // ─── 52 Operacionales de ventas ───────────────────────────────
            ['code' => '52',     'name' => 'OPERACIONALES DE VENTAS',                         'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '5205',   'name' => 'Gastos de personal — ventas',                     'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '520505', 'name' => 'Sueldos',                                         'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '520510', 'name' => 'Comisiones',                                      'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '520515', 'name' => 'Cesantías',                                       'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '520520', 'name' => 'Prima de servicios',                              'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '520525', 'name' => 'Vacaciones',                                      'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '5210',   'name' => 'Honorarios — ventas',                             'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5215',   'name' => 'Impuestos — ventas',                              'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5220',   'name' => 'Arrendamientos — ventas',                         'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5225',   'name' => 'Contribuciones y afiliaciones — ventas',          'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5230',   'name' => 'Seguros — ventas',                                'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5235',   'name' => 'Servicios — ventas',                              'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5240',   'name' => 'Gastos legales — ventas',                         'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5245',   'name' => 'Mantenimiento y reparaciones — ventas',           'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5250',   'name' => 'Adecuación e instalación — ventas',               'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5255',   'name' => 'Gastos de viaje — ventas',                        'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5260',   'name' => 'Depreciaciones — ventas',                         'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5265',   'name' => 'Amortizaciones — ventas',                         'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5295',   'name' => 'Gastos de ventas diversos',                       'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '529505', 'name' => 'Publicidad y propaganda',                         'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '529510', 'name' => 'Relaciones públicas',                             'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],

            // ─── 53 No operacionales ──────────────────────────────────────
            ['code' => '53',     'name' => 'NO OPERACIONALES',                                'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '5305',   'name' => 'Financieros',                                     'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '530505', 'name' => 'Intereses',                                       'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '530510', 'name' => 'Comisiones bancarias',                            'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '530515', 'name' => 'Diferencia en cambio',                            'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '530520', 'name' => 'GMF — 4x1000',                                   'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '5310',   'name' => 'Pérdida en venta de propiedades planta y equipo', 'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5315',   'name' => 'Pérdida en venta de inversiones',                 'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5320',   'name' => 'Gastos de ejercicios anteriores',                 'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5325',   'name' => 'Impuesto de renta y complementarios',             'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5330',   'name' => 'Multas, sanciones, litigios e indemnizaciones',   'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5335',   'name' => 'Donaciones',                                      'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5340',   'name' => 'Pérdidas por siniestros',                         'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '5395',   'name' => 'Gastos no operacionales diversos',                'type' => 'gasto',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],

            // ══════════════════════════════════════════════════════════════
            // CLASE 6 · COSTOS DE VENTA
            // ══════════════════════════════════════════════════════════════
            ['code' => '6',      'name' => 'COSTOS DE VENTA',                                 'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 1],
            ['code' => '61',     'name' => 'COSTO DE VENTAS Y DE PRESTACIÓN DE SERVICIOS',    'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '6105',   'name' => 'Agricultura, ganadería, caza y silvicultura',     'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '6110',   'name' => 'Pesca',                                           'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '6115',   'name' => 'Explotación de minas y canteras',                 'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '6120',   'name' => 'Industrias manufactureras',                       'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '6125',   'name' => 'Construcción',                                    'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '6130',   'name' => 'Comercio al por mayor',                           'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '6135',   'name' => 'Comercio al por mayor y al por menor',            'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '613505', 'name' => 'Compras brutas',                                  'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 4],
            ['code' => '613510', 'name' => 'Devoluciones en compras (Cr)',                    'type' => 'costo',      'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '613515', 'name' => 'Descuentos en compras (Cr)',                      'type' => 'costo',      'nature' => 'credito', 'parent_id' => null, 'level' => 4],
            ['code' => '6140',   'name' => 'Hoteles, restaurantes y similares',               'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '6145',   'name' => 'Transporte, almacenamiento y comunicaciones',     'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '6150',   'name' => 'Financieros y seguros',                           'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '6160',   'name' => 'Servicios',                                       'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '6195',   'name' => 'Devoluciones en compras (Cr)',                    'type' => 'costo',      'nature' => 'credito', 'parent_id' => null, 'level' => 3],

            // ══════════════════════════════════════════════════════════════
            // CLASE 7 · COSTOS DE PRODUCCIÓN O DE OPERACIÓN
            // ══════════════════════════════════════════════════════════════
            ['code' => '7',      'name' => 'COSTOS DE PRODUCCIÓN O DE OPERACIÓN',             'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 1],
            ['code' => '71',     'name' => 'MATERIA PRIMA',                                   'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '7105',   'name' => 'Materias primas y materiales',                    'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '72',     'name' => 'MANO DE OBRA DIRECTA',                            'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '7205',   'name' => 'Sueldos y salarios',                              'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '7210',   'name' => 'Horas extras y recargos',                         'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '7215',   'name' => 'Aportes a EPS, pensiones y ARL',                  'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '7220',   'name' => 'Prestaciones sociales',                           'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '73',     'name' => 'COSTOS INDIRECTOS',                               'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '7305',   'name' => 'Materiales indirectos',                           'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '7310',   'name' => 'Mano de obra indirecta',                          'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '7315',   'name' => 'Depreciación de planta y equipo',                 'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '7320',   'name' => 'Amortización de diferidos de producción',         'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '7325',   'name' => 'Servicios públicos de planta',                    'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '7395',   'name' => 'Otros costos indirectos',                         'type' => 'costo',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],

            // ══════════════════════════════════════════════════════════════
            // CLASE 8 · CUENTAS DE ORDEN DEUDORAS
            // ══════════════════════════════════════════════════════════════
            ['code' => '8',      'name' => 'CUENTAS DE ORDEN DEUDORAS',                       'type' => 'orden',      'nature' => 'debito',  'parent_id' => null, 'level' => 1],
            ['code' => '81',     'name' => 'DERECHOS CONTINGENTES',                           'type' => 'orden',      'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '8105',   'name' => 'Litigios y demandas',                             'type' => 'orden',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '83',     'name' => 'ACTIVOS CASTIGADOS',                              'type' => 'orden',      'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '8305',   'name' => 'Inversiones',                                     'type' => 'orden',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '8310',   'name' => 'Deudores',                                        'type' => 'orden',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '86',     'name' => 'ACTIVOS TOTALMENTE DEPRECIADOS',                  'type' => 'orden',      'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '8605',   'name' => 'Propiedades, planta y equipo depreciados',        'type' => 'orden',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '89',     'name' => 'OTRAS CUENTAS DE ORDEN DEUDORAS',                 'type' => 'orden',      'nature' => 'debito',  'parent_id' => null, 'level' => 2],
            ['code' => '8905',   'name' => 'Bienes y valores entregados en garantía',         'type' => 'orden',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '8910',   'name' => 'Bienes y valores entregados en custodia',         'type' => 'orden',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],
            ['code' => '8915',   'name' => 'Mercancías recibidas en consignación',            'type' => 'orden',      'nature' => 'debito',  'parent_id' => null, 'level' => 3],

            // ══════════════════════════════════════════════════════════════
            // CLASE 9 · CUENTAS DE ORDEN ACREEDORAS
            // ══════════════════════════════════════════════════════════════
            ['code' => '9',      'name' => 'CUENTAS DE ORDEN ACREEDORAS',                     'type' => 'orden',      'nature' => 'credito', 'parent_id' => null, 'level' => 1],
            ['code' => '91',     'name' => 'RESPONSABILIDADES CONTINGENTES',                  'type' => 'orden',      'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '9105',   'name' => 'Litigios y demandas',                             'type' => 'orden',      'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '9110',   'name' => 'Garantías otorgadas',                             'type' => 'orden',      'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '93',     'name' => 'ACTIVOS Y PASIVOS CONTINGENTES',                  'type' => 'orden',      'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '9305',   'name' => 'Activos contingentes',                            'type' => 'orden',      'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '99',     'name' => 'OTRAS CUENTAS DE ORDEN ACREEDORAS',               'type' => 'orden',      'nature' => 'credito', 'parent_id' => null, 'level' => 2],
            ['code' => '9905',   'name' => 'Bienes y valores recibidos en garantía',          'type' => 'orden',      'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '9910',   'name' => 'Bienes y valores recibidos en custodia',          'type' => 'orden',      'nature' => 'credito', 'parent_id' => null, 'level' => 3],
            ['code' => '9915',   'name' => 'Mercancías entregadas en consignación',           'type' => 'orden',      'nature' => 'credito', 'parent_id' => null, 'level' => 3],
        ];
    }
}
