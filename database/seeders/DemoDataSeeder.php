<?php

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\PaymentStatus;
use App\Enums\ProductUnit;
use App\Enums\PurchaseInvoiceStatus;
use App\Enums\ReceiptStatus;
use App\Enums\TaxRate;
use App\Enums\ThirdType;
use App\Models\Central\Tenant;
use App\Models\Tenant\Account;
use App\Models\Tenant\CashReceipt;
use App\Models\Tenant\CashReceiptItem;
use App\Models\Tenant\CompanyConfig;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\Payment;
use App\Models\Tenant\Product;
use App\Models\Tenant\PurchaseInvoice;
use App\Models\Tenant\Third;
use App\Services\AccountingService;
use App\Services\InvoiceService;
use App\Services\PurchaseService;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Datos demo por empresa. Cada tenant tiene su propio contexto
     * para que los estudiantes vean empresas diferentes.
     */
    private array $empresas = [
        'cc1023456789' => [
            'config' => [
                'nit'              => '900111222-5',
                'razon_social'     => 'García Distribuciones S.A.S.',
                'regimen'          => 'comun',
                'direccion'        => 'Cra 15 # 93-47, Bogotá',
                'telefono'         => '6017654321',
                'email'            => 'info@garciadistrib.com',
                'prefijo_factura'  => 'FV',
                'resolucion_dian'  => 'Res. 18764000001234 del 2024-01-15',
            ],
            'clientes' => [
                ['document_type' => 'nit', 'document' => '900201301', 'name' => 'Supermercados El Ahorro S.A.S.',  'type' => ThirdType::Cliente,   'regimen' => 'comun',        'email' => 'compras@elahorro.com'],
                ['document_type' => 'nit', 'document' => '830045621', 'name' => 'Papelería Central Ltda.',         'type' => ThirdType::Cliente,   'regimen' => 'simplificado', 'email' => 'pedidos@papelcentral.com'],
                ['document_type' => 'cc',  'document' => '79456123',  'name' => 'José Armando Cárdenas',          'type' => ThirdType::Cliente,   'regimen' => 'simplificado', 'email' => 'jacardenas@gmail.com'],
            ],
            'proveedores' => [
                ['document_type' => 'nit', 'document' => '860004060', 'name' => 'Colombina S.A.',                  'type' => ThirdType::Proveedor, 'regimen' => 'comun',        'email' => 'ventas@colombina.com'],
                ['document_type' => 'nit', 'document' => '890903939', 'name' => 'Nestlé de Colombia S.A.',         'type' => ThirdType::Proveedor, 'regimen' => 'comun',        'email' => 'proveedores@nestle.com'],
            ],
            'productos' => [
                ['code' => 'PRD-001', 'name' => 'Confites surtidos x 500g',    'unit' => ProductUnit::Kilogramo, 'sale_price' => 18500,  'cost_price' => 11000, 'tax_rate' => TaxRate::Exento],
                ['code' => 'PRD-002', 'name' => 'Galletas Festival x 12 und',  'unit' => ProductUnit::Caja,      'sale_price' => 52000,  'cost_price' => 32000, 'tax_rate' => TaxRate::Exento],
                ['code' => 'PRD-003', 'name' => 'Chocolate Jet x 16 und',      'unit' => ProductUnit::Caja,      'sale_price' => 38000,  'cost_price' => 23000, 'tax_rate' => TaxRate::Exento],
                ['code' => 'PRD-004', 'name' => 'Leche condensada x 397g',     'unit' => ProductUnit::Unidad,    'sale_price' => 8900,   'cost_price' => 5500,  'tax_rate' => TaxRate::Exento],
            ],
        ],

        'cc1098765432' => [
            'config' => [
                'nit'              => '900333444-7',
                'razon_social'     => 'Pérez Comercial E.U.',
                'regimen'          => 'simplificado',
                'direccion'        => 'Cl 72 # 11-25, Medellín',
                'telefono'         => '6044512233',
                'email'            => 'contacto@perezcomercial.co',
                'prefijo_factura'  => 'FA',
                'resolucion_dian'  => 'Res. 18764000005678 del 2024-03-01',
            ],
            'clientes' => [
                ['document_type' => 'nit', 'document' => '800100200', 'name' => 'Ferretería Los Andes Ltda.',       'type' => ThirdType::Cliente,   'regimen' => 'comun',        'email' => 'compras@ferretlosandes.com'],
                ['document_type' => 'nit', 'document' => '811033924', 'name' => 'Constructora Pórtico S.A.',        'type' => ThirdType::Cliente,   'regimen' => 'comun',        'email' => 'proveedores@portico.com.co'],
                ['document_type' => 'cc',  'document' => '43856712',  'name' => 'Claudia Inés Montoya',            'type' => ThirdType::Cliente,   'regimen' => 'simplificado', 'email' => 'cmontoya@outlook.com'],
            ],
            'proveedores' => [
                ['document_type' => 'nit', 'document' => '890907843', 'name' => 'Pinturas Tito Pabón S.A.',         'type' => ThirdType::Proveedor, 'regimen' => 'comun',        'email' => 'ventas@titopavon.com'],
                ['document_type' => 'nit', 'document' => '860077594', 'name' => 'Almacenes Éxito S.A.',             'type' => ThirdType::Proveedor, 'regimen' => 'comun',        'email' => 'proveedores@exito.com.co'],
            ],
            'productos' => [
                ['code' => 'HER-001', 'name' => 'Tornillo hex. galvanizado M8',  'unit' => ProductUnit::Unidad, 'sale_price' => 450,    'cost_price' => 250,   'tax_rate' => TaxRate::General],
                ['code' => 'HER-002', 'name' => 'Broca para concreto 1/2"',     'unit' => ProductUnit::Unidad, 'sale_price' => 12500,  'cost_price' => 7200,  'tax_rate' => TaxRate::General],
                ['code' => 'HER-003', 'name' => 'Cemento gris x 50kg',          'unit' => ProductUnit::Unidad, 'sale_price' => 28000,  'cost_price' => 19000, 'tax_rate' => TaxRate::General],
                ['code' => 'HER-004', 'name' => 'Varilla corrugada 1/2" x 6m',  'unit' => ProductUnit::Metro,  'sale_price' => 35000,  'cost_price' => 22000, 'tax_rate' => TaxRate::General],
            ],
        ],

        'cc1055544433' => [
            'config' => [
                'nit'              => '900555666-3',
                'razon_social'     => 'Rodríguez & Asociados S.A.S.',
                'regimen'          => 'comun',
                'direccion'        => 'Av. Roosevelt # 33-12, Cali',
                'telefono'         => '6023456789',
                'email'            => 'gerencia@rodriguezasoc.com',
                'prefijo_factura'  => 'FE',
                'resolucion_dian'  => 'Res. 18764000009012 del 2024-02-10',
            ],
            'clientes' => [
                ['document_type' => 'nit', 'document' => '800124850', 'name' => 'Clínica Valle del Lili',           'type' => ThirdType::Cliente,   'regimen' => 'comun',        'email' => 'compras@valledellili.org'],
                ['document_type' => 'nit', 'document' => '805001157', 'name' => 'Telecomunicaciones del Valle S.A.','type' => ThirdType::Cliente,   'regimen' => 'comun',        'email' => 'facturasrecib@telcovalle.com'],
                ['document_type' => 'cc',  'document' => '66918345',  'name' => 'Diana Marcela Torres',            'type' => ThirdType::Cliente,   'regimen' => 'simplificado', 'email' => 'dmtorres@gmail.com'],
            ],
            'proveedores' => [
                ['document_type' => 'nit', 'document' => '800099110', 'name' => 'Tecnología y Sistemas S.A.S.',     'type' => ThirdType::Proveedor, 'regimen' => 'comun',        'email' => 'ventas@tecnosis.com.co'],
                ['document_type' => 'nit', 'document' => '830115395', 'name' => 'Papeles y Empaques del Sur Ltda.', 'type' => ThirdType::Proveedor, 'regimen' => 'comun',        'email' => 'pedidos@papsur.com'],
            ],
            'productos' => [
                ['code' => 'SRV-001', 'name' => 'Consultoría contable (hora)',     'unit' => ProductUnit::Unidad, 'sale_price' => 120000, 'cost_price' => 0,     'tax_rate' => TaxRate::General],
                ['code' => 'SRV-002', 'name' => 'Auditoría financiera (día)',      'unit' => ProductUnit::Unidad, 'sale_price' => 850000, 'cost_price' => 0,     'tax_rate' => TaxRate::General],
                ['code' => 'SRV-003', 'name' => 'Declaración de renta persona nat.','unit' => ProductUnit::Unidad,'sale_price' => 280000, 'cost_price' => 0,     'tax_rate' => TaxRate::General],
                ['code' => 'INS-001', 'name' => 'Resma de papel carta x 500 hjs',  'unit' => ProductUnit::Unidad, 'sale_price' => 18500,  'cost_price' => 12000, 'tax_rate' => TaxRate::Exento],
            ],
        ],
    ];

    public function run(): void
    {
        $tenants = Tenant::on('pgsql')->whereIn('id', array_keys($this->empresas))->get();

        foreach ($tenants as $tenant) {
            $this->command?->info("→ Sembrando demo en: {$tenant->company_name} ({$tenant->id})");

            $tenant->run(function () use ($tenant) {
                $this->seedTenant($this->empresas[$tenant->id]);
            });
        }

        $this->command?->info('✓ Demo data sembrada en los 3 tenants.');
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function seedTenant(array $data): void
    {
        $invoiceService  = app(InvoiceService::class);
        $purchaseService = app(PurchaseService::class);

        // 1. Configuración de empresa
        $this->seedConfig($data['config']);

        // 2. Terceros
        $clientes    = $this->seedTerceros($data['clientes']);
        $proveedores = $this->seedTerceros($data['proveedores']);

        // 3. Productos
        $productos = $this->seedProductos($data['productos']);

        // 4. Facturas de venta (3 confirmadas)
        $this->seedFacturasVenta($clientes, $productos, $invoiceService);

        // 5. Facturas de compra (2 confirmadas)
        $this->seedFacturasCompra($proveedores, $productos, $purchaseService);
    }

    // ─── Config ──────────────────────────────────────────────────────────────

    private function seedConfig(array $cfg): void
    {
        CompanyConfig::updateOrCreate([], [
            'nit'             => $cfg['nit'],
            'razon_social'    => $cfg['razon_social'],
            'regimen'         => $cfg['regimen'],
            'direccion'       => $cfg['direccion'],
            'telefono'        => $cfg['telefono'],
            'email'           => $cfg['email'],
            'prefijo_factura' => $cfg['prefijo_factura'],
            'resolucion_dian' => $cfg['resolucion_dian'],
        ]);
    }

    // ─── Terceros ─────────────────────────────────────────────────────────────

    private function seedTerceros(array $terceros): array
    {
        $created = [];
        foreach ($terceros as $t) {
            $created[] = Third::firstOrCreate(
                ['document' => $t['document']],
                array_merge($t, [
                    'address' => 'Dirección de prueba',
                    'phone'   => '3001234567',
                    'active'  => true,
                ])
            );
        }
        return $created;
    }

    // ─── Productos ────────────────────────────────────────────────────────────

    private function seedProductos(array $productos): array
    {
        $invAccount  = Account::where('code', '1435')->value('id');
        $revAccount  = Account::where('code', '4135')->value('id');
        $cogsAccount = Account::where('code', '6135')->value('id');

        $created = [];
        foreach ($productos as $p) {
            $created[] = Product::firstOrCreate(
                ['code' => $p['code']],
                array_merge($p, [
                    'description'         => $p['name'],
                    'inventory_account_id'=> $invAccount,
                    'revenue_account_id'  => $revAccount,
                    'cogs_account_id'     => $cogsAccount,
                    'active'              => true,
                ])
            );
        }
        return $created;
    }

    // ─── Facturas de venta ────────────────────────────────────────────────────

    private function seedFacturasVenta(array $clientes, array $productos, InvoiceService $svc): void
    {
        $config = CompanyConfig::first();
        $series = $config?->prefijo_factura ?? 'FV';

        $facturas = [
            // Factura 1: cliente 0, productos 0 y 1
            [
                'third'  => $clientes[0],
                'date'   => now()->subDays(25)->toDateString(),
                'due'    => now()->subDays(5)->toDateString(),
                'lines'  => [
                    ['product' => $productos[0], 'qty' => 10, 'unit_price' => $productos[0]->sale_price],
                    ['product' => $productos[1], 'qty' => 5,  'unit_price' => $productos[1]->sale_price],
                ],
            ],
            // Factura 2: cliente 1, productos 2 y 3
            [
                'third'  => $clientes[1],
                'date'   => now()->subDays(15)->toDateString(),
                'due'    => now()->addDays(15)->toDateString(),
                'lines'  => [
                    ['product' => $productos[2], 'qty' => 8,  'unit_price' => $productos[2]->sale_price],
                    ['product' => $productos[3], 'qty' => 20, 'unit_price' => $productos[3]->sale_price],
                ],
            ],
            // Factura 3: cliente 2, producto 0
            [
                'third'  => $clientes[2],
                'date'   => now()->subDays(5)->toDateString(),
                'due'    => now()->addDays(25)->toDateString(),
                'lines'  => [
                    ['product' => $productos[0], 'qty' => 3, 'unit_price' => $productos[0]->sale_price],
                ],
            ],
        ];

        foreach ($facturas as $fData) {
            $lines = array_map(fn ($l) => $this->buildInvoiceLine($l), $fData['lines']);

            $invoice = $svc->saveDraft([
                'type'     => InvoiceType::Venta,
                'series'   => $series,
                'date'     => $fData['date'],
                'due_date' => $fData['due'],
                'third_id' => $fData['third']->id,
                'status'   => InvoiceStatus::Borrador,
                'notes'    => 'Factura generada por seeder demo.',
            ], $lines);

            $invoice->load('lines.product', 'third');
            $svc->confirm($invoice);

            // Recibo de caja parcial sobre la primera factura (50 %)
            if ($invoice->number === 1) {
                $this->seedReciboCaja($fData['third'], $invoice);
            }
        }
    }

    private function buildInvoiceLine(array $l): array
    {
        $product   = $l['product'];
        $qty       = $l['qty'];
        $unitPrice = $l['unit_price'];
        $taxRate   = $product->tax_rate instanceof TaxRate ? $product->tax_rate->value : (int) $product->tax_rate;
        $subtotal  = round($qty * $unitPrice, 2);
        $tax       = round($subtotal * $taxRate / 100, 2);

        return [
            'product_id'   => $product->id,
            'description'  => $product->name,
            'qty'          => $qty,
            'unit_price'   => $unitPrice,
            'discount_pct' => 0,
            'tax_rate'     => $taxRate,
            'line_subtotal'=> $subtotal,
            'line_tax'     => $tax,
            'line_total'   => $subtotal + $tax,
        ];
    }

    // ─── Recibo de caja ───────────────────────────────────────────────────────

    private function seedReciboCaja(Third $cliente, Invoice $invoice): void
    {
        $accounting = app(AccountingService::class);

        $montoPagado = round($invoice->total * 0.5, 2); // 50 % de la factura

        $receipt = CashReceipt::create([
            'third_id' => $cliente->id,
            'date'     => now()->subDays(10)->toDateString(),
            'total'    => $montoPagado,
            'notes'    => 'Anticipo 50 % — recibo demo.',
            'status'   => ReceiptStatus::Aplicado,
        ]);

        CashReceiptItem::create([
            'cash_receipt_id' => $receipt->id,
            'invoice_id'      => $invoice->id,
            'amount_applied'  => $montoPagado,
        ]);

        $receipt->load('third');
        $accounting->generateReceiptEntry($receipt);
    }

    // ─── Facturas de compra ───────────────────────────────────────────────────

    private function seedFacturasCompra(array $proveedores, array $productos, PurchaseService $svc): void
    {
        $compras = [
            // Compra 1: proveedor 0, productos 0 y 1
            [
                'proveedor'       => $proveedores[0],
                'date'            => now()->subDays(20)->toDateString(),
                'due_date'        => now()->addDays(10)->toDateString(),
                'supplier_invoice'=> 'FC-' . rand(1000, 9999),
                'lines'           => [
                    ['product' => $productos[0], 'qty' => 50, 'unit_cost' => $productos[0]->cost_price],
                    ['product' => $productos[1], 'qty' => 30, 'unit_cost' => $productos[1]->cost_price],
                ],
            ],
            // Compra 2: proveedor 1, productos 2 y 3
            [
                'proveedor'       => $proveedores[1],
                'date'            => now()->subDays(10)->toDateString(),
                'due_date'        => now()->addDays(20)->toDateString(),
                'supplier_invoice'=> 'FC-' . rand(1000, 9999),
                'lines'           => [
                    ['product' => $productos[2], 'qty' => 40, 'unit_cost' => $productos[2]->cost_price],
                    ['product' => $productos[3], 'qty' => 60, 'unit_cost' => $productos[3]->cost_price],
                ],
            ],
        ];

        foreach ($compras as $cData) {
            $subtotal  = 0;
            $taxAmount = 0;
            $lines     = [];

            foreach ($cData['lines'] as $l) {
                $product  = $l['product'];
                $qty      = $l['qty'];
                $cost     = $l['unit_cost'];
                $taxRate  = $product->tax_rate instanceof TaxRate ? $product->tax_rate->value : (int) $product->tax_rate;
                $lineSub  = round($qty * $cost, 2);
                $lineTax  = round($lineSub * $taxRate / 100, 2);

                $subtotal  += $lineSub;
                $taxAmount += $lineTax;

                $lines[] = [
                    'product_id'  => $product->id,
                    'description' => $product->name,
                    'qty'         => $qty,
                    'unit_cost'   => $cost,
                    'tax_rate'    => $taxRate,
                    'line_total'  => $lineSub + $lineTax,
                ];
            }

            $purchaseInvoice = PurchaseInvoice::create([
                'third_id'                => $cData['proveedor']->id,
                'supplier_invoice_number' => $cData['supplier_invoice'],
                'date'                    => $cData['date'],
                'due_date'                => $cData['due_date'],
                'status'                  => PurchaseInvoiceStatus::Borrador,
                'subtotal'                => $subtotal,
                'tax_amount'              => $taxAmount,
                'total'                   => $subtotal + $taxAmount,
            ]);

            foreach ($lines as $line) {
                $purchaseInvoice->lines()->create($line);
            }

            $purchaseInvoice->load('lines.product', 'third');
            $svc->confirmInvoice($purchaseInvoice);

            // Pago total a la primera factura de compra
            if ($purchaseInvoice->id === PurchaseInvoice::min('id')) {
                $this->seedPago($cData['proveedor'], $purchaseInvoice, $svc);
            }
        }
    }

    // ─── Pago a proveedor ─────────────────────────────────────────────────────

    private function seedPago(Third $proveedor, PurchaseInvoice $invoice, PurchaseService $svc): void
    {
        $payment = Payment::create([
            'third_id' => $proveedor->id,
            'date'     => now()->subDays(5)->toDateString(),
            'total'    => $invoice->total,
            'notes'    => 'Pago total — generado por seeder demo.',
            'status'   => PaymentStatus::Borrador,
        ]);

        $svc->applyPayment($payment, [[
            'purchase_invoice_id' => $invoice->id,
            'amount_applied'      => $invoice->total,
        ]]);
    }
}
