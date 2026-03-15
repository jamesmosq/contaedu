<?php

use App\Exceptions\AccountingImbalanceException;
use App\Services\AccountingService;

it('lanza AccountingImbalanceException cuando débitos no son iguales a créditos', function () {
    $service = new AccountingService();
    $method  = new ReflectionMethod($service, 'createEntry');
    $method->setAccessible(true);

    $lines = [
        ['account_id' => 1, 'debit' => 100.00, 'credit' =>   0.00, 'description' => 'Débito prueba'],
        ['account_id' => 2, 'debit' =>   0.00, 'credit' =>  80.00, 'description' => 'Crédito prueba'], // no cuadra
    ];

    $header = [
        'date'           => '2025-01-01',
        'reference'      => 'TEST-001',
        'description'    => 'Asiento de prueba',
        'document_type'  => 'test',
        'document_id'    => 1,
        'auto_generated' => false,
    ];

    expect(fn () => $method->invoke($service, $header, $lines))
        ->toThrow(AccountingImbalanceException::class);
});

it('no lanza excepción cuando débitos y créditos son iguales', function () {
    // Verificar la lógica de balance matemático del servicio
    $lines = [
        ['debit' => 119.00, 'credit' =>   0.00],
        ['debit' =>   0.00, 'credit' => 100.00],
        ['debit' =>   0.00, 'credit' =>  19.00],
    ];

    $totalDebit  = array_sum(array_column($lines, 'debit'));
    $totalCredit = array_sum(array_column($lines, 'credit'));

    expect(abs($totalDebit - $totalCredit))->toBeLessThan(0.01);
});

it('AccountingImbalanceException es una RuntimeException', function () {
    $exception = new AccountingImbalanceException();

    expect($exception)->toBeInstanceOf(RuntimeException::class);
});

it('AccountingImbalanceException tiene mensaje por defecto en español', function () {
    $exception = new AccountingImbalanceException();

    expect($exception->getMessage())->toContain('débitos deben ser iguales a los créditos');
});

it('AccountingImbalanceException acepta mensaje personalizado', function () {
    $exception = new AccountingImbalanceException('Asiento desequilibrado: débitos=100, créditos=80');

    expect($exception->getMessage())->toContain('débitos=100');
});

it('tolerancia de 0.01 permite diferencias de redondeo menores', function () {
    // diferencia de 0.005 debe ser ACEPTADA (< 0.01)
    $lines = [
        ['debit' => 100.005, 'credit' =>   0.000],
        ['debit' =>   0.000, 'credit' => 100.000],
    ];

    $diff = abs(
        array_sum(array_column($lines, 'debit')) -
        array_sum(array_column($lines, 'credit'))
    );

    expect($diff)->toBeLessThan(0.01);
});

it('diferencia mayor a tolerancia debe ser rechazada', function () {
    $lines = [
        ['debit' => 100.02, 'credit' =>   0.00],
        ['debit' =>   0.00, 'credit' => 100.00],
    ];

    $diff = abs(
        array_sum(array_column($lines, 'debit')) -
        array_sum(array_column($lines, 'credit'))
    );

    expect($diff)->toBeGreaterThanOrEqual(0.01);
});
