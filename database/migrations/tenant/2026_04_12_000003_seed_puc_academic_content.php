<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('accounts') || ! Schema::hasColumn('accounts', 'descripcion')) {
            return;
        }

        $cuentas = [
            '1105' => [
                'descripcion'    => 'Registra la existencia en dinero efectivo o en cheques con que cuenta el ente económico, tanto en moneda nacional como extranjera, disponible en forma inmediata.',
                'dinamica_debe'  => "1. Por las entradas de dinero en efectivo y cheques recibidos por cualquier concepto.\n2. Por los sobrantes en caja al efectuar arqueos.\n3. Por la constitución o incremento del fondo de caja menor.",
                'dinamica_haber' => "1. Por el valor de las consignaciones diarias en cuentas corrientes o de ahorro.\n2. Por los faltantes en caja al efectuar arqueos.\n3. Por la reducción o cancelación del fondo de caja menor.\n4. Por el valor de los pagos en efectivo.",
                'ejemplo'        => 'Venta de contado por $500.000: DR 1105 Caja $500.000 / CR 4135 Ingresos $500.000.',
            ],
            '1110' => [
                'descripcion'    => 'Registra los movimientos de fondos en cuentas corrientes y de ahorros en entidades bancarias, en moneda nacional o extranjera.',
                'dinamica_debe'  => "1. Por las consignaciones de dinero efectivo, cheques o transferencias recibidas.\n2. Por las notas crédito del banco (intereses ganados, etc.).",
                'dinamica_haber' => "1. Por el valor de los cheques girados o transferencias enviadas.\n2. Por las notas débito del banco (comisiones, GMF 4x1000, etc.).",
                'ejemplo'        => 'Pago a proveedor por transferencia $2.000.000: DR 2205 Proveedores $2.000.000 / CR 1110 Bancos $2.000.000.',
            ],
            '1305' => [
                'descripcion'    => 'Registra el valor de las deudas a cargo de clientes por concepto de ventas de bienes o servicios realizados en desarrollo de las actividades propias del ente económico.',
                'dinamica_debe'  => "1. Por el valor de las ventas a crédito (total factura con IVA).\n2. Por las notas débito enviadas al cliente.",
                'dinamica_haber' => "1. Por los pagos recibidos de los clientes (recibos de caja).\n2. Por las notas crédito aplicadas.\n3. Por las devoluciones y descuentos concedidos.",
                'ejemplo'        => 'Venta a crédito $1.190.000 (IVA incluido): DR 1305 Clientes $1.190.000 / CR 4135 Ingresos $1.000.000 / CR 2408 IVA por pagar $190.000.',
            ],
            '1355' => [
                'descripcion'    => 'Registra los anticipos de impuestos y las retenciones que terceros han practicado al ente económico (retenciones sufridas).',
                'dinamica_debe'  => "1. Por las retenciones en la fuente que le practican al ente (sufridas).\n2. Por la retención de IVA sufrida.\n3. Por la retención de ICA sufrida.\n4. Por los anticipos de impuesto de renta pagados.",
                'dinamica_haber' => "1. Por la aplicación contra el impuesto a pagar en la declaración.\n2. Por las devoluciones de saldos a favor aprobadas por la DIAN.",
                'ejemplo'        => 'Gran contribuyente retiene 3.5% sobre venta de $1.000.000: DR 1355 Anticipo impuestos $35.000 (reduce la cartera neta cobrada).',
            ],
            '1435' => [
                'descripcion'    => 'Registra el costo de las mercancías adquiridas por el ente económico para ser vendidas sin transformación. Cuenta principal de inventario para empresas comerciales.',
                'dinamica_debe'  => "1. Por el valor de las compras de mercancía.\n2. Por los fletes necesarios para poner la mercancía en almacén.\n3. Por las devoluciones en ventas (ingreso al inventario).",
                'dinamica_haber' => "1. Por el costo de la mercancía vendida (salida del inventario).\n2. Por las devoluciones en compras.\n3. Por los faltantes detectados en toma física.",
                'ejemplo'        => 'Compra mercancía $3.000.000: DR 1435 Mercancías $3.000.000 / CR 2205 Proveedores $3.000.000. Al vender: DR 6135 Costo ventas $3.000.000 / CR 1435 Mercancías $3.000.000.',
            ],
            '1592' => [
                'descripcion'    => 'Registra la acumulación de la depreciación calculada sobre el costo de propiedades, planta y equipo. Cuenta de naturaleza crédito que reduce el valor en libros del activo.',
                'dinamica_debe'  => "1. Por la venta o baja del activo (se cancela la depreciación acumulada).\n2. Por ajustes que reduzcan la depreciación acumulada.",
                'dinamica_haber' => "1. Por el valor de la cuota de depreciación del período.\n2. Por ajustes que incrementen la depreciación acumulada.",
                'ejemplo'        => 'Depreciación mensual computador $4.800.000, vida útil 36 meses: DR 5160 Gasto depreciación $133.333 / CR 1592 Depreciación acumulada $133.333.',
            ],
            '2205' => [
                'descripcion'    => 'Registra el valor de las obligaciones contraídas por el ente económico con proveedores nacionales por adquisición de bienes o servicios.',
                'dinamica_debe'  => "1. Por el valor de los pagos realizados a proveedores.\n2. Por las notas crédito recibidas de proveedores.\n3. Por las retenciones practicadas al proveedor.",
                'dinamica_haber' => "1. Por el valor de las compras a crédito (total factura del proveedor).\n2. Por las notas débito recibidas del proveedor.",
                'ejemplo'        => 'Compra a crédito $2.380.000 (IVA incluido): DR 1435 Mercancías $2.000.000 / DR 2408 IVA descontable $380.000 / CR 2205 Proveedores $2.380.000.',
            ],
            '2365' => [
                'descripcion'    => 'Registra el valor de las retenciones en la fuente practicadas por el ente económico en su calidad de agente retenedor, sobre los pagos realizados a terceros.',
                'dinamica_debe'  => "1. Por el pago a la DIAN en la declaración mensual.\n2. Por ajustes que reduzcan el saldo a pagar.",
                'dinamica_haber' => "1. Por el valor de las retenciones practicadas en cada pago a proveedores o contratistas.",
                'ejemplo'        => 'Pago honorarios $2.000.000, retención 11%: DR 5115 Honorarios $2.000.000 / CR 2365 Retención fuente $220.000 / CR 1110 Bancos $1.780.000.',
            ],
            '2367' => [
                'descripcion'    => 'Registra el valor del IVA retenido por el ente económico en su calidad de agente retenedor del impuesto sobre las ventas.',
                'dinamica_debe'  => "1. Por el pago a la DIAN del IVA retenido declarado.",
                'dinamica_haber' => "1. Por el valor del IVA retenido a proveedores en cada compra (generalmente 50% del IVA facturado).",
                'ejemplo'        => 'Compra $1.000.000 + IVA $190.000, se retiene 50% del IVA: CR 2367 Reteiva $95.000.',
            ],
            '2408' => [
                'descripcion'    => 'Registra el IVA generado en ventas (cobrado) y el IVA pagado en compras (descontable). Saldo crédito = IVA a pagar DIAN; saldo débito = saldo a favor.',
                'dinamica_debe'  => "1. Por el IVA pagado en compras (IVA descontable).\n2. Por el pago del IVA neto a la DIAN en la declaración bimestral.",
                'dinamica_haber' => "1. Por el IVA cobrado en ventas (IVA generado).",
                'ejemplo'        => 'Venta $1.000.000 + IVA 19%: CR 2408 IVA por pagar $190.000. Compra $500.000 + IVA: DR 2408 IVA descontable $95.000. Pago neto DIAN: $190.000 - $95.000 = $95.000.',
            ],
            '3105' => [
                'descripcion'    => 'Registra el valor del capital suscrito y pagado por los socios o accionistas al constituir la empresa o en aumentos de capital posteriores.',
                'dinamica_debe'  => "1. Por la reducción de capital aprobada por la asamblea.\n2. Por la absorción de pérdidas acumuladas.",
                'dinamica_haber' => "1. Por el capital pagado al constituir la empresa.\n2. Por los aumentos de capital aprobados y pagados.",
                'ejemplo'        => 'Constitución SAS capital $100.000.000: DR 1110 Bancos $100.000.000 / CR 3105 Capital suscrito y pagado $100.000.000.',
            ],
            '4135' => [
                'descripcion'    => 'Registra el valor de los ingresos obtenidos por ventas de bienes o servicios propios de la actividad comercial del ente económico.',
                'dinamica_debe'  => "1. Por las devoluciones en ventas.\n2. Por los descuentos comerciales concedidos.\n3. Por el cierre al final del período.",
                'dinamica_haber' => "1. Por el valor de las ventas (sin IVA).\n2. Por ajustes que incrementen los ingresos.",
                'ejemplo'        => 'Venta 10 unidades a $100.000 c/u: DR 1305 Clientes $1.190.000 / CR 4135 Ingresos $1.000.000 / CR 2408 IVA $190.000.',
            ],
            '5160' => [
                'descripcion'    => 'Registra el gasto por pérdida de valor de propiedades, planta y equipo por uso, paso del tiempo u obsolescencia.',
                'dinamica_debe'  => "1. Por el valor de la cuota de depreciación del período.",
                'dinamica_haber' => "1. Por el cierre al final del período contable.",
                'ejemplo'        => 'Vehículo $60.000.000, vida útil 60 meses, valor residual $5.000.000. Cuota mensual = $916.667: DR 5160 Gasto depreciación $916.667 / CR 1592 Depreciación acumulada $916.667.',
            ],
            '5195' => [
                'descripcion'    => 'Registra los gastos administrativos y de operación que no tienen clasificación específica en otras subcuentas de gastos.',
                'dinamica_debe'  => "1. Por el valor de gastos varios no clasificados en otras cuentas.",
                'dinamica_haber' => "1. Por el cierre al final del período contable.",
                'ejemplo'        => 'Compra útiles de oficina $150.000: DR 5195 Gastos generales $150.000 / CR 1105 Caja $150.000.',
            ],
            '6135' => [
                'descripcion'    => 'Registra el costo de las mercancías vendidas durante el período. Representa el valor en libros de los bienes que salieron del inventario por ventas.',
                'dinamica_debe'  => "1. Por el costo de las mercancías entregadas a clientes.",
                'dinamica_haber' => "1. Por las devoluciones en ventas (regresa al inventario).\n2. Por el cierre al final del período.",
                'ejemplo'        => 'Mercancía vendida con costo $700.000: DR 6135 Costo de ventas $700.000 / CR 1435 Mercancías $700.000. (Siempre acompaña la factura de venta).',
            ],
        ];

        foreach ($cuentas as $code => $data) {
            DB::table('accounts')->where('code', $code)->update($data);
        }
    }

    public function down(): void
    {
        DB::table('accounts')->update([
            'descripcion'    => null,
            'dinamica_debe'  => null,
            'dinamica_haber' => null,
            'ejemplo'        => null,
        ]);
    }
};
