<?php

namespace App\Services;

use App\Models\Central\Exercise;
use App\Models\Central\ExerciseAssignment;
use App\Models\Central\ExerciseCompletion;
use App\Models\Central\Tenant as CentralTenant;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\JournalEntry;
use App\Models\Tenant\Payment;
use App\Models\Tenant\Product;
use App\Models\Tenant\PurchaseInvoice;
use App\Models\Tenant\Third;
use Illuminate\Support\Carbon;

class ExerciseVerificationService
{
    /**
     * Verifica si el tenant cumple las condiciones del ejercicio.
     * Retorna ['result' => 'aprobado'|'parcial'|'no_cumple', 'detail' => [...]]
     *
     * @return array{result: string, detail: array<string, mixed>}
     */
    public function verify(Exercise $exercise, CentralTenant $tenant, ExerciseAssignment $assignment): array
    {
        // Pre-cargar todo lo Central antes de run()
        $assignedAt = $assignment->assigned_at;
        $montoMinimo = $exercise->monto_minimo;
        $cuentaPuc = $exercise->cuenta_puc_requerida;

        return $tenant->run(function () use ($exercise, $assignedAt, $montoMinimo, $cuentaPuc): array {
            return match ($exercise->type) {
                'factura_venta' => $this->verifyFacturaVenta($assignedAt, $montoMinimo),
                'factura_compra' => $this->verifyFacturaCompra($assignedAt, $montoMinimo),
                'asiento_manual' => $this->verifyAsientoManual($assignedAt),
                'registro_tercero' => $this->verifyRegistroTercero($assignedAt),
                'registro_producto' => $this->verifyRegistroProducto($assignedAt, $cuentaPuc),
                'pago_proveedor' => $this->verifyPagoProveedor($assignedAt),
                default => ['result' => 'no_cumple', 'detail' => ['error' => 'Tipo desconocido']],
            };
        });
    }

    /** @return array{result: string, detail: array<string, mixed>} */
    private function verifyFacturaVenta(Carbon $assignedAt, ?string $montoMinimo): array
    {
        $query = Invoice::where('modo', 'sandbox')
            ->where('created_at', '>=', $assignedAt)
            ->where('status', '!=', 'anulada');

        $facturas = $query->get();

        if ($facturas->isEmpty()) {
            return ['result' => 'no_cumple', 'detail' => ['mensaje' => 'No hay facturas de venta sandbox creadas.']];
        }

        if ($montoMinimo && $facturas->where('total', '>=', (float) $montoMinimo)->isEmpty()) {
            $maxTotal = $facturas->max('total');

            return [
                'result' => 'parcial',
                'detail' => [
                    'mensaje' => 'Factura creada, pero el total no alcanza el monto mínimo.',
                    'monto_minimo' => $montoMinimo,
                    'maximo_encontrado' => $maxTotal,
                ],
            ];
        }

        return [
            'result' => 'aprobado',
            'detail' => ['mensaje' => 'Factura de venta sandbox creada correctamente.', 'cantidad' => $facturas->count()],
        ];
    }

    /** @return array{result: string, detail: array<string, mixed>} */
    private function verifyFacturaCompra(Carbon $assignedAt, ?string $montoMinimo): array
    {
        $facturas = PurchaseInvoice::where('modo', 'sandbox')
            ->where('created_at', '>=', $assignedAt)
            ->get();

        if ($facturas->isEmpty()) {
            return ['result' => 'no_cumple', 'detail' => ['mensaje' => 'No hay facturas de compra sandbox creadas.']];
        }

        if ($montoMinimo && $facturas->where('total', '>=', (float) $montoMinimo)->isEmpty()) {
            return [
                'result' => 'parcial',
                'detail' => ['mensaje' => 'Factura de compra creada, pero el total no alcanza el mínimo.', 'monto_minimo' => $montoMinimo],
            ];
        }

        return ['result' => 'aprobado', 'detail' => ['mensaje' => 'Factura de compra sandbox creada.', 'cantidad' => $facturas->count()]];
    }

    /** @return array{result: string, detail: array<string, mixed>} */
    private function verifyAsientoManual(Carbon $assignedAt): array
    {
        $entry = JournalEntry::where('modo', 'sandbox')
            ->where('created_at', '>=', $assignedAt)
            ->where('auto_generated', false)
            ->with('lines')
            ->first();

        if (! $entry) {
            return ['result' => 'no_cumple', 'detail' => ['mensaje' => 'No hay asientos manuales sandbox creados.']];
        }

        if (! $entry->isBalanced()) {
            return ['result' => 'parcial', 'detail' => ['mensaje' => 'Asiento creado pero no está balanceado.', 'referencia' => $entry->reference]];
        }

        return ['result' => 'aprobado', 'detail' => ['mensaje' => 'Asiento contable balanceado creado.', 'referencia' => $entry->reference]];
    }

    /** @return array{result: string, detail: array<string, mixed>} */
    private function verifyRegistroTercero(Carbon $assignedAt): array
    {
        $tercero = Third::where('created_at', '>=', $assignedAt)->first();

        if (! $tercero) {
            return ['result' => 'no_cumple', 'detail' => ['mensaje' => 'No hay terceros registrados después de la asignación.']];
        }

        return ['result' => 'aprobado', 'detail' => ['mensaje' => 'Tercero registrado correctamente.', 'nombre' => $tercero->name]];
    }

    /** @return array{result: string, detail: array<string, mixed>} */
    private function verifyRegistroProducto(Carbon $assignedAt, ?string $cuentaPuc): array
    {
        $query = Product::where('created_at', '>=', $assignedAt);

        if ($cuentaPuc) {
            $query->whereHas('account', fn ($q) => $q->where('code', $cuentaPuc));
        }

        $producto = $query->first();

        if (! $producto) {
            $msg = $cuentaPuc
                ? "No hay productos con cuenta PUC {$cuentaPuc} registrados."
                : 'No hay productos registrados después de la asignación.';

            return ['result' => 'no_cumple', 'detail' => ['mensaje' => $msg]];
        }

        return ['result' => 'aprobado', 'detail' => ['mensaje' => 'Producto registrado correctamente.', 'nombre' => $producto->name]];
    }

    /** @return array{result: string, detail: array<string, mixed>} */
    private function verifyPagoProveedor(Carbon $assignedAt): array
    {
        $pago = Payment::where('modo', 'sandbox')
            ->where('created_at', '>=', $assignedAt)
            ->first();

        if (! $pago) {
            return ['result' => 'no_cumple', 'detail' => ['mensaje' => 'No hay pagos a proveedor sandbox registrados.']];
        }

        return ['result' => 'aprobado', 'detail' => ['mensaje' => 'Pago a proveedor registrado.', 'total' => $pago->total]];
    }

    /**
     * Busca o crea el completion y corre la verificación.
     *
     * @return array{result: string, detail: array<string, mixed>}
     */
    public function submit(ExerciseAssignment $assignment, CentralTenant $tenant): array
    {
        $exercise = $assignment->exercise;
        $verification = $this->verify($exercise, $tenant, $assignment);

        ExerciseCompletion::updateOrCreate(
            ['assignment_id' => $assignment->id, 'tenant_id' => $tenant->id],
            [
                'exercise_id' => $exercise->id,
                'submitted_at' => now(),
                'result' => $verification['result'],
                'verification_detail' => $verification['detail'],
            ]
        );

        return $verification;
    }
}
