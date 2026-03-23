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

                        {{-- CIIU --}}
                        <div x-data="{
                            search: '',
                            open: false,
                            codes: {{ $ciiuCodes->map(fn($c) => ['code' => $c->code, 'name' => $c->name, 'label' => $c->code.' — '.Str::limit($c->name, 70)])->toJson() }},
                            get filtered() {
                                if (!this.search) return this.codes.slice(0, 10);
                                const q = this.search.toLowerCase();
                                return this.codes.filter(c => c.code.includes(q) || c.name.toLowerCase().includes(q)).slice(0, 15);
                            },
                            select(code, name) {
                                $wire.set('ciiu_code', code);
                                $wire.set('ciiu_description', name);
                                this.search = code + ' — ' + name.substring(0, 60);
                                this.open = false;
                            },
                            init() {
                                const current = '{{ $ciiu_code }}';
                                if (current) {
                                    const found = this.codes.find(c => c.code === current);
                                    if (found) this.search = found.code + ' — ' + found.name.substring(0, 60);
                                }
                            }
                        }" class="relative">
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Código CIIU — Actividad económica
                                <span class="text-slate-400 font-normal">(opcional)</span>
                            </label>
                            <input
                                type="text"
                                x-model="search"
                                @focus="open = true"
                                @click.outside="open = false"
                                @input="open = true"
                                placeholder="Buscar por código o nombre de actividad..."
                                class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500"
                            />
                            <div x-show="open && filtered.length > 0" x-cloak
                                class="absolute z-20 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                                <template x-for="item in filtered" :key="item.code">
                                    <button type="button"
                                        @click="select(item.code, item.name)"
                                        class="w-full text-left px-4 py-2 hover:bg-slate-50 text-sm border-b border-slate-50 last:border-0">
                                        <span class="font-mono text-xs font-bold text-brand-700" x-text="item.code"></span>
                                        <span class="text-slate-600 ml-2" x-text="item.name.substring(0, 80) + (item.name.length > 80 ? '...' : '')"></span>
                                    </button>
                                </template>
                            </div>
                            @if($ciiu_code)
                                <div class="mt-1.5 flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded bg-brand-50 text-brand-700 font-mono text-xs font-bold">{{ $ciiu_code }}</span>
                                    <span class="text-xs text-slate-500">{{ Str::limit($ciiu_description, 90) }}</span>
                                    <button type="button" wire:click="$set('ciiu_code', '')" class="text-slate-300 hover:text-red-400 text-xs ml-auto">✕ Quitar</button>
                                </div>
                            @endif
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
