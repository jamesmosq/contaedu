<?php

namespace App\Livewire\Teacher;

use App\Models\Tenant\Invoice;
use App\Models\Tenant\JournalLine;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Comparativo extends Component
{
    public function render(): mixed
    {
        $teacher = auth()->user();
        $groups  = $teacher->teacherGroups()->with(['institution', 'tenants'])->get();

        $rows = [];
        foreach ($groups as $group) {
            foreach ($group->tenants as $tenant) {
                $data = $tenant->run(function () {
                    $ventasCount  = Invoice::where('type', 'venta')->where('status', 'emitida')->count();
                    $ventasTotal  = (float) Invoice::where('type', 'venta')->where('status', 'emitida')->sum('total');
                    $comprasCount = Invoice::where('type', 'compra')->where('status', 'pagada')->count();
                    $comprasTotal = (float) Invoice::where('type', 'compra')->where('status', 'pagada')->sum('total');

                    // Verificar si balance cuadra: sum(debit) == sum(credit) en journal_lines
                    $totalDebit  = (float) JournalLine::sum('debit');
                    $totalCredit = (float) JournalLine::sum('credit');
                    $balanced    = abs($totalDebit - $totalCredit) < 0.02;

                    return compact('ventasCount', 'ventasTotal', 'comprasCount', 'comprasTotal', 'balanced', 'totalDebit');
                });

                $rows[] = [
                    'tenant'  => $tenant,
                    'group'   => $group,
                    'data'    => $data,
                ];
            }
        }

        return view('livewire.teacher.comparativo', compact('groups', 'rows'))
            ->title('Panel comparativo');
    }
}
