<?php

namespace App\Livewire\Admin;

use App\Models\Central\SecurityLog;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class SecurityLogs extends Component
{
    use WithPagination;

    public string $filterEvent    = '';
    public string $filterUserType = '';
    public string $filterSearch   = '';
    public string $filterDate     = '';

    public function updatingFilterEvent(): void    { $this->resetPage(); }
    public function updatingFilterUserType(): void { $this->resetPage(); }
    public function updatingFilterSearch(): void   { $this->resetPage(); }
    public function updatingFilterDate(): void     { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->reset(['filterEvent', 'filterUserType', 'filterSearch', 'filterDate']);
        $this->resetPage();
    }

    public function render(): mixed
    {
        $query = SecurityLog::query()->orderByDesc('created_at');

        if ($this->filterEvent) {
            $query->where('event', $this->filterEvent);
        }

        if ($this->filterUserType) {
            $query->where('user_type', $this->filterUserType);
        }

        if ($this->filterSearch) {
            $query->where('identifier', 'ilike', '%'.$this->filterSearch.'%');
        }

        if ($this->filterDate) {
            $query->whereDate('created_at', $this->filterDate);
        }

        $logs  = $query->paginate(50);
        $stats = [
            'total'                => SecurityLog::count(),
            'login_success'        => SecurityLog::where('event', 'login_success')->count(),
            'login_failed'         => SecurityLog::where('event', 'login_failed')->count(),
            'logout'               => SecurityLog::where('event', 'logout')->count(),
            'bloqueo'              => SecurityLog::where('event', 'bloqueo')->count(),
            'actividad_sospechosa' => SecurityLog::where('event', 'actividad_sospechosa')->count(),
            'password_reset'       => SecurityLog::where('event', 'password_reset')->count(),
        ];

        return view('livewire.admin.security-logs', compact('logs', 'stats'))
            ->title('Logs de Seguridad — ContaEdu');
    }
}
