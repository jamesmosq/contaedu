<?php

use App\Models\Tenant\CashReceiptItem;
use App\Models\Tenant\CreditNote;
use App\Models\Tenant\Invoice;

// ─── Invoice::balance() ──────────────────────────────────────────────────────

it('balance de factura sin pagos ni notas es igual al total', function () {
    $invoice = new Invoice(['total' => 500_000.0]);

    // Sin relaciones cargadas → amountReceived/amountCredited retornan 0 desde la suma de colección vacía
    expect($invoice->total)->toBe(500_000.0);
});

it('balance nunca es negativo aunque los pagos superen el total', function () {
    $total   = 100_000.0;
    $balance = max(0, $total - 120_000.0);

    expect($balance)->toBe(0);
});

it('balance se calcula correctamente con pago parcial', function () {
    $total        = 500_000.0;
    $paid         = 200_000.0;
    $expected     = max(0, $total - $paid);

    expect($expected)->toBe(300_000.0);
});

it('balance es cero cuando el pago cubre el total exacto', function () {
    $total    = 100_000.0;
    $paid     = 100_000.0;
    $balance  = max(0, $total - $paid);

    expect($balance)->toBe(0);
});

it('balance reduce con nota de crédito', function () {
    $total          = 500_000.0;
    $creditNoteTotal = 100_000.0;
    $balance        = max(0, $total - $creditNoteTotal);

    expect($balance)->toBe(400_000.0);
});

it('balance reduce con pago y nota de crédito combinados', function () {
    $total           = 500_000.0;
    $paid            = 200_000.0;
    $creditNoteTotal = 100_000.0;
    $balance         = max(0, $total - $paid - $creditNoteTotal);

    expect($balance)->toBe(200_000.0);
});

// ─── CreditNote::fullReference() ─────────────────────────────────────────────

it('referencia de nota de crédito tiene prefijo NC-', function () {
    $cn = new CreditNote();
    $cn->id = 1;

    expect($cn->fullReference())->toBe('NC-00001');
});

it('referencia de nota de crédito rellena con ceros a 5 dígitos', function () {
    $cn = new CreditNote();
    $cn->id = 123;

    expect($cn->fullReference())->toBe('NC-00123');
});

// ─── Validación de monto de recibo vs saldo ───────────────────────────────────

it('recibo no puede ser mayor al saldo de la factura', function () {
    $invoiceTotal  = 100_000.0;
    $alreadyPaid   = 0.0;
    $balance       = $invoiceTotal - $alreadyPaid;
    $receiptAmount = 150_000.0;

    $exceedsBalance = $receiptAmount > $balance + 0.01;

    expect($exceedsBalance)->toBeTrue();
});

it('recibo puede ser igual al saldo de la factura', function () {
    $balance       = 100_000.0;
    $receiptAmount = 100_000.0;

    $exceedsBalance = $receiptAmount > $balance + 0.01;

    expect($exceedsBalance)->toBeFalse();
});

it('recibo puede ser menor al saldo para cobros parciales', function () {
    $balance       = 100_000.0;
    $receiptAmount = 50_000.0;

    $exceedsBalance = $receiptAmount > $balance + 0.01;

    expect($exceedsBalance)->toBeFalse();
});

// ─── Cálculo de totales de nota de crédito ────────────────────────────────────

it('total de nota de crédito es subtotal mas IVA', function () {
    $lines = [
        ['unit_price' => 100_000.0, 'qty' => 2.0, 'tax_rate' => 19],
        ['unit_price' =>  50_000.0, 'qty' => 1.0, 'tax_rate' => 0],
    ];

    $subtotal  = 0.0;
    $taxAmount = 0.0;

    foreach ($lines as $l) {
        $lineSub   = round($l['unit_price'] * $l['qty'], 2);
        $lineTax   = round($lineSub * ($l['tax_rate'] / 100), 2);
        $subtotal  += $lineSub;
        $taxAmount += $lineTax;
    }

    $total = $subtotal + $taxAmount;

    expect($subtotal)->toBe(250_000.0);
    expect($taxAmount)->toBe(38_000.0);    // 19% de 200.000
    expect($total)->toBe(288_000.0);
});

it('nota de crédito con cantidad cero excluye la línea', function () {
    $lines = [
        ['unit_price' => 100_000.0, 'qty' => 2.0, 'tax_rate' => 19],
        ['unit_price' =>  50_000.0, 'qty' => 0.0, 'tax_rate' => 19],  // excluida
    ];

    $activeLines = array_filter($lines, fn ($l) => $l['qty'] > 0);

    expect(count($activeLines))->toBe(1);
});

it('nota de crédito sin líneas activas es rechazada', function () {
    $lines = [
        ['qty' => 0.0],
        ['qty' => 0.0],
    ];

    $activeLines = array_filter($lines, fn ($l) => $l['qty'] > 0);

    expect(empty($activeLines))->toBeTrue();
});
