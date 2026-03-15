<?php
namespace App\Livewire\Tenant\Reportes;

use App\Models\Tenant\Account;
use App\Models\Tenant\CompanyConfig;
use App\Services\ReportService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class Index extends Component
{
    public string $report    = 'cartera';
    public string $dateFrom  = '';
    public string $dateTo    = '';
    public int    $accountId = 0;

    // Datos del reporte activo
    public mixed $reportData = null;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfYear()->toDateString();
        $this->dateTo   = now()->toDateString();
        $this->generate();
    }

    public function generate(ReportService $service = null): void
    {
        $service ??= app(ReportService::class);

        try {
            $this->reportData = match ($this->report) {
                'diario'        => $service->libroDiario($this->dateFrom, $this->dateTo),
                'mayor'         => $this->accountId ? $service->libroMayor($this->accountId, $this->dateFrom, $this->dateTo) : null,
                'comprobacion'  => $service->balanceComprobacion($this->dateFrom, $this->dateTo),
                'resultados'    => $service->estadoResultados($this->dateFrom, $this->dateTo),
                'balance'       => $service->balanceGeneral($this->dateTo),
                'cartera'       => $service->carteraPorCobrar(),
                'cxp'           => $service->cuentasPorPagar(),
                default         => null,
            };
        } catch (\Exception $e) {
            $this->reportData = null;
            session()->flash('error', $e->getMessage());
        }
    }

    public function updatedReport(): void
    {
        $this->reportData = null;
        $this->generate();
    }

    public function render(): mixed
    {
        $accounts = Account::where('level', '>=', 3)->orderBy('code')->get();
        $config   = CompanyConfig::first();

        return view('livewire.tenant.reportes.index', compact('accounts', 'config'))
            ->title('Reportes');
    }
}
