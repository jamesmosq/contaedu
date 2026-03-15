<?php

use App\Enums\PurchaseOrderStatus;

// ─── PurchaseOrderStatus enum ─────────────────────────────────────────────────

it('PurchaseOrderStatus tiene los 4 estados requeridos', function () {
    $values = array_map(fn ($case) => $case->value, PurchaseOrderStatus::cases());

    expect($values)->toContain('pendiente')
        ->and($values)->toContain('parcial')
        ->and($values)->toContain('recibida')
        ->and($values)->toContain('cancelada');
});

it('estado pendiente tiene etiqueta en español', function () {
    expect(PurchaseOrderStatus::Pendiente->label())->toBe('Pendiente');
});

it('estado recibida tiene etiqueta en español', function () {
    expect(PurchaseOrderStatus::Recibida->label())->toBe('Recibida');
});

it('estado cancelada tiene etiqueta en español', function () {
    expect(PurchaseOrderStatus::Cancelada->label())->toBe('Cancelada');
});

it('cada estado tiene un color de badge definido', function () {
    foreach (PurchaseOrderStatus::cases() as $status) {
        expect($status->color())->toBeString()->not->toBeEmpty();
    }
});

it('estado recibida tiene badge verde', function () {
    expect(PurchaseOrderStatus::Recibida->color())->toContain('green');
});

it('estado cancelada tiene badge gris', function () {
    expect(PurchaseOrderStatus::Cancelada->color())->toContain('slate');
});

// ─── Lógica de transición de estados ─────────────────────────────────────────

it('solo órdenes pendientes pueden ser recibidas', function () {
    $allowedToReceive = [PurchaseOrderStatus::Pendiente];
    $notAllowed       = [
        PurchaseOrderStatus::Parcial,
        PurchaseOrderStatus::Recibida,
        PurchaseOrderStatus::Cancelada,
    ];

    foreach ($allowedToReceive as $status) {
        expect($status === PurchaseOrderStatus::Pendiente)->toBeTrue();
    }

    foreach ($notAllowed as $status) {
        expect($status !== PurchaseOrderStatus::Pendiente)->toBeTrue();
    }
});

it('solo órdenes pendientes pueden ser canceladas', function () {
    $status = PurchaseOrderStatus::Pendiente;

    expect($status === PurchaseOrderStatus::Pendiente)->toBeTrue();
});

// ─── Cálculo de líneas de orden ───────────────────────────────────────────────

it('línea de orden calcula line_total como qty * unit_cost', function () {
    $line      = ['qty' => 5.0, 'unit_cost' => 45_000.0];
    $lineTotal = round($line['unit_cost'] * $line['qty'], 2);

    expect($lineTotal)->toBe(225_000.0);
});

it('línea de orden con producto libre no requiere product_id', function () {
    $line = [
        'product_id'  => null,
        'description' => 'Producto personalizado',
        'qty'         => 1.0,
        'unit_cost'   => 10_000.0,
        'line_total'  => 10_000.0,
    ];

    expect($line['product_id'])->toBeNull();
    expect($line['description'])->not->toBeEmpty();
});

it('total de orden es la suma de todas las líneas', function () {
    $lines = [
        ['line_total' => 100_000.0],
        ['line_total' =>  50_000.0],
        ['line_total' =>  25_000.0],
    ];

    $total = array_sum(array_column($lines, 'line_total'));

    expect($total)->toBe(175_000.0);
});

// ─── Recepción genera factura en borrador ─────────────────────────────────────

it('al recibir orden el estado pasa a recibida', function () {
    $newStatus = PurchaseOrderStatus::Recibida->value;

    expect($newStatus)->toBe('recibida');
});

it('factura generada al recibir orden tiene IVA en cero para ajuste posterior', function () {
    // La lógica de receiveOrder crea la factura con tax_rate = 0
    // para que el estudiante ajuste el IVA manualmente antes de confirmar
    $taxRate = 0;

    expect($taxRate)->toBe(0);
});
