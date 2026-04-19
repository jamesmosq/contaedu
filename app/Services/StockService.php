<?php

namespace App\Services;

use App\Models\Tenant\Product;
use App\Models\Tenant\StockMovement;

class StockService
{
    /**
     * Registra una entrada de inventario (compra, apertura, ajuste positivo).
     * Actualiza el saldo acumulado usando costo promedio ponderado.
     */
    public static function registrarEntrada(
        Product $product,
        float $qty,
        float $costoUnitario,
        string $referenciaTipo,
        ?int $referenciaId,
        string $fecha,
        string $descripcion = '',
        ?int $thirdId = null
    ): StockMovement {
        $ultimo = StockMovement::where('product_id', $product->id)
            ->where('modo', modoContable())
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->first();

        $saldoQtyAnterior = (float) ($ultimo?->saldo_qty ?? 0);
        $saldoValorAnterior = (float) ($ultimo?->saldo_valor ?? 0);

        $costoTotal = round($qty * $costoUnitario, 2);
        $nuevoSaldoQty = $saldoQtyAnterior + $qty;
        $nuevoSaldoValor = $saldoValorAnterior + $costoTotal;

        return StockMovement::create([
            'modo' => modoContable(),
            'product_id' => $product->id,
            'tipo' => 'entrada',
            'qty' => $qty,
            'costo_unitario' => $costoUnitario,
            'costo_total' => $costoTotal,
            'referencia_tipo' => $referenciaTipo,
            'referencia_id' => $referenciaId,
            'third_id' => $thirdId,
            'saldo_qty' => $nuevoSaldoQty,
            'saldo_valor' => $nuevoSaldoValor,
            'fecha' => $fecha,
            'descripcion' => $descripcion ?: "Entrada por {$referenciaTipo}",
        ]);
    }

    /**
     * Registra una salida de inventario (venta).
     * Usa costo promedio ponderado para valorizar la salida.
     */
    public static function registrarSalida(
        Product $product,
        float $qty,
        string $referenciaTipo,
        ?int $referenciaId,
        string $fecha,
        string $descripcion = '',
        ?int $thirdId = null
    ): StockMovement {
        $ultimo = StockMovement::where('product_id', $product->id)
            ->where('modo', modoContable())
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->first();

        $saldoQtyAnterior = (float) ($ultimo?->saldo_qty ?? 0);
        $saldoValorAnterior = (float) ($ultimo?->saldo_valor ?? 0);
        $costoUnitario = self::costoPromedio($product);
        $costoTotal = round($qty * $costoUnitario, 2);

        // En modo educativo permitimos saldo negativo para no bloquear el flujo
        $nuevoSaldoQty = $saldoQtyAnterior - $qty;
        $nuevoSaldoValor = max(0, $saldoValorAnterior - $costoTotal);

        return StockMovement::create([
            'modo' => modoContable(),
            'product_id' => $product->id,
            'tipo' => 'salida',
            'qty' => $qty,
            'costo_unitario' => $costoUnitario,
            'costo_total' => $costoTotal,
            'referencia_tipo' => $referenciaTipo,
            'referencia_id' => $referenciaId,
            'third_id' => $thirdId,
            'saldo_qty' => $nuevoSaldoQty,
            'saldo_valor' => $nuevoSaldoValor,
            'fecha' => $fecha,
            'descripcion' => $descripcion ?: "Salida por {$referenciaTipo}",
        ]);
    }

    /**
     * Retorna el stock actual (unidades disponibles) del producto en el modo activo.
     */
    public static function stockActual(Product $product): float
    {
        $ultimo = StockMovement::where('product_id', $product->id)
            ->where('modo', modoContable())
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->value('saldo_qty');

        return (float) ($ultimo ?? 0);
    }

    /**
     * Retorna el costo promedio ponderado actual del producto en el modo activo.
     * Fallback: cost_price del producto si no hay movimientos.
     */
    public static function costoPromedio(Product $product): float
    {
        $ultimo = StockMovement::where('product_id', $product->id)
            ->where('modo', modoContable())
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->first();

        if (! $ultimo || (float) $ultimo->saldo_qty <= 0) {
            return (float) $product->cost_price;
        }

        return round((float) $ultimo->saldo_valor / (float) $ultimo->saldo_qty, 4);
    }
}
