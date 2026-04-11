<div>

    {{-- ── Hero ───────────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-2xl mx-auto">
            <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Maestros contables</p>
            <h1 class="font-display text-2xl font-bold text-white">Configuración de empresa</h1>
            <p class="text-forest-300 text-sm mt-1">Datos básicos, régimen tributario y facturación</p>
        </div>
    </div>

    <div class="px-6 py-8 lg:px-10">
        <div class="max-w-2xl mx-auto">

            <div class="bg-white rounded-2xl border border-cream-200 shadow-card divide-y divide-cream-100">

                {{-- Datos básicos --}}
                <div class="px-6 py-5">
                    <h3 class="text-sm font-semibold text-slate-700 mb-4">Datos de la empresa</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">NIT</label>
                            <input wire:model="nit" type="text" inputmode="numeric" pattern="[0-9\-]+"
                                placeholder="ej: 900123456-7"
                                @disabled(!$isEditing)
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500 disabled:bg-cream-50 disabled:text-slate-400 disabled:cursor-not-allowed" />
                            @if($isEditing)
                                <p class="text-slate-400 text-xs mt-1">Incluye el dígito de verificación con guión. Ejemplo: 900123456-7</p>
                            @endif
                            @error('nit') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Régimen tributario --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                Régimen tributario <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <button type="button"
                                    @if(!$isEditing) disabled @endif
                                    wire:click="$set('regimen', 'responsable_iva')"
                                    class="flex-1 py-2.5 px-4 rounded-xl text-sm font-medium border transition
                                        {{ $regimen === 'responsable_iva'
                                            ? 'bg-forest-800 text-white border-forest-800'
                                            : 'bg-white text-slate-600 border-cream-200 hover:border-forest-400 disabled:opacity-50 disabled:cursor-not-allowed' }}">
                                    Responsable de IVA
                                </button>
                                <button type="button"
                                    @if(!$isEditing) disabled @endif
                                    wire:click="$set('regimen', 'no_responsable_iva')"
                                    class="flex-1 py-2.5 px-4 rounded-xl text-sm font-medium border transition
                                        {{ $regimen === 'no_responsable_iva'
                                            ? 'bg-forest-800 text-white border-forest-800'
                                            : 'bg-white text-slate-600 border-cream-200 hover:border-forest-400 disabled:opacity-50 disabled:cursor-not-allowed' }}">
                                    No responsable de IVA
                                </button>
                            </div>
                            @error('regimen') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                            {{-- Indicador F. Electrónica --}}
                            @if($regimen === 'responsable_iva')
                                <div class="mt-2 flex items-center gap-2 px-3 py-2 bg-green-50 border border-green-200 rounded-xl">
                                    <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/>
                                    </svg>
                                    <span class="text-xs font-medium text-green-800">Facturación Electrónica habilitada — requerida para este régimen</span>
                                </div>
                            @else
                                <div class="mt-2 flex items-center gap-2 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    <span class="text-xs text-slate-500">Facturación Electrónica no disponible para este régimen</span>
                                </div>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Razón social</label>
                            <input wire:model="razon_social" type="text"
                                @disabled(!$isEditing)
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500 disabled:bg-cream-50 disabled:text-slate-400 disabled:cursor-not-allowed" />
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
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                Código CIIU — Actividad económica
                                <span class="text-slate-400 font-normal">(opcional)</span>
                            </label>
                            <input
                                type="text"
                                x-model="search"
                                @focus="if ($wire.isEditing) open = true"
                                @click.outside="open = false"
                                @input="if ($wire.isEditing) open = true"
                                :disabled="!$wire.isEditing"
                                placeholder="Buscar por código o nombre de actividad…"
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500 disabled:bg-cream-50 disabled:text-slate-400 disabled:cursor-not-allowed"
                            />
                            <div x-show="open && filtered.length > 0 && $wire.isEditing" x-cloak
                                class="absolute z-20 mt-1 w-full bg-white border border-cream-200 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                                <template x-for="item in filtered" :key="item.code">
                                    <button type="button"
                                        @click="select(item.code, item.name)"
                                        class="w-full text-left px-4 py-2 hover:bg-cream-50 text-sm border-b border-cream-50 last:border-0">
                                        <span class="font-mono text-xs font-bold text-forest-700" x-text="item.code"></span>
                                        <span class="text-slate-600 ml-2" x-text="item.name.substring(0, 80) + (item.name.length > 80 ? '...' : '')"></span>
                                    </button>
                                </template>
                            </div>
                            @if($ciiu_code)
                                <div class="mt-1.5 flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-lg bg-forest-50 text-forest-700 font-mono text-xs font-bold border border-forest-100">{{ $ciiu_code }}</span>
                                    <span class="text-xs text-slate-500">{{ Str::limit($ciiu_description, 90) }}</span>
                                    @if($isEditing)
                                        <button type="button" wire:click="$set('ciiu_code', '')" class="text-slate-300 hover:text-red-400 text-xs ml-auto">✕ Quitar</button>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Sector empresarial --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                Sector empresarial <span class="text-red-500">*</span>
                            </label>
                            <p class="text-xs text-slate-400 mb-2">Clasificación para el Mercado Interempresarial</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach(['industrial' => 'Industrial', 'comercial' => 'Comercial', 'servicios' => 'Servicios', 'avicola' => 'Avícola', 'ganadera' => 'Ganadera', 'otros' => 'Otros'] as $val => $label)
                                    <button type="button"
                                        @if(!$isEditing) disabled @endif
                                        wire:click="$set('sector_empresarial', '{{ $val }}')"
                                        class="px-4 py-1.5 rounded-xl text-sm font-medium border transition
                                            {{ $sector_empresarial === $val
                                                ? 'bg-forest-800 text-white border-forest-800'
                                                : 'bg-white text-slate-600 border-cream-200 hover:border-forest-400 disabled:opacity-50 disabled:cursor-not-allowed' }}">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                            @error('sector_empresarial') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono</label>
                                <input wire:model="telefono" type="tel" inputmode="tel"
                                    @disabled(!$isEditing)
                                    class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500 disabled:bg-cream-50 disabled:text-slate-400 disabled:cursor-not-allowed" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo</label>
                                <input wire:model="email" type="email"
                                    @disabled(!$isEditing)
                                    class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500 disabled:bg-cream-50 disabled:text-slate-400 disabled:cursor-not-allowed" />
                                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Dirección</label>
                            <input wire:model="direccion" type="text"
                                @disabled(!$isEditing)
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500 disabled:bg-cream-50 disabled:text-slate-400 disabled:cursor-not-allowed" />
                        </div>
                    </div>
                </div>

                {{-- Facturación --}}
                <div class="px-6 py-5">
                    <h3 class="text-sm font-semibold text-slate-700 mb-4">Facturación</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Prefijo factura</label>
                            <input wire:model="prefijo_factura" type="text" maxlength="5" placeholder="FV"
                                @disabled(!$isEditing)
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500 disabled:bg-cream-50 disabled:text-slate-400 disabled:cursor-not-allowed" />
                            @error('prefijo_factura') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Resolución DIAN <span class="text-slate-400 font-normal">(educativo)</span></label>
                            <input wire:model="resolucion_dian" type="text"
                                @disabled(!$isEditing)
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500 disabled:bg-cream-50 disabled:text-slate-400 disabled:cursor-not-allowed" />
                        </div>
                    </div>
                </div>

                {{-- Acciones --}}
                @if(! session('audit_mode') && ! session('reference_mode'))
                    <div class="px-6 py-4 flex justify-end">
                        @if($isEditing)
                            <button wire:click="save" wire:loading.attr="disabled"
                                class="px-5 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                                <span wire:loading.remove wire:target="save">Guardar configuración</span>
                                <span wire:loading wire:target="save">Guardando…</span>
                            </button>
                        @else
                            <button wire:click="edit"
                                class="px-5 py-2 bg-white border border-forest-700 text-forest-800 text-sm font-semibold rounded-xl hover:bg-forest-50 transition">
                                Editar configuración
                            </button>
                        @endif
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
