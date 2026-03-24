<div>
    {{-- ── Hero ───────────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Contabilidad</p>
                <h1 class="font-display text-2xl font-bold text-white">Reportes contables</h1>
                <p class="text-forest-300 text-sm mt-1">Estados financieros y libros contables</p>
            </div>
            @php
                $pdfQuery = http_build_query(array_filter([
                    'report'     => $report,
                    'date_from'  => $dateFrom,
                    'date_to'    => $dateTo,
                    'account_id' => $accountId ?: null,
                ]));
                $pdfUrl = session('audit_mode')
                    ? route('teacher.auditoria.reportes.pdf', session('audit_tenant_id')) . '?' . $pdfQuery
                    : route('student.reportes.pdf') . '?' . $pdfQuery;
            @endphp
            <a href="{{ $pdfUrl }}" target="_blank"
               class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                Exportar PDF
            </a>
        </div>
    </div>

    <div class="px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto">

            {{-- Controles --}}
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-5 mb-6">
                <div class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Reporte</label>
                        <select wire:model.live="report" class="rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                            <option value="cartera">Cartera por cobrar</option>
                            <option value="cxp">Cuentas por pagar</option>
                            <option value="diario">Libro diario</option>
                            <option value="mayor">Libro mayor (cuenta)</option>
                            <option value="comprobacion">Balance de comprobación</option>
                            <option value="resultados">Estado de resultados</option>
                            <option value="balance">Balance general</option>
                            <option value="iva">Libro auxiliar de IVA</option>
                        </select>
                    </div>

                    @if(!in_array($report, ['cartera','cxp']))
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Desde</label>
                            <input wire:model="dateFrom" type="date" class="rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Hasta</label>
                            <input wire:model="dateTo" type="date" class="rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        </div>
                    @endif

                    @if($report === 'mayor')
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Cuenta</label>
                            <select wire:model="accountId" class="rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                                <option value="0">— Seleccionar —</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <button wire:click="generate" class="px-4 py-2 bg-slate-800 text-white text-sm font-semibold rounded-xl hover:bg-slate-700 transition">
                        <span wire:loading.remove wire:target="generate">Generar</span>
                        <span wire:loading wire:target="generate">Cargando...</span>
                    </button>
                </div>
            </div>

            {{-- Contenido del reporte --}}
            @if($reportData !== null)

                {{-- CARTERA POR COBRAR --}}
                @if($report === 'cartera')
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                        <div class="px-6 py-4 border-b border-cream-100">
                            <h3 class="font-semibold text-slate-800">Cartera por cobrar</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Facturas de venta emitidas pendientes de pago</p>
                        </div>
                        <table class="w-full text-sm">
                            <thead class="bg-forest-950 border-b border-forest-800">
                                <tr>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Factura</th>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Cliente</th>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Emisión</th>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Vence</th>
                                    <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Total</th>
                                    <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Días vencida</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($reportData as $row)
                                    <tr class="{{ $row['vencida'] ? 'bg-red-50' : '' }} hover:bg-slate-50">
                                        <td class="px-6 py-3 font-mono text-xs font-bold text-slate-700">{{ $row['reference'] }}</td>
                                        <td class="px-6 py-3 text-slate-700">{{ $row['client'] }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ $row['due_date'] ? \Carbon\Carbon::parse($row['due_date'])->format('d/m/Y') : '—' }}</td>
                                        <td class="px-6 py-3 text-right font-mono font-semibold text-slate-800">$ {{ number_format($row['total'], 0, ',', '.') }}</td>
                                        <td class="px-6 py-3 text-right">
                                            @if($row['vencida'])
                                                <span class="px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700">{{ $row['dias_vencida'] }} días</span>
                                            @else
                                                <span class="text-xs text-slate-400">Al día</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-6 py-10 text-center text-slate-400">No hay cartera pendiente.</td></tr>
                                @endforelse
                            </tbody>
                            @if($reportData->count() > 0)
                                <tfoot class="bg-slate-50 border-t border-slate-200">
                                    <tr>
                                        <td colspan="4" class="px-6 py-3 text-sm font-semibold text-slate-700">Total cartera</td>
                                        <td class="px-6 py-3 text-right font-mono font-bold text-slate-800">$ {{ number_format($reportData->sum('total'), 0, ',', '.') }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                @endif

                {{-- CUENTAS POR PAGAR --}}
                @if($report === 'cxp')
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                        <div class="px-6 py-4 border-b border-cream-100">
                            <h3 class="font-semibold text-slate-800">Cuentas por pagar</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Facturas de compra pendientes de pago</p>
                        </div>
                        <table class="w-full text-sm">
                            <thead class="bg-forest-950 border-b border-forest-800">
                                <tr>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Factura</th>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Proveedor</th>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Fecha</th>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Vence</th>
                                    <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Saldo</th>
                                    <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Días vencida</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($reportData as $row)
                                    <tr class="{{ $row['vencida'] ? 'bg-red-50' : '' }} hover:bg-slate-50">
                                        <td class="px-6 py-3 font-mono text-xs text-slate-600">{{ $row['reference'] }}</td>
                                        <td class="px-6 py-3 text-slate-700">{{ $row['supplier'] }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                                        <td class="px-6 py-3 text-slate-500">{{ $row['due_date'] ? \Carbon\Carbon::parse($row['due_date'])->format('d/m/Y') : '—' }}</td>
                                        <td class="px-6 py-3 text-right font-mono font-semibold text-red-700">$ {{ number_format($row['balance'], 0, ',', '.') }}</td>
                                        <td class="px-6 py-3 text-right">
                                            @if($row['vencida'])
                                                <span class="px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700">{{ $row['dias_vencida'] }} días</span>
                                            @else
                                                <span class="text-xs text-slate-400">Al día</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-6 py-10 text-center text-slate-400">No hay cuentas por pagar pendientes.</td></tr>
                                @endforelse
                            </tbody>
                            @if($reportData->count() > 0)
                                <tfoot class="bg-slate-50 border-t border-slate-200">
                                    <tr>
                                        <td colspan="4" class="px-6 py-3 text-sm font-semibold text-slate-700">Total por pagar</td>
                                        <td class="px-6 py-3 text-right font-mono font-bold text-red-700">$ {{ number_format($reportData->sum('balance'), 0, ',', '.') }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                @endif

                {{-- LIBRO DIARIO --}}
                @if($report === 'diario')
                    <div class="space-y-4">
                        @forelse($reportData as $entry)
                            <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                                <div class="px-5 py-3 bg-slate-50 border-b border-cream-100 flex items-center gap-4">
                                    <span class="font-mono text-xs font-bold text-forest-800">{{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}</span>
                                    <span class="text-xs font-semibold text-slate-600">{{ $entry->reference }}</span>
                                    <span class="text-xs text-slate-500 flex-1">{{ $entry->description }}</span>
                                    @if($entry->auto_generated)
                                        <span class="px-2 py-0.5 rounded text-xs bg-forest-50 text-forest-700 font-medium">Automático</span>
                                    @endif
                                </div>
                                <table class="w-full text-sm">
                                    <tbody class="divide-y divide-slate-50">
                                        @foreach($entry->lines as $line)
                                            <tr>
                                                <td class="px-5 py-2 font-mono text-xs text-slate-500 w-24">{{ $line->account->code }}</td>
                                                <td class="px-5 py-2 text-slate-700">{{ $line->account->name }}</td>
                                                <td class="px-5 py-2 text-xs text-slate-400">{{ $line->description }}</td>
                                                <td class="px-5 py-2 text-right font-mono text-sm {{ $line->debit > 0 ? 'text-slate-800 font-medium' : 'text-slate-300' }}">
                                                    {{ $line->debit > 0 ? '$ ' . number_format($line->debit, 0, ',', '.') : '' }}
                                                </td>
                                                <td class="px-5 py-2 text-right font-mono text-sm {{ $line->credit > 0 ? 'text-slate-800 font-medium' : 'text-slate-300' }}">
                                                    {{ $line->credit > 0 ? '$ ' . number_format($line->credit, 0, ',', '.') : '' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-slate-50 border-t border-cream-100">
                                        <tr>
                                            <td colspan="3" class="px-5 py-2 text-xs font-semibold text-slate-500 text-right">Totales:</td>
                                            <td class="px-5 py-2 text-right font-mono text-sm font-bold text-slate-700">$ {{ number_format($entry->lines->sum('debit'), 0, ',', '.') }}</td>
                                            <td class="px-5 py-2 text-right font-mono text-sm font-bold text-slate-700">$ {{ number_format($entry->lines->sum('credit'), 0, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @empty
                            <div class="bg-white rounded-2xl border border-cream-200 shadow-card px-6 py-10 text-center text-slate-400">
                                No hay asientos en el período seleccionado.
                            </div>
                        @endforelse
                    </div>
                @endif

                {{-- LIBRO MAYOR --}}
                @if($report === 'mayor' && is_array($reportData))
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                        <div class="px-6 py-4 border-b border-cream-100">
                            <h3 class="font-semibold text-slate-800">{{ $reportData['account']->code }} — {{ $reportData['account']->name }}</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Naturaleza: {{ ucfirst($reportData['account']->nature) }} | Tipo: {{ ucfirst($reportData['account']->type) }}</p>
                        </div>
                        <table class="w-full text-sm">
                            <thead class="bg-forest-950 border-b border-forest-800">
                                <tr>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Fecha</th>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Referencia</th>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Descripción</th>
                                    <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Débito</th>
                                    <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Crédito</th>
                                    <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Saldo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($reportData['rows'] as $row)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-3 text-slate-500">{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                                        <td class="px-6 py-3 font-mono text-xs text-slate-600">{{ $row['reference'] }}</td>
                                        <td class="px-6 py-3 text-slate-700">{{ $row['description'] }}</td>
                                        <td class="px-6 py-3 text-right font-mono text-sm {{ $row['debit'] > 0 ? 'text-slate-800' : 'text-slate-300' }}">{{ $row['debit'] > 0 ? '$ '.number_format($row['debit'],0,',','.') : '—' }}</td>
                                        <td class="px-6 py-3 text-right font-mono text-sm {{ $row['credit'] > 0 ? 'text-slate-800' : 'text-slate-300' }}">{{ $row['credit'] > 0 ? '$ '.number_format($row['credit'],0,',','.') : '—' }}</td>
                                        <td class="px-6 py-3 text-right font-mono text-sm font-semibold {{ $row['balance'] >= 0 ? 'text-slate-800' : 'text-red-600' }}">$ {{ number_format($row['balance'],0,',','.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-6 py-10 text-center text-slate-400">Sin movimientos en el período.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- BALANCE DE COMPROBACIÓN --}}
                @if($report === 'comprobacion')
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                        <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-slate-800">Balance de comprobación</h3>
                                <p class="text-xs text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
                            </div>
                            @php $sumD = $reportData->sum('total_debit'); $sumC = $reportData->sum('total_credit'); @endphp
                            <span class="px-3 py-1 rounded-xl text-xs font-semibold {{ abs($sumD-$sumC)<1 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ abs($sumD-$sumC)<1 ? 'Cuadrado ✓' : 'Descuadrado ✗' }}
                            </span>
                        </div>
                        <table class="w-full text-sm">
                            <thead class="bg-forest-950 border-b border-forest-800">
                                <tr>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Código</th>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Cuenta</th>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Tipo</th>
                                    <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Débitos</th>
                                    <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Créditos</th>
                                    <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase">Saldo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($reportData as $row)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-2 font-mono text-xs text-slate-500">{{ $row['code'] }}</td>
                                        <td class="px-6 py-2 text-slate-700">{{ $row['name'] }}</td>
                                        <td class="px-6 py-2 text-xs text-slate-400 capitalize">{{ $row['type'] }}</td>
                                        <td class="px-6 py-2 text-right font-mono text-sm text-slate-700">$ {{ number_format($row['total_debit'],0,',','.') }}</td>
                                        <td class="px-6 py-2 text-right font-mono text-sm text-slate-700">$ {{ number_format($row['total_credit'],0,',','.') }}</td>
                                        <td class="px-6 py-2 text-right font-mono text-sm font-semibold {{ $row['balance'] >= 0 ? 'text-slate-800' : 'text-red-600' }}">$ {{ number_format($row['balance'],0,',','.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                                <tr>
                                    <td colspan="3" class="px-6 py-3 text-sm font-bold text-slate-700">TOTALES</td>
                                    <td class="px-6 py-3 text-right font-mono font-bold text-slate-800">$ {{ number_format($sumD,0,',','.') }}</td>
                                    <td class="px-6 py-3 text-right font-mono font-bold text-slate-800">$ {{ number_format($sumC,0,',','.') }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif

                {{-- ESTADO DE RESULTADOS --}}
                @if($report === 'resultados')
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Ingresos --}}
                        <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                            <div class="px-6 py-4 border-b border-cream-100 bg-green-50">
                                <h3 class="font-semibold text-green-800">Ingresos operacionales</h3>
                            </div>
                            <table class="w-full text-sm">
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($reportData['ingresos']['rows'] as $row)
                                        <tr><td class="px-6 py-2 font-mono text-xs text-slate-500">{{ $row['code'] }}</td><td class="px-6 py-2 text-slate-700">{{ $row['name'] }}</td><td class="px-6 py-2 text-right font-mono text-slate-800">$ {{ number_format($row['balance'],0,',','.') }}</td></tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-green-50 border-t border-slate-200"><tr><td colspan="2" class="px-6 py-3 font-bold text-green-800">Total ingresos</td><td class="px-6 py-3 text-right font-mono font-bold text-green-800">$ {{ number_format($reportData['ingresos']['total'],0,',','.') }}</td></tr></tfoot>
                            </table>
                        </div>
                        {{-- Costos y gastos --}}
                        <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                            <div class="px-6 py-4 border-b border-cream-100 bg-red-50">
                                <h3 class="font-semibold text-red-800">Costos y gastos</h3>
                            </div>
                            <table class="w-full text-sm">
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($reportData['costos']['rows'] as $row)
                                        <tr><td class="px-6 py-2 font-mono text-xs text-slate-500">{{ $row['code'] }}</td><td class="px-6 py-2 text-slate-700">{{ $row['name'] }}</td><td class="px-6 py-2 text-right font-mono text-slate-800">$ {{ number_format($row['balance'],0,',','.') }}</td></tr>
                                    @endforeach
                                    @foreach($reportData['gastos']['rows'] as $row)
                                        <tr class="bg-orange-50/30"><td class="px-6 py-2 font-mono text-xs text-slate-500">{{ $row['code'] }}</td><td class="px-6 py-2 text-slate-700">{{ $row['name'] }}</td><td class="px-6 py-2 text-right font-mono text-slate-800">$ {{ number_format($row['balance'],0,',','.') }}</td></tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-red-50 border-t border-slate-200"><tr><td colspan="2" class="px-6 py-3 font-bold text-red-800">Total costos + gastos</td><td class="px-6 py-3 text-right font-mono font-bold text-red-800">$ {{ number_format($reportData['costos']['total']+$reportData['gastos']['total'],0,',','.') }}</td></tr></tfoot>
                            </table>
                        </div>
                        {{-- Utilidad --}}
                        <div class="lg:col-span-2 bg-white rounded-2xl border-2 {{ $reportData['utilidad'] >= 0 ? 'border-green-300' : 'border-red-300' }} p-6 flex items-center justify-between">
                            <span class="text-lg font-bold {{ $reportData['utilidad'] >= 0 ? 'text-green-800' : 'text-red-700' }}">{{ $reportData['utilidad'] >= 0 ? 'Utilidad del ejercicio' : 'Pérdida del ejercicio' }}</span>
                            <span class="text-2xl font-mono font-bold {{ $reportData['utilidad'] >= 0 ? 'text-green-700' : 'text-red-700' }}">$ {{ number_format(abs($reportData['utilidad']),0,',','.') }}</span>
                        </div>
                    </div>
                @endif

                {{-- BALANCE GENERAL --}}
                @if($report === 'balance')
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Activos --}}
                        <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                            <div class="px-6 py-4 border-b border-cream-100 bg-blue-50">
                                <h3 class="font-semibold text-blue-800">ACTIVOS</h3>
                            </div>
                            <table class="w-full text-sm">
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($reportData['activos']['rows'] as $row)
                                        <tr><td class="px-6 py-2 font-mono text-xs text-slate-500">{{ $row['code'] }}</td><td class="px-6 py-2 text-slate-700">{{ $row['name'] }}</td><td class="px-6 py-2 text-right font-mono text-slate-800">$ {{ number_format($row['balance'],0,',','.') }}</td></tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-blue-50 border-t-2 border-blue-200"><tr><td colspan="2" class="px-6 py-3 font-bold text-blue-800">Total Activos</td><td class="px-6 py-3 text-right font-mono font-bold text-blue-800">$ {{ number_format($reportData['activos']['total'],0,',','.') }}</td></tr></tfoot>
                            </table>
                        </div>
                        {{-- Pasivos + Patrimonio --}}
                        <div class="space-y-4">
                            <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                                <div class="px-6 py-4 border-b border-cream-100 bg-red-50">
                                    <h3 class="font-semibold text-red-800">PASIVOS</h3>
                                </div>
                                <table class="w-full text-sm">
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($reportData['pasivos']['rows'] as $row)
                                            <tr><td class="px-6 py-2 font-mono text-xs text-slate-500">{{ $row['code'] }}</td><td class="px-6 py-2 text-slate-700">{{ $row['name'] }}</td><td class="px-6 py-2 text-right font-mono text-slate-800">$ {{ number_format($row['balance'],0,',','.') }}</td></tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-red-50 border-t border-slate-200"><tr><td colspan="2" class="px-6 py-3 font-bold text-red-800">Total Pasivos</td><td class="px-6 py-3 text-right font-mono font-bold text-red-800">$ {{ number_format($reportData['pasivos']['total'],0,',','.') }}</td></tr></tfoot>
                                </table>
                            </div>
                            <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                                <div class="px-6 py-4 border-b border-cream-100 bg-purple-50">
                                    <h3 class="font-semibold text-purple-800">PATRIMONIO</h3>
                                </div>
                                <table class="w-full text-sm">
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($reportData['patrimonio']['rows'] as $row)
                                            <tr><td class="px-6 py-2 font-mono text-xs text-slate-500">{{ $row['code'] }}</td><td class="px-6 py-2 text-slate-700">{{ $row['name'] }}</td><td class="px-6 py-2 text-right font-mono text-slate-800">$ {{ number_format($row['balance'],0,',','.') }}</td></tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-purple-50 border-t border-slate-200"><tr><td colspan="2" class="px-6 py-3 font-bold text-purple-800">Total Patrimonio</td><td class="px-6 py-3 text-right font-mono font-bold text-purple-800">$ {{ number_format($reportData['patrimonio']['total'],0,',','.') }}</td></tr></tfoot>
                                </table>
                            </div>
                            <div class="bg-white rounded-2xl border-2 {{ $reportData['cuadra'] ? 'border-green-300' : 'border-red-400' }} p-4 flex items-center justify-between">
                                <span class="font-semibold text-slate-700">Pasivos + Patrimonio</span>
                                <div class="text-right">
                                    <p class="font-mono font-bold text-slate-800">$ {{ number_format($reportData['pasivos']['total']+$reportData['patrimonio']['total'],0,',','.') }}</p>
                                    <p class="text-xs {{ $reportData['cuadra'] ? 'text-green-600' : 'text-red-600' }} font-semibold">{{ $reportData['cuadra'] ? '✓ Cuadra con activos' : '✗ No cuadra' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- LIBRO AUXILIAR DE IVA --}}
                @if($report === 'iva' && is_array($reportData))
                    {{-- Tarjetas resumen --}}
                    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                        <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-4 text-center">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">IVA generado</p>
                            <p class="text-xl font-mono font-bold text-emerald-700">$ {{ number_format($reportData['iva_ventas'], 0, ',', '.') }}</p>
                            <p class="text-xs text-slate-400 mt-1">Cuenta 2408 CR (ventas)</p>
                        </div>
                        <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-4 text-center">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">IVA descontable</p>
                            <p class="text-xl font-mono font-bold text-blue-700">$ {{ number_format($reportData['iva_compras'], 0, ',', '.') }}</p>
                            <p class="text-xs text-slate-400 mt-1">Cuenta 2408 DR (compras)</p>
                        </div>
                        <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-4 text-center">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Reteiva practicada</p>
                            <p class="text-xl font-mono font-bold text-amber-700">$ {{ number_format($reportData['reteiva'], 0, ',', '.') }}</p>
                            <p class="text-xs text-slate-400 mt-1">Cuenta 2367</p>
                        </div>
                        <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-4 text-center">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Reteica practicada</p>
                            <p class="text-xl font-mono font-bold text-purple-700">$ {{ number_format($reportData['reteica'], 0, ',', '.') }}</p>
                            <p class="text-xs text-slate-400 mt-1">Cuenta 2368</p>
                        </div>
                        @php $saldo = $reportData['saldo_dian']; @endphp
                        <div class="bg-white rounded-2xl border-2 {{ $saldo > 0 ? 'border-red-300' : ($saldo < 0 ? 'border-green-300' : 'border-cream-200') }} p-4 text-center">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Saldo DIAN</p>
                            <p class="text-xl font-mono font-bold {{ $saldo > 0 ? 'text-red-700' : ($saldo < 0 ? 'text-green-700' : 'text-slate-600') }}">
                                $ {{ number_format(abs($saldo), 0, ',', '.') }}
                            </p>
                            <p class="text-xs font-semibold mt-1 {{ $saldo > 0 ? 'text-red-500' : ($saldo < 0 ? 'text-green-600' : 'text-slate-400') }}">
                                {{ $saldo > 0 ? 'A pagar' : ($saldo < 0 ? 'Saldo a favor' : 'En cero') }}
                            </p>
                        </div>
                    </div>

                    {{-- Tabla de movimientos --}}
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                        <div class="px-6 py-4 border-b border-cream-100">
                            <h3 class="font-semibold text-slate-800">Movimientos de IVA</h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
                            </p>
                        </div>
                        <table class="w-full text-sm">
                            <thead class="bg-forest-950 border-b border-forest-800">
                                <tr>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-forest-300 uppercase">Fecha</th>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-forest-300 uppercase">Referencia</th>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-forest-300 uppercase">Descripción</th>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-forest-300 uppercase">Tipo</th>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-forest-300 uppercase">Cuenta</th>
                                    <th class="text-right px-4 py-3 text-xs font-semibold text-forest-300 uppercase">Débito</th>
                                    <th class="text-right px-4 py-3 text-xs font-semibold text-forest-300 uppercase">Crédito</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($reportData['movimientos'] as $mov)
                                    @php
                                        $rowColor = match(true) {
                                            str_contains($mov['tipo'], 'generado')    => 'bg-emerald-50/40',
                                            str_contains($mov['tipo'], 'descontable') => 'bg-blue-50/40',
                                            str_contains($mov['tipo'], 'Reteiva')     => 'bg-amber-50/40',
                                            str_contains($mov['tipo'], 'Reteica')     => 'bg-purple-50/40',
                                            default                                   => '',
                                        };
                                    @endphp
                                    <tr class="{{ $rowColor }} hover:bg-slate-50">
                                        <td class="px-4 py-2 text-slate-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($mov['date'])->format('d/m/Y') }}</td>
                                        <td class="px-4 py-2 font-mono text-xs text-slate-600">{{ $mov['reference'] }}</td>
                                        <td class="px-4 py-2 text-slate-600 text-xs">{{ $mov['description'] }}</td>
                                        <td class="px-4 py-2">
                                            <span class="px-2 py-0.5 rounded text-xs font-medium
                                                @if(str_contains($mov['tipo'], 'generado')) bg-emerald-100 text-emerald-700
                                                @elseif(str_contains($mov['tipo'], 'descontable')) bg-blue-100 text-blue-700
                                                @elseif(str_contains($mov['tipo'], 'Reteiva')) bg-amber-100 text-amber-700
                                                @elseif(str_contains($mov['tipo'], 'Reteica')) bg-purple-100 text-purple-700
                                                @else bg-slate-100 text-slate-600 @endif">
                                                {{ $mov['tipo'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 font-mono text-xs text-slate-500">{{ $mov['cuenta'] }}</td>
                                        <td class="px-4 py-2 text-right font-mono text-sm {{ $mov['debito'] > 0 ? 'text-slate-800' : 'text-slate-300' }}">
                                            {{ $mov['debito'] > 0 ? '$ '.number_format($mov['debito'], 0, ',', '.') : '—' }}
                                        </td>
                                        <td class="px-4 py-2 text-right font-mono text-sm {{ $mov['credito'] > 0 ? 'text-slate-800' : 'text-slate-300' }}">
                                            {{ $mov['credito'] > 0 ? '$ '.number_format($mov['credito'], 0, ',', '.') : '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-10 text-center text-slate-400">
                                            No hay movimientos de IVA en el período seleccionado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

            @else
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card px-6 py-12 text-center text-slate-400">
                    Configura los filtros y haz clic en <strong>Generar</strong>.
                </div>
            @endif

        </div>
    </div>
</div>
