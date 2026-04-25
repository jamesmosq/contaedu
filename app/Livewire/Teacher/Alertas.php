<?php

namespace App\Livewire\Teacher;

use App\Models\Central\Group;
use App\Models\Central\Tenant as CentralTenant;
use App\Models\Tenant\BankAccount;
use App\Models\Tenant\CompanyConfig;
use App\Models\Tenant\Product;
use App\Models\Tenant\Third;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.teacher')]
#[Title('Alertas contables')]
class Alertas extends Component
{
    public int $groupFilter = 0;

    /** @var array<string, bool> Tipos de alerta activos en el filtro */
    public array $alertFilter = ['A1' => true, 'A2' => true, 'A3' => true, 'A4' => true, 'A5' => true, 'A6' => true];

    public bool $onlyWithAlerts = false;

    public function refresh(): void
    {
        $teacher = auth()->user();
        $key = "alertas_{$teacher->id}_{$this->groupFilter}";
        Cache::forget($key);
        $this->dispatch('notify', type: 'success', message: 'Panel de alertas actualizado.');
    }

    public function render(): mixed
    {
        $teacher = auth()->user();
        $groups = Group::where('teacher_id', $teacher->id)->orderBy('name')->get();

        $activeGroupIds = $this->groupFilter
            ? collect([$this->groupFilter])
            : $groups->pluck('id');

        $cacheKey = "alertas_{$teacher->id}_{$this->groupFilter}";

        /** @var Collection<int, array<string, mixed>> $rows */
        $rows = Cache::remember($cacheKey, 900, function () use ($activeGroupIds): Collection {
            $tenants = CentralTenant::whereIn('group_id', $activeGroupIds)
                ->where('type', 'student')
                ->where('active', true)
                ->orderBy('company_name')
                ->get();

            return $tenants->map(function (CentralTenant $tenant): array {
                $a3 = is_null($tenant->last_activity_at)
                    || $tenant->last_activity_at->lt(now()->subDays(30));

                $alerts = $tenant->run(function () use ($a3): array {
                    $a1 = DB::table('journal_entries as je')
                        ->join('journal_lines as jl', 'je.id', '=', 'jl.journal_entry_id')
                        ->groupBy('je.id')
                        ->havingRaw('ABS(SUM(jl.debit) - SUM(jl.credit)) > 0.01')
                        ->select('je.id')
                        ->exists();

                    $a2 = BankAccount::where('bloqueada', true)->exists();

                    $config = CompanyConfig::first();
                    $a4 = is_null($config) || empty($config->nit);

                    $a5 = Third::count() === 0;
                    $a6 = Product::count() === 0;

                    return compact('a1', 'a2', 'a4', 'a5', 'a6');
                });

                return [
                    'tenant_id' => $tenant->id,
                    'company_name' => $tenant->company_name,
                    'student_name' => $tenant->student_name,
                    'group_id' => $tenant->group_id,
                    'A1' => $alerts['a1'],
                    'A2' => $alerts['a2'],
                    'A3' => $a3,
                    'A4' => $alerts['a4'],
                    'A5' => $alerts['a5'],
                    'A6' => $alerts['a6'],
                ];
            });
        });

        $filtered = $rows->filter(function (array $row): bool {
            if ($this->onlyWithAlerts) {
                $hasAlert = $row['A1'] || $row['A2'] || $row['A3'] || $row['A4'] || $row['A5'] || $row['A6'];
                if (! $hasAlert) {
                    return false;
                }
            }

            return true;
        })->values();

        $summary = [
            'total' => $rows->count(),
            'con_alertas' => $rows->filter(fn ($r) => $r['A1'] || $r['A2'] || $r['A3'] || $r['A4'] || $r['A5'] || $r['A6'])->count(),
            'A1' => $rows->where('A1', true)->count(),
            'A2' => $rows->where('A2', true)->count(),
            'A3' => $rows->where('A3', true)->count(),
            'A4' => $rows->where('A4', true)->count(),
            'A5' => $rows->where('A5', true)->count(),
            'A6' => $rows->where('A6', true)->count(),
        ];

        return view('livewire.teacher.alertas', compact('groups', 'filtered', 'summary'));
    }
}
