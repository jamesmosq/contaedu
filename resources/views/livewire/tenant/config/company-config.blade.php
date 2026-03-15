<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Configuración de empresa</h2>
                <p class="text-sm text-slate-500 mt-0.5">Datos básicos, régimen y facturación</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($saved)
                <div class="mb-5 p-4 bg-accent-50 border border-accent-200 rounded-xl text-accent-700 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                    Configuración guardada correctamente.
                </div>
            @endif

            <div class="bg-white rounded-xl border border-slate-200 divide-y divide-slate-100">

                {{-- Datos básicos --}}
                <div class="px-6 py-5">
                    <h3 class="text-sm font-semibold text-slate-700 mb-4">Datos de la empresa</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">NIT</label>
                                <input wire:model="nit" type="text" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                                @error('nit') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Régimen</label>
                                <select wire:model="regimen" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500">
                                    <option value="simplificado">Régimen Simplificado</option>
                                    <option value="comun">Régimen Común</option>
                                    <option value="gran_contribuyente">Gran Contribuyente</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Razón social</label>
                            <input wire:model="razon_social" type="text" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                            @error('razon_social') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
                                <input wire:model="telefono" type="text" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Correo</label>
                                <input wire:model="email" type="email" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Dirección</label>
                            <input wire:model="direccion" type="text" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                        </div>
                    </div>
                </div>

                {{-- Facturación --}}
                <div class="px-6 py-5">
                    <h3 class="text-sm font-semibold text-slate-700 mb-4">Facturación</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Prefijo factura</label>
                            <input wire:model="prefijo_factura" type="text" maxlength="5" placeholder="FV" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                            @error('prefijo_factura') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Resolución DIAN <span class="text-slate-400">(educativo)</span></label>
                            <input wire:model="resolucion_dian" type="text" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                        </div>
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="px-6 py-4 flex justify-end">
                    <button wire:click="save" class="px-5 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
                        <span wire:loading.remove wire:target="save">Guardar configuración</span>
                        <span wire:loading wire:target="save">Guardando...</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
