<x-tenant-layout :title="'Factura ' . $factura->numero_completo">

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">
                    Factura Electrónica: {{ $factura->numero_completo !== 'PENDIENTE' ? $factura->numero_completo : '(Borrador)' }}
                </h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ $factura->fecha_emision->format('d \d\e F \d\e Y') }}</p>
            </div>
            <a href="{{ fe_route('index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Volver al listado</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">


            {{-- Panel de estado --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <div class="flex flex-wrap items-center gap-4 justify-between">
                    <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold {{ $factura->estado->badgeClasses() }}">
                            {{ $factura->estado->label() }}
                        </span>
                        @if($factura->codigo_respuesta_dian)
                            <span class="text-xs text-slate-500">Código DIAN: <strong>{{ $factura->codigo_respuesta_dian }}</strong></span>
                        @endif
                    </div>

                    {{-- Acciones según estado --}}
                    @if(! session('audit_mode'))
                    <div class="flex gap-2 flex-wrap">
                        @if($factura->esBorrador())
                            <form method="POST" action="{{ fe_route('destroy', $factura) }}"
                                  onsubmit="return confirm('¿Eliminar este borrador? Esta acción no se puede deshacer.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-4 py-2 bg-red-100 text-red-700 text-sm font-semibold rounded-lg hover:bg-red-200 transition">
                                    Eliminar borrador
                                </button>
                            </form>
                            <form id="form-emitir" method="POST" action="{{ fe_route('emitir', $factura) }}">
                                @csrf
                                <button type="button"
                                        onclick="confirmarEmision()"
                                        class="px-4 py-2 bg-green-700 text-white text-sm font-semibold rounded-lg hover:bg-green-600 transition">
                                    Emitir al simulador DIAN
                                </button>
                            </form>
                            <script>
                                function confirmarEmision() {
                                    Swal.fire({
                                        title: '¿Emitir factura?',
                                        html: 'Se enviará al <strong>simulador DIAN</strong>.<br>Esta acción <strong>no se puede deshacer</strong>.',
                                        icon: 'question',
                                        showCancelButton: true,
                                        confirmButtonColor: '#15803d',
                                        cancelButtonColor: '#64748b',
                                        confirmButtonText: 'Sí, emitir',
                                        cancelButtonText: 'Cancelar',
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            document.getElementById('form-emitir').submit();
                                        }
                                    });
                                }
                            </script>
                        @endif

                        @if($factura->estado->value === 'rechazada')
                            <form method="POST" action="{{ fe_route('reenviar', $factura) }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-amber-600 text-white text-sm font-semibold rounded-lg hover:bg-amber-500 transition">
                                    Reenviar al simulador
                                </button>
                            </form>
                        @endif

                        @if($factura->esValidada() && ! $factura->tieneAceptacionExpresa())
                            <button onclick="document.getElementById('modal-anular').classList.remove('hidden')"
                                    class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-500 transition">
                                Anular factura
                            </button>
                        @endif

                        @if($factura->cufe)
                            <a href="{{ fe_route('xml', $factura) }}" target="_blank"
                               class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition">
                                Ver XML
                            </a>
                            <a href="{{ fe_route('representacion', $factura) }}" target="_blank"
                               class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition">
                                Representación gráfica
                            </a>
                            <a href="{{ fe_route('pdf', $factura) }}"
                               class="px-4 py-2 bg-blue-700 text-white text-sm font-medium rounded-lg hover:bg-blue-600 transition">
                                Descargar PDF
                            </a>
                        @endif
                    </div>
                    @endif
                </div>

                @if($factura->mensaje_dian)
                    <div class="mt-4 p-3 border rounded-lg text-sm {{ $factura->estado->messageClasses() }}">
                        <strong>Mensaje DIAN:</strong> {{ $factura->mensaje_dian }}
                    </div>
                @endif

                @if($factura->cufe)
                    <div class="mt-4 p-3 bg-slate-50 border border-slate-200 rounded-lg">
                        <div class="text-xs text-slate-500 mb-1 font-semibold">CUFE (Código Único de Factura Electrónica):</div>
                        <div class="font-mono text-xs text-slate-700 break-all">{{ $factura->cufe }}</div>
                    </div>
                @endif

                @if($factura->fecha_validacion_dian)
                    <div class="mt-2 text-xs text-slate-400">
                        Validado: {{ $factura->fecha_validacion_dian->format('d/m/Y H:i:s') }}
                    </div>
                @endif
            </div>

            {{-- Datos emisor / adquirente --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl border border-slate-200 p-6">
                    <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-3">Emisor</h3>
                    <div class="text-sm space-y-1">
                        <div class="font-bold text-slate-800">{{ $factura->razon_social_emisor }}</div>
                        <div class="text-slate-600">NIT: {{ $factura->nit_emisor }}-{{ $factura->dv_emisor }}</div>
                        <div class="text-slate-500 text-xs">Resolución: {{ $factura->resolucion->numero_resolucion }}</div>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 p-6">
                    <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-3">Adquirente</h3>
                    <div class="text-sm space-y-1">
                        <div class="font-bold text-slate-800">{{ $factura->nombre_adquirente }}</div>
                        <div class="text-slate-600">{{ $factura->tipo_doc_adquirente }}: {{ $factura->num_doc_adquirente }}</div>
                        @if($factura->email_adquirente)
                            <div class="text-slate-500 text-xs">{{ $factura->email_adquirente }}</div>
                        @endif
                        @if($factura->telefono_adquirente)
                            <div class="text-slate-500 text-xs">{{ $factura->telefono_adquirente }}</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Ítems --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-sm font-semibold text-slate-800">Ítems de la Factura</h3>
                </div>
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-600">#</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-slate-600">Descripción</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-slate-600">Cantidad</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-slate-600">Precio unit.</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-slate-600">Dto%</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-slate-600">IVA</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-slate-600">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($factura->detalles as $detalle)
                        <tr>
                            <td class="px-4 py-2 text-slate-400">{{ $detalle->orden }}</td>
                            <td class="px-4 py-2">
                                <div class="font-medium text-slate-800">{{ $detalle->descripcion }}</div>
                                @if($detalle->codigo_producto)
                                    <div class="text-xs text-slate-400">Cód: {{ $detalle->codigo_producto }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right text-slate-700">{{ number_format((float)$detalle->cantidad, 2, ',', '.') }}</td>
                            <td class="px-4 py-2 text-right text-slate-700">${{ number_format((float)$detalle->precio_unitario, 0, ',', '.') }}</td>
                            <td class="px-4 py-2 text-right text-slate-500">{{ $detalle->porcentaje_descuento }}%</td>
                            <td class="px-4 py-2 text-right text-slate-500">{{ $detalle->porcentaje_iva }}% = ${{ number_format((float)$detalle->valor_iva, 0, ',', '.') }}</td>
                            <td class="px-4 py-2 text-right font-semibold text-slate-800">${{ number_format((float)$detalle->total_linea, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-slate-200 bg-slate-50">
                        <tr>
                            <td colspan="6" class="px-4 py-2 text-right text-xs font-semibold text-slate-600">Subtotal:</td>
                            <td class="px-4 py-2 text-right font-semibold">${{ number_format((float)$factura->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="6" class="px-4 py-2 text-right text-xs font-semibold text-slate-600">IVA:</td>
                            <td class="px-4 py-2 text-right font-semibold">${{ number_format((float)$factura->valor_iva, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="6" class="px-4 py-2 text-right text-base font-bold text-slate-800">Total a pagar:</td>
                            <td class="px-4 py-2 text-right text-base font-bold text-slate-800">${{ number_format((float)$factura->total, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Eventos del receptor --}}
            @if($factura->esValidada())
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h3 class="text-sm font-semibold text-slate-800 mb-4">Eventos del Receptor (RADIAN)</h3>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                    @foreach($tiposEvento as $tipo)
                        @php
                            $registrado = $factura->eventosReceptor->firstWhere('tipo_evento', $tipo);
                        @endphp
                        <div class="border rounded-lg p-3 text-center {{ $registrado ? 'bg-green-50 border-green-200' : 'bg-slate-50 border-slate-200' }}">
                            <div class="text-xs font-bold {{ $registrado ? 'text-green-800' : 'text-slate-500' }}">
                                {{ $tipo->value }}
                            </div>
                            <div class="text-xs {{ $registrado ? 'text-green-700' : 'text-slate-500' }} mt-1">
                                @switch($tipo->value)
                                    @case('030') Acuse de Recibo @break
                                    @case('032') Recibo del Bien @break
                                    @case('033') Aceptación Expresa @break
                                    @case('031') Reclamo @break
                                @endswitch
                            </div>
                            @if($registrado)
                                <div class="text-xs text-green-600 mt-1 font-semibold">✓ Registrado</div>
                            @else
                                <div class="text-xs text-slate-400 mt-1">Pendiente</div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if(! session('audit_mode') && ! $factura->esAnulada())
                <form method="POST" action="{{ fe_route('eventos.store', $factura) }}" class="flex flex-wrap gap-3 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Tipo de evento</label>
                        <select name="tipo_evento" required class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
                            @foreach($tiposEvento as $tipo)
                                @if(! $factura->eventosReceptor->firstWhere('tipo_evento', $tipo))
                                    <option value="{{ $tipo->value }}">{{ $tipo->label() }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Observaciones</label>
                        <input type="text" name="observaciones" placeholder="Opcional"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
                        Registrar evento
                    </button>
                </form>
                @endif
            </div>
            @endif

            {{-- Historial de eventos internos --}}
            @if($factura->eventos->isNotEmpty())
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h3 class="text-sm font-semibold text-slate-800 mb-4">Historial de cambios de estado</h3>
                <ol class="relative border-l border-slate-200 ml-3 space-y-4">
                    @foreach($factura->eventos as $evento)
                    <li class="ml-4">
                        <div class="absolute -left-1.5 mt-1.5 w-3 h-3 rounded-full border border-white bg-brand-600"></div>
                        <div class="text-xs text-slate-400">{{ $evento->created_at->format('d/m/Y H:i:s') }} — {{ $evento->origen }}</div>
                        <div class="text-sm text-slate-700 mt-0.5">
                            @if($evento->estado_anterior)
                                <span class="text-slate-500">{{ $evento->estado_anterior }}</span> →
                            @endif
                            <span class="font-semibold">{{ $evento->estado_nuevo }}</span>
                        </div>
                        @if($evento->descripcion)
                            <div class="text-xs text-slate-500 mt-0.5">{{ $evento->descripcion }}</div>
                        @endif
                    </li>
                    @endforeach
                </ol>
            </div>
            @endif

            {{-- Notas crédito --}}
            @if($factura->notasCredito->isNotEmpty())
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h3 class="text-sm font-semibold text-slate-800 mb-4">Notas Crédito</h3>
                @foreach($factura->notasCredito as $nota)
                <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0 text-sm">
                    <div>
                        <span class="font-mono font-semibold text-slate-800">{{ $nota->numero_completo }}</span>
                        <span class="ml-2 text-slate-500">{{ $nota->fecha_emision->format('d/m/Y') }}</span>
                    </div>
                    <div class="text-right">
                        <span class="font-semibold text-red-700">-${{ number_format((float)$nota->total, 0, ',', '.') }}</span>
                        <span class="ml-2 text-xs px-2 py-0.5 rounded-full {{ $nota->estado === 'validada' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $nota->estado }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

        </div>
    </div>

    {{-- Modal anular --}}
    <div id="modal-anular" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl w-full max-w-md p-6">
            <h3 class="text-base font-bold text-slate-800 mb-2">Anular Factura</h3>
            <p class="text-sm text-slate-600 mb-4">Se generará una <strong>nota crédito de anulación</strong> y la factura pasará a estado "Anulada". Esta acción no se puede deshacer.</p>
            <form method="POST" action="{{ fe_route('anular', $factura) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Motivo de anulación <span class="text-red-500">*</span></label>
                    <textarea name="motivo" rows="3" required
                              class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm"
                              placeholder="Ej: Error en los datos del adquirente"></textarea>
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="document.getElementById('modal-anular').classList.add('hidden')"
                            class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-500 transition">
                        Confirmar anulación
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-tenant-layout>
