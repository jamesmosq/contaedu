<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $subcuentas = array_map(
            fn ($a) => array_merge($a, ['active' => true, 'parent_id' => null, 'created_at' => $now, 'updated_at' => $now]),
            $this->subcuentas()
        );

        DB::table('accounts')->insertOrIgnore($subcuentas);
    }

    public function down(): void {}

    private function subcuentas(): array
    {
        return [

            // ──────────────────────────────────────────────────────────────
            // CLASE 1 · ACTIVO — subcuentas faltantes
            // ──────────────────────────────────────────────────────────────

            // 1205 Acciones
            ['code' => '120505', 'name' => 'Acciones ordinarias',                              'type' => 'activo',     'nature' => 'debito',  'level' => 4],
            ['code' => '120510', 'name' => 'Acciones privilegiadas',                           'type' => 'activo',     'nature' => 'debito',  'level' => 4],

            // 1305 Clientes — ya tiene 130505, 130510 en seeder

            // 1330 Anticipos y avances — ya tiene subcuentas en seeder

            // 1405 Materias primas
            ['code' => '140505', 'name' => 'Nacionales',                                       'type' => 'activo',     'nature' => 'debito',  'level' => 4],
            ['code' => '140510', 'name' => 'Del exterior',                                     'type' => 'activo',     'nature' => 'debito',  'level' => 4],

            // 1410 Productos en proceso
            ['code' => '141005', 'name' => 'De fabricación propia',                            'type' => 'activo',     'nature' => 'debito',  'level' => 4],
            ['code' => '141010', 'name' => 'En poder de terceros',                             'type' => 'activo',     'nature' => 'debito',  'level' => 4],

            // 1430 Productos terminados
            ['code' => '143005', 'name' => 'En almacén',                                       'type' => 'activo',     'nature' => 'debito',  'level' => 4],
            ['code' => '143010', 'name' => 'En poder de terceros',                             'type' => 'activo',     'nature' => 'debito',  'level' => 4],

            // 1440 Terrenos
            ['code' => '144005', 'name' => 'Urbanos',                                          'type' => 'activo',     'nature' => 'debito',  'level' => 4],
            ['code' => '144010', 'name' => 'Rurales',                                          'type' => 'activo',     'nature' => 'debito',  'level' => 4],

            // 1455 Materiales, repuestos y accesorios
            ['code' => '145505', 'name' => 'Materiales',                                       'type' => 'activo',     'nature' => 'debito',  'level' => 4],
            ['code' => '145510', 'name' => 'Repuestos y accesorios',                           'type' => 'activo',     'nature' => 'debito',  'level' => 4],

            // 1460 Envases y empaques
            ['code' => '146005', 'name' => 'Envases',                                          'type' => 'activo',     'nature' => 'debito',  'level' => 4],
            ['code' => '146010', 'name' => 'Empaques',                                         'type' => 'activo',     'nature' => 'debito',  'level' => 4],

            // ──────────────────────────────────────────────────────────────
            // CLASE 2 · PASIVO — subcuentas faltantes
            // ──────────────────────────────────────────────────────────────

            // 2105 Bancos nacionales
            ['code' => '210505', 'name' => 'Préstamos bancarios ordinarios',                   'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '210510', 'name' => 'Préstamos bancarios de fomento',                   'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '210515', 'name' => 'Sobregiros bancarios',                             'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],

            // 2110 Corporaciones financieras
            ['code' => '211005', 'name' => 'Crédito ordinario',                               'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '211010', 'name' => 'Crédito de fomento',                              'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],

            // 2205 Proveedores nacionales
            ['code' => '220505', 'name' => 'Grandes empresas',                                 'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '220510', 'name' => 'Medianas empresas',                                'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '220515', 'name' => 'Pequeñas empresas',                                'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '220520', 'name' => 'Microempresarios',                                 'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],

            // 2335 Costos y gastos por pagar
            ['code' => '233505', 'name' => 'Honorarios',                                       'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '233510', 'name' => 'Comisiones',                                       'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '233515', 'name' => 'Servicios',                                        'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '233520', 'name' => 'Arrendamientos',                                   'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '233525', 'name' => 'Intereses',                                        'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],

            // 2365 Retención en la fuente
            ['code' => '236505', 'name' => 'Salarios y pagos laborales',                       'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '236510', 'name' => 'Honorarios',                                       'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '236515', 'name' => 'Comisiones',                                       'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '236520', 'name' => 'Servicios',                                        'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '236525', 'name' => 'Arrendamientos',                                   'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '236530', 'name' => 'Compras',                                          'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],

            // 2367 Impuesto a las ventas retenido (IVA)
            ['code' => '236705', 'name' => 'Servicios',                                        'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '236710', 'name' => 'Bienes',                                           'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],

            // 2368 Impuesto de industria y comercio retenido (ICA)
            ['code' => '236805', 'name' => 'Retención ICA',                                    'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],

            // 2370 Retenciones y aportes de nómina
            ['code' => '237005', 'name' => 'Aportes a EPS (salud)',                            'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '237010', 'name' => 'Aportes a fondos de pensiones',                    'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '237015', 'name' => 'Aportes ARL',                                      'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '237020', 'name' => 'Aportes caja de compensación',                     'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '237025', 'name' => 'Aportes SENA',                                     'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '237030', 'name' => 'Aportes ICBF',                                     'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],

            // 2380 Acreedores varios
            ['code' => '238005', 'name' => 'Accionistas o socios',                             'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '238010', 'name' => 'Directores',                                       'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],
            ['code' => '238095', 'name' => 'Otros acreedores',                                 'type' => 'pasivo',     'nature' => 'credito', 'level' => 4],

            // ──────────────────────────────────────────────────────────────
            // CLASE 3 · PATRIMONIO — subcuentas faltantes
            // ──────────────────────────────────────────────────────────────

            // 3105 Capital suscrito y pagado
            ['code' => '310505', 'name' => 'Capital autorizado',                               'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '310510', 'name' => 'Capital por suscribir (DB)',                       'type' => 'patrimonio', 'nature' => 'debito',  'level' => 4],
            ['code' => '310515', 'name' => 'Capital suscrito por cobrar (DB)',                 'type' => 'patrimonio', 'nature' => 'debito',  'level' => 4],

            // 3110 Aportes sociales
            ['code' => '311005', 'name' => 'Cuotas o partes de interés social',               'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '311010', 'name' => 'Aportes de socios',                               'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],

            // 3115 (Aportes sociales según puc.com.co — Capital de personas naturales en seeder)
            ['code' => '311505', 'name' => 'Cuotas o partes de interés social',               'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '311510', 'name' => 'Aportes de socios — fondo mutuo de inversión',    'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '311515', 'name' => 'Contribución de la empresa — fondo mutuo',        'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '311520', 'name' => 'Suscripciones del público',                       'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],

            // 3120 Capital asignado
            ['code' => '312005', 'name' => 'Principal',                                        'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '312010', 'name' => 'Sucursales y agencias',                            'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],

            // 3205 Prima en colocación de acciones
            ['code' => '320505', 'name' => 'Prima en colocación de acciones',                 'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '320510', 'name' => 'Prima en colocación de acciones por cobrar (DB)', 'type' => 'patrimonio', 'nature' => 'debito',  'level' => 4],
            ['code' => '320515', 'name' => 'Prima en colocación de cuotas o partes',          'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],

            // 3210 Donaciones
            ['code' => '321005', 'name' => 'En dinero',                                        'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '321010', 'name' => 'En valores mobiliarios',                           'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '321015', 'name' => 'En bienes muebles',                                'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '321020', 'name' => 'En bienes inmuebles',                              'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '321025', 'name' => 'En intangibles',                                   'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],

            // 3225 Superávit método de participación
            ['code' => '322505', 'name' => 'De acciones',                                      'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '322510', 'name' => 'De cuotas o partes de interés social',            'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],

            // 3305 Reserva legal
            ['code' => '330505', 'name' => 'Reserva legal',                                    'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '330510', 'name' => 'Reservas por disposiciones fiscales',              'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '330515', 'name' => 'Reserva para readquisición de acciones',           'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '330520', 'name' => 'Reserva para extensión agropecuaria',              'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '330525', 'name' => 'Reserva Ley 7ª de 1990',                           'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '330530', 'name' => 'Reserva para reposición de semovientes',           'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '330595', 'name' => 'Otras reservas obligatorias',                      'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],

            // 3310 Reservas estatutarias
            ['code' => '331005', 'name' => 'Para futuras capitalizaciones',                    'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '331010', 'name' => 'Para reposición de activos',                       'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '331015', 'name' => 'Para futuros ensanches',                           'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '331095', 'name' => 'Otras reservas estatutarias',                      'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],

            // 3315 Reservas ocasionales (331505, 331510 ya están en seeder)
            ['code' => '331515', 'name' => 'Para adquisición o reposición de activos',        'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '331520', 'name' => 'Para investigaciones y desarrollo',                'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '331525', 'name' => 'Para fomento económico',                           'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '331530', 'name' => 'Para capital de trabajo',                          'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '331535', 'name' => 'Para estabilización de rendimientos',              'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '331540', 'name' => 'A disposición del máximo órgano social',           'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '331595', 'name' => 'Otras reservas ocasionales',                       'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],

            // 3405 Revalorización del patrimonio
            ['code' => '340505', 'name' => 'De capital social',                                'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '340510', 'name' => 'De superávit de capital',                          'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '340515', 'name' => 'De reservas',                                      'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '340520', 'name' => 'De resultados de ejercicios anteriores',           'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],

            // 3505 Dividendos decretados en acciones
            ['code' => '350505', 'name' => 'Dividendos decretados en acciones ordinarias',    'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '350510', 'name' => 'Dividendos decretados en acciones privilegiadas', 'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],

            // 3510 Participaciones decretadas
            ['code' => '351005', 'name' => 'Participaciones decretadas',                       'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],

            // 3805 Superávit por valorizaciones
            ['code' => '380505', 'name' => 'De inversiones en acciones',                       'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '380510', 'name' => 'De inversiones en cuotas',                         'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],

            // 3810 De propiedades, planta y equipo
            ['code' => '381005', 'name' => 'Terrenos',                                         'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],
            ['code' => '381010', 'name' => 'Construcciones y edificaciones',                   'type' => 'patrimonio', 'nature' => 'credito', 'level' => 4],

            // ──────────────────────────────────────────────────────────────
            // CLASE 4 · INGRESOS — subcuentas faltantes
            // ──────────────────────────────────────────────────────────────

            // 4105 Agricultura, ganadería, caza y silvicultura
            ['code' => '410505', 'name' => 'Ventas brutas',                                    'type' => 'ingreso',    'nature' => 'credito', 'level' => 4],
            ['code' => '410510', 'name' => 'Devoluciones en ventas (DB)',                      'type' => 'ingreso',    'nature' => 'debito',  'level' => 4],

            // 4120 Industrias manufactureras
            ['code' => '412005', 'name' => 'Ventas brutas',                                    'type' => 'ingreso',    'nature' => 'credito', 'level' => 4],
            ['code' => '412010', 'name' => 'Devoluciones en ventas (DB)',                      'type' => 'ingreso',    'nature' => 'debito',  'level' => 4],

            // 4130 Comercio al por mayor
            ['code' => '413005', 'name' => 'Ventas brutas',                                    'type' => 'ingreso',    'nature' => 'credito', 'level' => 4],
            ['code' => '413010', 'name' => 'Devoluciones en ventas (DB)',                      'type' => 'ingreso',    'nature' => 'debito',  'level' => 4],

            // 4140 Hoteles, restaurantes
            ['code' => '414005', 'name' => 'Ventas brutas',                                    'type' => 'ingreso',    'nature' => 'credito', 'level' => 4],
            ['code' => '414010', 'name' => 'Devoluciones en ventas (DB)',                      'type' => 'ingreso',    'nature' => 'debito',  'level' => 4],

            // 4160 Servicios
            ['code' => '416005', 'name' => 'Servicios prestados',                              'type' => 'ingreso',    'nature' => 'credito', 'level' => 4],
            ['code' => '416010', 'name' => 'Devoluciones (DB)',                                'type' => 'ingreso',    'nature' => 'debito',  'level' => 4],

            // 4165 Honorarios
            ['code' => '416505', 'name' => 'Honorarios facturados',                            'type' => 'ingreso',    'nature' => 'credito', 'level' => 4],

            // 4170 Arrendamientos
            ['code' => '417005', 'name' => 'Arrendamientos de inmuebles',                      'type' => 'ingreso',    'nature' => 'credito', 'level' => 4],
            ['code' => '417010', 'name' => 'Arrendamientos de muebles',                        'type' => 'ingreso',    'nature' => 'credito', 'level' => 4],

            // 4205 Financieros no operacionales
            ['code' => '420505', 'name' => 'Intereses',                                        'type' => 'ingreso',    'nature' => 'credito', 'level' => 4],
            ['code' => '420510', 'name' => 'Rendimientos',                                     'type' => 'ingreso',    'nature' => 'credito', 'level' => 4],
            ['code' => '420515', 'name' => 'Diferencia en cambio',                             'type' => 'ingreso',    'nature' => 'credito', 'level' => 4],

            // ──────────────────────────────────────────────────────────────
            // CLASE 6 · COSTOS DE VENTA — subcuentas faltantes
            // ──────────────────────────────────────────────────────────────

            // 6105 Agricultura, ganadería
            ['code' => '610505', 'name' => 'Costo de ventas',                                  'type' => 'costo',      'nature' => 'debito',  'level' => 4],

            // 6120 Industrias manufactureras
            ['code' => '612005', 'name' => 'Costo de ventas',                                  'type' => 'costo',      'nature' => 'debito',  'level' => 4],

            // 6130 Comercio al por mayor
            ['code' => '613005', 'name' => 'Compras brutas',                                   'type' => 'costo',      'nature' => 'debito',  'level' => 4],
            ['code' => '613010', 'name' => 'Devoluciones en compras (CR)',                     'type' => 'costo',      'nature' => 'credito', 'level' => 4],

            // 6140 Hoteles, restaurantes
            ['code' => '614005', 'name' => 'Costo de servicios',                               'type' => 'costo',      'nature' => 'debito',  'level' => 4],

            // 6160 Servicios
            ['code' => '616005', 'name' => 'Costo de servicios prestados',                     'type' => 'costo',      'nature' => 'debito',  'level' => 4],
        ];
    }
};
