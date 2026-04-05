<?php

namespace App\Http\Controllers\Tenant\FacturacionElectronica;

use App\Enums\EstadoFacturaEnum;
use App\Enums\EventoReceptorEnum;
use App\Enums\TipoDocumentoEnum;
use App\Http\Controllers\Controller;
use App\Models\Tenant\CompanyConfig;
use App\Models\Tenant\FeFactura;
use App\Models\Tenant\FeResolucion;
use App\Models\Tenant\Product;
use App\Models\Tenant\Third;
use App\Services\FacturacionElectronica\FacturaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class FacturacionElectronicaController extends Controller
{
    public function __construct(
        private readonly FacturaService $facturaService
    ) {}

    public function index(): View
    {
        $facturas = FeFactura::with('resolucion')
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id')
            ->paginate(20);

        $resolucionActiva = FeResolucion::where('activa', true)->first();

        return view('facturacion-electronica.index', compact('facturas', 'resolucionActiva'));
    }

    public function crear(): View
    {
        $resoluciones = FeResolucion::where('activa', true)->where('activa', true)->get();
        $clientes = Third::clientes()->where('active', true)->orderBy('name')->get();
        $productos = Product::where('active', true)->orderBy('name')->get();
        $tiposDocumento = TipoDocumentoEnum::cases();

        return view('facturacion-electronica.crear', compact('resoluciones', 'clientes', 'productos', 'tiposDocumento'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'resolucion_id' => ['required', 'exists:fe_resoluciones,id'],
            'fecha_emision' => ['required', 'date'],
            'hora_emision' => ['required'],
            'tipo_doc_adquirente' => ['required', 'string'],
            'num_doc_adquirente' => ['required', 'string', 'max:20'],
            'nombre_adquirente' => ['required', 'string', 'max:255'],
            'email_adquirente' => ['required', 'email', 'max:255'],
            'telefono_adquirente' => ['nullable', 'string', 'max:20'],
            'direccion_adquirente' => ['nullable', 'string', 'max:255'],
            'medio_pago' => ['required', 'string'],
            'forma_pago' => ['required', 'string'],
            'fecha_vencimiento_pago' => ['nullable', 'date'],
            'notas' => ['nullable', 'string'],
            'lineas' => ['required', 'array', 'min:1'],
            'lineas.*.descripcion' => ['required', 'string'],
            'lineas.*.cantidad' => ['required', 'numeric', 'min:0.0001'],
            'lineas.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'lineas.*.porcentaje_iva' => ['required', 'numeric', 'in:0,5,19'],
        ]);

        // Obtener resolución y datos del emisor
        $resolucion = FeResolucion::findOrFail($request->resolucion_id);
        $config = CompanyConfig::first();

        $subtotal = 0;
        $totalIva = 0;
        $totalDescuentos = 0;

        $lineasProcesadas = [];
        foreach ($request->lineas as $i => $linea) {
            $cantidad = (float) $linea['cantidad'];
            $precioUnitario = (float) $linea['precio_unitario'];
            $pctDescuento = (float) ($linea['porcentaje_descuento'] ?? 0);
            $pctIva = (float) $linea['porcentaje_iva'];

            $valorDescuento = round($cantidad * $precioUnitario * $pctDescuento / 100, 2);
            $subtotalLinea = round($cantidad * $precioUnitario - $valorDescuento, 2);
            $valorIva = round($subtotalLinea * $pctIva / 100, 2);
            $totalLinea = $subtotalLinea + $valorIva;

            $subtotal += $subtotalLinea;
            $totalIva += $valorIva;
            $totalDescuentos += $valorDescuento;

            $lineasProcesadas[] = [
                'orden' => $i + 1,
                'producto_id' => $linea['producto_id'] ?? null,
                'codigo_producto' => $linea['codigo_producto'] ?? null,
                'descripcion' => $linea['descripcion'],
                'unidad_medida' => $linea['unidad_medida'] ?? '94',
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'porcentaje_descuento' => $pctDescuento,
                'valor_descuento' => $valorDescuento,
                'porcentaje_iva' => $pctIva,
                'valor_iva' => $valorIva,
                'subtotal_linea' => $subtotalLinea,
                'total_linea' => $totalLinea,
            ];
        }

        $total = $subtotal + $totalIva;

        // Calcular DV del NIT emisor
        $nitEmisor = preg_replace('/\D/', '', $config?->nit ?? '0');
        $dvEmisor = $this->calcularDv($nitEmisor);

        $factura = FeFactura::create([
            'resolucion_id' => $resolucion->id,
            'numero' => null, // se asignará al emitir
            'numero_completo' => 'PENDIENTE',
            'tipo_operacion' => '10',
            'fecha_emision' => $request->fecha_emision,
            'hora_emision' => $request->hora_emision.':00',
            'estado' => EstadoFacturaEnum::Borrador->value,
            'nit_emisor' => $nitEmisor,
            'dv_emisor' => $dvEmisor,
            'razon_social_emisor' => $config?->razon_social ?? 'Empresa Educativa',
            'regimen_fiscal_emisor' => '48',
            'tipo_doc_adquirente' => $request->tipo_doc_adquirente,
            'num_doc_adquirente' => $request->num_doc_adquirente,
            'nombre_adquirente' => $request->nombre_adquirente,
            'email_adquirente' => $request->email_adquirente,
            'telefono_adquirente' => $request->telefono_adquirente,
            'direccion_adquirente' => $request->direccion_adquirente,
            'cliente_id' => $request->cliente_id ?? null,
            'subtotal' => $subtotal,
            'total_descuentos' => $totalDescuentos,
            'base_iva' => $subtotal,
            'valor_iva' => $totalIva,
            'total' => $total,
            'medio_pago' => $request->medio_pago,
            'forma_pago' => $request->forma_pago,
            'fecha_vencimiento_pago' => $request->fecha_vencimiento_pago,
            'notas' => $request->notas,
            'user_id' => auth('student')->id() ?? auth()->id(),
        ]);

        foreach ($lineasProcesadas as $linea) {
            $factura->detalles()->create($linea);
        }

        return redirect()->route(...$this->feShowRoute($factura))
            ->with('success', 'Factura creada en estado borrador. Revisa los datos y emítela cuando esté lista.');
    }

    public function destroy(FeFactura $factura): RedirectResponse
    {
        if (session('audit_mode')) {
            return back()->with('error', 'No se pueden realizar acciones en modo auditoría.');
        }

        if (! $factura->esBorrador()) {
            return back()->with('error', 'Solo se pueden eliminar facturas en estado borrador.');
        }

        $factura->detalles()->delete();
        $factura->delete();

        return redirect()->route(...$this->feIndexRoute())
            ->with('success', 'Factura borrador eliminada.');
    }

    public function show(FeFactura $factura): View
    {
        $factura->load(['resolucion', 'detalles.producto', 'eventos', 'eventosReceptor', 'notasCredito']);
        $tiposEvento = EventoReceptorEnum::cases();

        return view('facturacion-electronica.detalle', compact('factura', 'tiposEvento'));
    }

    public function emitir(FeFactura $factura): RedirectResponse
    {
        if (session('audit_mode')) {
            return back()->with('error', 'No se pueden realizar acciones en modo auditoría.');
        }

        try {
            $this->facturaService->emitir($factura);

            $mensaje = $factura->fresh()->esValidada()
                ? 'Factura emitida y validada por el simulador DIAN correctamente.'
                : 'Factura emitida. El simulador DIAN rechazó el documento — ver detalles.';

            return redirect()->route(...$this->feShowRoute($factura))->with('success', $mensaje);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reenviar(FeFactura $factura): RedirectResponse
    {
        if (session('audit_mode')) {
            return back()->with('error', 'No se pueden realizar acciones en modo auditoría.');
        }

        try {
            $this->facturaService->reenviar($factura);

            return redirect()->route(...$this->feShowRoute($factura))
                ->with('success', 'Factura reenviada al simulador DIAN.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function anular(Request $request, FeFactura $factura): RedirectResponse
    {
        if (session('audit_mode')) {
            return back()->with('error', 'No se pueden realizar acciones en modo auditoría.');
        }

        $request->validate([
            'motivo' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->facturaService->anular($factura, $request->motivo);

            return redirect()->route(...$this->feShowRoute($factura))
                ->with('success', 'Factura anulada mediante nota crédito.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function verXml(FeFactura $factura): Response
    {
        if (empty($factura->xml_factura)) {
            abort(404, 'Esta factura aún no tiene XML generado.');
        }

        return response($factura->xml_factura, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function representacion(FeFactura $factura): View
    {
        $factura->load(['resolucion', 'detalles.producto']);

        return view('facturacion-electronica.representacion-grafica', compact('factura'));
    }

    public function registrarEvento(Request $request, FeFactura $factura): RedirectResponse
    {
        if (session('audit_mode')) {
            return back()->with('error', 'No se pueden realizar acciones en modo auditoría.');
        }

        $request->validate([
            'tipo_evento' => ['required', 'string'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $tipoEvento = EventoReceptorEnum::from($request->tipo_evento);
            $this->facturaService->registrarEventoReceptor($factura, $tipoEvento, $request->observaciones ?? '');

            return redirect()->route(...$this->feShowRoute($factura))
                ->with('success', "Evento {$tipoEvento->label()} registrado correctamente.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function feIndexRoute(): array
    {
        if (auth('web')->check() && ($demoId = request()->route('demoId'))) {
            return ['teacher.demo.fe.index', ['demoId' => $demoId]];
        }

        return ['student.fe.index', []];
    }

    private function feShowRoute(FeFactura $factura): array
    {
        if (auth('web')->check() && ($demoId = request()->route('demoId'))) {
            return ['teacher.demo.fe.show', ['demoId' => $demoId, 'factura' => $factura]];
        }

        return ['student.fe.show', [$factura]];
    }

    private function calcularDv(string $nit): int
    {
        $pesos = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
        $nit = str_pad($nit, 15, '0', STR_PAD_LEFT);
        $suma = 0;
        for ($i = 0; $i < 15; $i++) {
            $suma += (int) $nit[$i] * $pesos[$i];
        }
        $residuo = $suma % 11;

        return $residuo > 1 ? 11 - $residuo : $residuo;
    }
}
