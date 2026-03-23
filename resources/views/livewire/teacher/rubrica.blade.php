<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Rúbrica de calificación</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ $tenant->student_name }} — {{ $tenant->company_name }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('teacher.auditar.start', $tenant->id) }}" class="px-3 py-1.5 border border-brand-200 text-brand-700 text-xs font-medium rounded-lg hover:bg-brand-50 transition">
                    Auditar empresa →
                </a>
                <a href="{{ route('teacher.dashboard') }}" class="px-3 py-1.5 border border-slate-200 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 transition">
                    ← Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="font-semibold text-slate-800">Notas por módulo (1.0 – 5.0)</h3>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($modules as $key => $label)
                        <div class="px-6 py-5">
                            <div class="flex items-start justify-between gap-6">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-slate-700 mb-2">{{ $label }}</label>
                                    <input wire:model.live="notes.{{ $key }}" type="text" placeholder="Observaciones (opcional)"
                                        class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                                </div>
                                <div class="w-28 shrink-0">
                                    <label class="block text-sm font-medium text-slate-700 mb-2 text-center">Nota</label>
                                    <input wire:model.live="scores.{{ $key }}" type="number" min="1.0" max="5.0" step="0.1"
                                        placeholder="—"
                                        class="block w-full rounded-lg border-slate-200 text-sm text-center focus:ring-brand-500 focus:border-brand-500
                                            {{ $scores[$key] !== '' ? (floatval($scores[$key]) >= 3.0 ? 'text-accent-700 font-semibold' : 'text-red-600 font-semibold') : '' }}" />
                                    @error("scores.{$key}") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Promedio --}}
                <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-700">Promedio ponderado</p>
                        <p class="text-xs text-slate-400">Promedio de los módulos calificados</p>
                    </div>
                    <div class="text-right">
                        @if($promedio !== null)
                            <p class="text-2xl font-bold {{ $promedio >= 3.0 ? 'text-accent-700' : 'text-red-600' }}">
                                {{ number_format($promedio, 1) }}
                            </p>
                        @else
                            <p class="text-2xl font-bold text-slate-300">—</p>
                        @endif
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 flex justify-end">
                    <button wire:click="save" wire:loading.attr="disabled"
                        class="px-5 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="save">Guardar notas</span>
                        <span wire:loading wire:target="save">Guardando…</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
