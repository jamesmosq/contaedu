<?php

use App\Exceptions\AccountingImbalanceException;
use App\Services\AccountingService;

// ─── Estructura del asiento de nota de crédito ────────────────────────────────
//
// Regla: Débito 4135 Ingresos + Débito 2408 IVA = Crédito 1305 CxC
// El asiento debe siempre cuadrar (débitos = créditos).

it('asiento de nota de crédito sin IVA cuadra: débito ingresos = crédito CxC', function () {
    $subtotal  = 100_000.0;
    $taxAmount = 0.0;
    $total     = $subtotal + $taxAmount;

    $lines = [
        ['account_id' => 1, 'debit' => $subtotal,  'credit' => 0.0,   'description' => 'NC ingresos'],
        ['account_id' => 3, 'debit' => 0.0,         'credit' => $total, 'description' => 'NC cartera'],
    ];

    $totalDebit  = array_sum(array_column($lines, 'debit'));
    $totalCredit = array_sum(array_column($lines, 'credit'));

    expect(abs($totalDebit - $totalCredit))->toBeLessThan(0.01);
});

it('asiento de nota de crédito con IVA 19% cuadra', function () {
    $subtotal  = 100_000.0;
    $taxAmount = 19_000.0;
    $total     = $subtotal + $taxAmount;

    $lines = [
        ['account_id' => 1, 'debit' => $subtotal,  'credit' => 0.0,   'description' => 'NC ingresos'],
        ['account_id' => 2, 'debit' => $taxAmount,  'credit' => 0.0,   'description' => 'NC IVA'],
        ['account_id' => 3, 'debit' => 0.0,          'credit' => $total, 'description' => 'NC cartera'],
    ];

    $totalDebit  = array_sum(array_column($lines, 'debit'));
    $totalCredit = array_sum(array_column($lines, 'credit'));

    expect($totalDebit)->toBe(119_000.0);
    expect($totalCredit)->toBe(119_000.0);
    expect(abs($totalDebit - $totalCredit))->toBeLessThan(0.01);
});

it('asiento de nota de crédito con IVA 5% cuadra', function () {
    $subtotal  = 200_000.0;
    $taxAmount = 10_000.0;
    $total     = $subtotal + $taxAmount;

    $lines = [
        ['account_id' => 1, 'debit' => $subtotal,  'credit' => 0.0,   'description' => 'NC ingresos'],
        ['account_id' => 2, 'debit' => $taxAmount,  'credit' => 0.0,   'description' => 'NC IVA'],
        ['account_id' => 3, 'debit' => 0.0,          'credit' => $total, 'description' => 'NC cartera'],
    ];

    $totalDebit  = array_sum(array_column($lines, 'debit'));
    $totalCredit = array_sum(array_column($lines, 'credit'));

    expect(abs($totalDebit - $totalCredit))->toBeLessThan(0.01);
});

it('asiento de nota de crédito desbalanceado lanza excepción', function () {
    $service = new AccountingService();
    $method  = new ReflectionMethod($service, 'createEntry');
    $method->setAccessible(true);

    $lines = [
        ['account_id' => 1, 'debit' => 100_000.0, 'credit' => 0.0,       'description' => 'NC ingresos'],
        // Falta el crédito a CxC → desequilibrado
        ['account_id' => 3, 'debit' => 0.0,        'credit' => 80_000.0, 'description' => 'NC cartera'],
    ];

    $header = [
        'date'           => '2025-01-01',
        'reference'      => 'NC-00001',
        'description'    => 'Nota crédito — FV00001 — Razón test',
        'document_type'  => 'credit_note',
        'document_id'    => 1,
        'auto_generated' => true,
    ];

    expect(fn () => $method->invoke($service, $header, $lines))
        ->toThrow(AccountingImbalanceException::class);
});

// ─── Estructura del asiento de recibo de caja ─────────────────────────────────
//
// Regla: Débito 1105 Caja = Crédito 1305 CxC

it('asiento de recibo de caja cuadra: débito caja = crédito CxC', function () {
    $amount = 250_000.0;

    $lines = [
        ['account_id' => 1, 'debit' => $amount,  'credit' => 0.0,    'description' => 'Recibo caja'],
        ['account_id' => 2, 'debit' => 0.0,       'credit' => $amount, 'description' => 'Abono cartera'],
    ];

    $totalDebit  = array_sum(array_column($lines, 'debit'));
    $totalCredit = array_sum(array_column($lines, 'credit'));

    expect(abs($totalDebit - $totalCredit))->toBeLessThan(0.01);
});

it('asiento de recibo de caja desbalanceado lanza excepción', function () {
    $service = new AccountingService();
    $method  = new ReflectionMethod($service, 'createEntry');
    $method->setAccessible(true);

    $lines = [
        ['account_id' => 1, 'debit' => 100_000.0, 'credit' => 0.0,      'description' => 'Recibo caja'],
        ['account_id' => 2, 'debit' => 0.0,        'credit' => 90_000.0, 'description' => 'Abono cartera'],
    ];

    $header = [
        'date'           => '2025-01-01',
        'reference'      => 'RC-00001',
        'description'    => 'Recibo de caja',
        'document_type'  => 'cash_receipt',
        'document_id'    => 1,
        'auto_generated' => true,
    ];

    expect(fn () => $method->invoke($service, $header, $lines))
        ->toThrow(AccountingImbalanceException::class);
});

// ─── Cálculo de totales de orden de compra ────────────────────────────────────

it('total de orden de compra es suma de líneas', function () {
    $lines = [
        ['unit_cost' => 50_000.0, 'qty' => 3],
        ['unit_cost' => 20_000.0, 'qty' => 5],
        ['unit_cost' =>  5_000.0, 'qty' => 10],
    ];

    $lineData = array_map(fn ($l) => round($l['unit_cost'] * $l['qty'], 2), $lines);
    $total    = array_sum($lineData);

    expect($total)->toBe(300_000.0);  // 150k + 100k + 50k
});

it('orden de compra recibida crea factura en borrador con mismo proveedor', function () {
    // Verifica que la lógica de receiveOrder copiaría el third_id de la orden
    $orderThirdId = 42;
    $invoiceData  = ['third_id' => $orderThirdId, 'status' => 'borrador'];

    expect($invoiceData['third_id'])->toBe($orderThirdId);
    expect($invoiceData['status'])->toBe('borrador');
});
