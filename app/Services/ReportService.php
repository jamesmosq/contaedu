<?php

namespace App\Services;

use App\Models\Tenant\Account;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\JournalEntry;
use App\Models\Tenant\JournalLine;
use App\Models\Tenant\PurchaseInvoice;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * LIBRO DIARIO: todos los asientos en rango de fechas.
     */
    public function libroDiario(string $from, string $to): Collection
    {
        return JournalEntry::with(['lines.account'])
            ->whereBetween('date', [$from, $to])
            ->whereNull('deleted_at')
            ->orderBy('date')
            ->orderBy('id')
            ->get();
    }

    /**
     * LIBRO MAYOR: movimientos de una cuenta específica con saldo acumulado.
     */
    public function libroMayor(int $accountId, string $from, string $to): array
    {
        $account = Account::findOrFail($accountId);

        $lines = JournalLine::with('journalEntry')
            ->where('account_id', $accountId)
            ->whereHas('journalEntry', fn ($q) => $q->whereBetween('date', [$from, $to])->whereNull('deleted_at'))
            ->get()
            ->sortBy(fn ($l) => $l->journalEntry->date->format('Y-m-d').str_pad($l->journalEntry->id, 10, '0', STR_PAD_LEFT));

        $balance = 0;
        $rows = [];

        foreach ($lines as $line) {
            if ($account->nature === 'debito') {
                $balance += $line->debit - $line->credit;
            } else {
                $balance += $line->credit - $line->debit;
            }
            $rows[] = [
                'date' => $line->journalEntry->date,
                'reference' => $line->journalEntry->reference,
                'description' => $line->journalEntry->description,
                'debit' => $line->debit,
                'credit' => $line->credit,
                'balance' => $balance,
            ];
        }

        return ['account' => $account, 'rows' => $rows];
    }

    /**
     * BALANCE DE COMPROBACIÓN: todas las cuentas con movimientos en el período.
     */
    public function balanceComprobacion(string $from, string $to): Collection
    {
        $lines = JournalLine::with('account')
            ->whereHas('journalEntry', fn ($q) => $q->whereBetween('date', [$from, $to])->whereNull('deleted_at'))
            ->get()
            ->groupBy('account_id');

        $rows = collect();
        foreach ($lines as $accountId => $accountLines) {
            $account = $accountLines->first()->account;
            $totalDebit = $accountLines->sum('debit');
            $totalCredit = $accountLines->sum('credit');
            $balance = $account->nature === 'debito'
                ? $totalDebit - $totalCredit
                : $totalCredit - $totalDebit;
            $rows->push([
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'nature' => $account->nature,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'balance' => $balance,
            ]);
        }

        return $rows->sortBy('code')->values();
    }

    /**
     * ESTADO DE RESULTADOS: ingresos - costos - gastos.
     */
    public function estadoResultados(string $from, string $to): array
    {
        $lines = JournalLine::with('account')
            ->whereHas('journalEntry', fn ($q) => $q->whereBetween('date', [$from, $to])->whereNull('deleted_at'))
            ->get();

        $ingresos = $this->sumByType($lines, 'ingreso');
        $costos = $this->sumByType($lines, 'costo');
        $gastos = $this->sumByType($lines, 'gasto');

        return [
            'ingresos' => $ingresos,
            'costos' => $costos,
            'gastos' => $gastos,
            'utilidad' => $ingresos['total'] - $costos['total'] - $gastos['total'],
        ];
    }

    /**
     * BALANCE GENERAL: activos = pasivos + patrimonio.
     */
    public function balanceGeneral(string $to): array
    {
        $lines = JournalLine::with('account')
            ->whereHas('journalEntry', fn ($q) => $q->where('date', '<=', $to)->whereNull('deleted_at'))
            ->get();

        $activos = $this->sumByType($lines, 'activo');
        $pasivos = $this->sumByType($lines, 'pasivo');
        $patrimonio = $this->sumByType($lines, 'patrimonio');

        return [
            'activos' => $activos,
            'pasivos' => $pasivos,
            'patrimonio' => $patrimonio,
            'cuadra' => abs($activos['total'] - ($pasivos['total'] + $patrimonio['total'])) < 1,
        ];
    }

    /**
     * CARTERA POR COBRAR: facturas de venta emitidas pendientes con aging.
     */
    public function carteraPorCobrar(): Collection
    {
        return Invoice::with('third')
            ->where('status', 'emitida')
            ->orderBy('due_date')
            ->get()
            ->map(function ($inv) {
                $diasVencida = now()->diffInDays($inv->due_date, false);

                return [
                    'reference' => $inv->fullReference(),
                    'client' => $inv->third?->name ?? 'Sin tercero',
                    'date' => $inv->date,
                    'due_date' => $inv->due_date,
                    'total' => $inv->total,
                    'dias_vencida' => $diasVencida < 0 ? abs($diasVencida) : 0,
                    'vencida' => $diasVencida < 0,
                ];
            });
    }

    /**
     * CUENTAS POR PAGAR: facturas de compra pendientes con aging.
     */
    public function cuentasPorPagar(): Collection
    {
        return PurchaseInvoice::with('third')
            ->where('status', 'pendiente')
            ->orderBy('due_date')
            ->get()
            ->map(function ($inv) {
                $diasVencida = now()->diffInDays($inv->due_date, false);

                return [
                    'reference' => $inv->supplier_invoice_number ?? 'FC-'.str_pad($inv->id, 5, '0', STR_PAD_LEFT),
                    'supplier' => $inv->third?->name ?? 'Sin tercero',
                    'date' => $inv->date,
                    'due_date' => $inv->due_date,
                    'balance' => $inv->balance(),
                    'dias_vencida' => $diasVencida < 0 ? abs($diasVencida) : 0,
                    'vencida' => $diasVencida < 0,
                ];
            });
    }

    /**
     * LIBRO AUXILIAR DE IVA: movimientos de IVA del período para declaración bimestral/anual.
     *
     * @return array{iva_ventas: float, iva_compras: float, reteiva: float, reteica: float, saldo_dian: float, movimientos: Collection}
     */
    public function libroIva(string $from, string $to): array
    {
        $codigosIva = ['2408', '2367', '2368'];

        $lines = JournalLine::with(['account', 'journalEntry'])
            ->whereHas('account', fn ($q) => $q->whereIn('code', $codigosIva))
            ->whereHas('journalEntry', fn ($q) => $q->whereBetween('date', [$from, $to])->whereNull('deleted_at'))
            ->get()
            ->sortBy(fn ($l) => $l->journalEntry->date->format('Y-m-d').str_pad($l->journalEntry->id, 10, '0', STR_PAD_LEFT));

        $ivaVentas = 0.0; // 2408 CR — facturas de venta
        $ivaCompras = 0.0; // 2408 DR — facturas de compra (descontable)
        $reteiva = 0.0; // 2367 CR — reteiva practicada a proveedores
        $reteica = 0.0; // 2368 CR — reteica practicada a proveedores

        $movimientos = collect();

        foreach ($lines as $line) {
            $code = $line->account->code;
            $entry = $line->journalEntry;

            $tipo = match (true) {
                $code === '2408' && $line->credit > 0 => 'IVA generado (venta)',
                $code === '2408' && $line->debit > 0 => 'IVA descontable (compra)',
                $code === '2367' => 'Reteiva practicada',
                $code === '2368' => 'Reteica practicada',
                default => 'Otro',
            };

            if ($code === '2408' && $line->credit > 0) {
                $ivaVentas += $line->credit;
            } elseif ($code === '2408' && $line->debit > 0) {
                $ivaCompras += $line->debit;
            } elseif ($code === '2367') {
                $reteiva += $line->credit;
            } elseif ($code === '2368') {
                $reteica += $line->credit;
            }

            $movimientos->push([
                'date' => $entry->date,
                'reference' => $entry->reference,
                'description' => $entry->description,
                'tipo' => $tipo,
                'cuenta' => $code.' '.$line->account->name,
                'debito' => $line->debit,
                'credito' => $line->credit,
            ]);
        }

        // Saldo DIAN: IVA generado - IVA descontable - Reteiva practicada
        // Positivo = debe pagar; Negativo = saldo a favor
        $saldoDian = $ivaVentas - $ivaCompras - $reteiva;

        return [
            'iva_ventas' => $ivaVentas,
            'iva_compras' => $ivaCompras,
            'reteiva' => $reteiva,
            'reteica' => $reteica,
            'saldo_dian' => $saldoDian,
            'movimientos' => $movimientos,
        ];
    }

    // ─── Helper ─────────────────────────────────────────────────────────────

    private function sumByType(Collection $lines, string $type): array
    {
        $filtered = $lines->filter(fn ($l) => $l->account?->type === $type);

        $rows = $filtered->groupBy('account_id')->map(function ($accountLines) use ($type) {
            $account = $accountLines->first()->account;
            $debit = $accountLines->sum('debit');
            $credit = $accountLines->sum('credit');
            $balance = in_array($type, ['activo', 'costo', 'gasto'])
                ? $debit - $credit
                : $credit - $debit;

            return [
                'code' => $account->code,
                'name' => $account->name,
                'balance' => $balance,
            ];
        })->sortBy('code')->values();

        return [
            'rows' => $rows,
            'total' => $rows->sum('balance'),
        ];
    }
}
