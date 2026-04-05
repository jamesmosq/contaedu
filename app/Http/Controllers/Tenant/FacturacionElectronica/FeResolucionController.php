<?php

namespace App\Http\Controllers\Tenant\FacturacionElectronica;

use App\Http\Controllers\Controller;
use App\Models\Tenant\FeResolucion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FeResolucionController extends Controller
{
    public function index(): View
    {
        $resoluciones = FeResolucion::orderByDesc('created_at')->get();

        return view('facturacion-electronica.resoluciones.index', compact('resoluciones'));
    }

    public function create(): View
    {
        return view('facturacion-electronica.resoluciones.crear');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'numero_resolucion' => ['required', 'string', 'max:50'],
            'prefijo' => ['nullable', 'string', 'max:10'],
            'numero_desde' => ['required', 'integer', 'min:1'],
            'numero_hasta' => ['required', 'integer', 'min:1', 'gte:numero_desde'],
            'fecha_desde' => ['required', 'date'],
            'fecha_hasta' => ['required', 'date', 'after_or_equal:fecha_desde'],
            'clave_tecnica' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string'],
        ], [
            'numero_hasta.gte' => 'El número final debe ser mayor o igual al número inicial.',
            'fecha_hasta.after_or_equal' => 'La fecha de vencimiento debe ser posterior a la fecha de inicio.',
        ]);

        // Desactivar las demás resoluciones activas
        FeResolucion::where('activa', true)->update(['activa' => false]);

        FeResolucion::create([
            ...$datos,
            'numero_actual' => $datos['numero_desde'],
            'clave_tecnica' => $datos['clave_tecnica'] ?? Str::uuid()->toString(),
            'ambiente' => '02',
            'activa' => true,
        ]);

        return redirect()->route(...$this->resolucionesIndexRoute())
            ->with('success', 'Resolución registrada correctamente.');
    }

    public function show(FeResolucion $resolucion): View
    {
        return view('facturacion-electronica.resoluciones.index', compact('resolucion'));
    }

    public function edit(FeResolucion $resolucion): View
    {
        return view('facturacion-electronica.resoluciones.crear', compact('resolucion'));
    }

    public function update(Request $request, FeResolucion $resolucion): RedirectResponse
    {
        $datos = $request->validate([
            'numero_resolucion' => ['required', 'string', 'max:50'],
            'prefijo' => ['nullable', 'string', 'max:10'],
            'numero_desde' => ['required', 'integer', 'min:1'],
            'numero_hasta' => ['required', 'integer', 'min:1', 'gte:numero_desde'],
            'fecha_desde' => ['required', 'date'],
            'fecha_hasta' => ['required', 'date', 'after_or_equal:fecha_desde'],
            'notas' => ['nullable', 'string'],
        ]);

        $resolucion->update($datos);

        return redirect()->route(...$this->resolucionesIndexRoute())
            ->with('success', 'Resolución actualizada.');
    }

    private function resolucionesIndexRoute(): array
    {
        if (auth('web')->check() && ($demoId = request()->route('demoId'))) {
            return ['teacher.demo.fe.resoluciones.index', ['demoId' => $demoId]];
        }

        return ['student.fe.resoluciones.index', []];
    }
}
