<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\DashboardController;
use App\Models\Central\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DemoController extends Controller
{
    /** Docente entra a su empresa demo (acceso completo de escritura). */
    public function enter(string $demoId): RedirectResponse
    {
        $teacher = auth()->user();
        $centralConn = config('tenancy.database.central_connection', 'pgsql');

        $demo = Tenant::on($centralConn)
            ->where('id', $demoId)
            ->where('type', 'demo')
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        session([
            'demo_mode' => true,
            'demo_tenant_id' => $demo->id,
            'demo_company_name' => $demo->company_name,
        ]);

        return redirect()->route('teacher.demo.dashboard', $demoId);
    }

    /** Docente sale de la empresa demo y vuelve a su panel. */
    public function exit(): RedirectResponse
    {
        session()->forget(['demo_mode', 'demo_tenant_id', 'demo_company_name']);

        if (tenancy()->initialized) {
            tenancy()->end();
        }

        return redirect()->route('teacher.demos');
    }

    /** Dashboard de la empresa demo (la tenancy ya fue inicializada por el middleware). */
    public function dashboard(string $demoId): View
    {
        $tenant = tenancy()->tenant;
        abort_if(! $tenant, 403, 'Tenancy no inicializada.');

        return app(DashboardController::class)->index();
    }
}
