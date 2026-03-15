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
            ->whereHas('journalEntry', fn($q) => $q->whereBetween('date', [$from, $to])->whereNull('deleted_at'))
            ->get()
            ->sortBy(fn($l) => $l->journalEntry->date->format('Y-m-d') . str_pad($l->journalEntry->id, 10, '0', STR_PAD_LEFT));

        $balance = 0;
        $rows    = [];

        foreach ($lines as $line) {
            if ($account->nature === 'debito') {
                $balance += $line->debit - $line->credit;
            } else {
                $balance += $line->credit - $line->debit;
            }
            $rows[] = [
                'date'        => $line->journalEntry->date,
                'reference'   => $line->journalEntry->reference,
                'description' => $line->journalEntry->description,
                'debit'       => $line->debit,
                'credit'      => $line->credit,
                'balance'     => $balance,
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
            ->whereHas('journalEntry', fn($q) => $q->whereBetween('date', [$from, $to])->whereNull('deleted_at'))
            ->get()
            ->groupBy('account_id');

        $rows = collect();
        foreach ($lines as $accountId => $accountLines) {
            $account     = $accountLines->first()->account;
            $totalDebit  = $accountLines->sum('debit');
            $totalCredit = $accountLines->sum('credit');
            $balance     = $account->nature === 'debito'
                ? $totalDebit - $totalCredit
                : $totalCredit - $totalDebit;
            $rows->push([
                'code'         => $account->code,
                'name'         => $account->name,
                'type'         => $account->type,
                'nature'       => $account->nature,
                'total_debit'  => $totalDebit,
                'total_credit' => $totalCredit,
                'balance'      => $balance,
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
            ->whereHas('journalEntry', fn($q) => $q->whereBetween('date', [$from, $to])->whereNull('deleted_at'))
            ->get();

        $ingresos = $this->sumByType($lines, 'ingreso');
        $costos   = $this->sumByType($lines, 'costo');
        $gastos   = $this->sumByType($lines, 'gasto');

        return [
            'ingresos'  => $ingresos,
            'costos'    => $costos,
            'gastos'    => $gastos,
            'utilidad'  => $ingresos['total'] - $costos['total'] - $gastos['total'],
        ];
    }

    /**
     * BALANCE GENERAL: activos = pasivos + patrimonio.
     */
    public function balanceGeneral(string $to): array
    {
        $lines = JournalLine::with('account')
            ->whereHas('journalEntry', fn($q) => $q->where('date', '<=', $to)->whereNull('deleted_at'))
            ->get();

        $activos    = $this->sumByType($lines, 'activo');
        $pasivos    = $this->sumByType($lines, 'pasivo');
        $patrimonio = $this->sumByType($lines, 'patrimonio');

        return [
            'activos'    => $activos,
            'pasivos'    => $pasivos,
            'patrimonio' => $patrimonio,
            'cuadra'     => abs($activos['total'] - ($pasivos['total'] + $patrimonio['total'])) < 1,
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
                    'reference'    => $inv->fullReference(),
                    'client'       => $inv->third->name,
                    'date'         => $inv->date,
                    'due_date'     => $inv->due_date,
                    'total'        => $inv->total,
                    'dias_vencida' => $diasVencida < 0 ? abs($diasVencida) : 0,
                    'vencida'      => $diasVencida < 0,
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
                    'reference'    => $inv->supplier_invoice_number ?? 'FC-' . str_pad($inv->id, 5, '0', STR_PAD_LEFT),
                    'supplier'     => $inv->third->name,
                    'date'         => $inv->date,
                    'due_date'     => $inv->due_date,
                    'balance'      => $inv->balance(),
                    'dias_vencida' => $diasVencida < 0 ? abs($diasVencida) : 0,
                    'vencida'      => $diasVencida < 0,
                ];
            });
    }

    // ─── Helper ─────────────────────────────────────────────────────────────

    private function sumByType(Collection $lines, string $type): array
    {
        $filtered = $lines->filter(fn($l) => $l->account?->type === $type);

        $rows = $filtered->groupBy('account_id')->map(function ($accountLines) use ($type) {
            $account = $accountLines->first()->account;
            $debit   = $accountLines->sum('debit');
            $credit  = $accountLines->sum('credit');
            $balance = in_array($type, ['activo', 'costo', 'gasto'])
                ? $debit - $credit
                : $credit - $debit;
            return [
                'code'    => $account->code,
                'name'    => $account->name,
                'balance' => $balance,
            ];
        })->sortBy('code')->values();

        return [
            'rows'  => $rows,
            'total' => $rows->sum('balance'),
        ];
    }
}
