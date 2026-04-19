<x-tenant-layout title="Nueva Factura Electrónica">

    <x-slot name="header">
        <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Facturación electrónica</p>
        <h2 class="font-display text-2xl font-bold text-white">Nueva Factura Electrónica</h2>
        <p class="text-forest-300 text-sm mt-1">Simulador DIAN — Ambiente de Pruebas</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ fe_route('store') }}" id="form-fe" x-data="facturaForm()">

                @csrf

                {{-- Resolución --}}
                <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
                    <h3 class="text-base font-semibold text-slate-800 mb-4">Resolución y Fechas</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Resolución activa <span class="text-red-500">*</span></label>
                            <select name="resolucion_id" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                                @foreach($resoluciones as $res)
                                    <option value="{{ $res->id }}">{{ $res->prefijo ? $res->prefijo . ' — ' : '' }}{{ $res->numero_resolucion }} ({{ $res->rangoDisponible() }} disponibles)</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de emisión <span class="text-red-500">*</span></label>
                            <input type="date" name="fecha_emision" value="{{ old('fecha_emision', now()->toDateString()) }}" required
                                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Hora de emisión <span class="text-red-500">*</span></label>
                            <input type="time" name="hora_emision" value="{{ old('hora_emision', now()->format('H:i')) }}" required
                                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                {{-- Adquirente --}}
                <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
                    <h3 class="text-base font-semibold text-slate-800 mb-4">Datos del Adquirente</h3>

                    {{-- Buscar en clientes existentes --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Buscar cliente registrado</label>
                        <select @change="cargarCliente($event)" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">— Seleccionar cliente existente —</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}"
                                    data-tipo="{{ match($cliente->document_type) { 'nit' => '31', 'cc' => '13', 'ce' => '22', 'pasaporte' => '91', default => '31' } }}"
                                    data-doc="{{ $cliente->document }}"
                                    data-nombre="{{ $cliente->name }}"
                                    data-email="{{ $cliente->email }}"
                                    data-telefono="{{ $cliente->phone }}"
                                    data-direccion="{{ $cliente->address }}">
                                    {{ $cliente->name }} — {{ $cliente->document }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="cliente_id" x-model="clienteId">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de documento <span class="text-red-500">*</span></label>
                            <select name="tipo_doc_adquirente" x-model="tipoDoc" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                                @foreach($tiposDocumento as $tipo)
                                    <option value="{{ $tipo->value }}">{{ $tipo->value }} — {{ $tipo->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Número de documento <span class="text-red-500">*</span></label>
                            <input type="text" name="num_doc_adquirente" x-model="numDoc" required
                                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="Ej: 900123456">
                            <p x-show="tipoDoc === '31'" class="mt-1 text-xs text-slate-500">
                                Para NIT puedes ingresar con guión (ej: <strong>900123456-7</strong>) o sin guión con el DV al final (ej: <strong>9001234567</strong>).
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nombre / Razón social <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre_adquirente" x-model="nombre" required
                                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="Nombre completo o razón social">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email_adquirente" x-model="email" required
                                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="correo@empresa.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
                            <input type="text" name="telefono_adquirente" x-model="telefono"
                                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="Opcional">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Dirección</label>
                            <input type="text" name="direccion_adquirente" x-model="direccion"
                                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="Opcional">
                        </div>
                    </div>
                </div>

                {{-- Condiciones de pago --}}
                <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
                    <h3 class="text-base font-semibold text-slate-800 mb-4">Condiciones de Pago</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Medio de pago</label>
                            <select name="medio_pago" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                                <option value="10">10 — Efectivo</option>
                                <option value="42">42 — Consignación / Débito</option>
                                <option value="48">48 — Tarjeta de crédito/débito</option>
                                <option value="20">20 — Cheque</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Forma de pago</label>
                            <select name="forma_pago" x-model="formaPago" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                                <option value="1">1 — Contado</option>
                                <option value="2">2 — Crédito</option>
                            </select>
                        </div>
                        <div x-show="formaPago === '2'">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Fecha vencimiento</label>
                            <input type="date" name="fecha_vencimiento_pago"
                                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                {{-- Ítems --}}
                <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-semibold text-slate-800">Ítems de la Factura</h3>
                        <button type="button" @click="agregarLinea()"
                                class="px-3 py-1.5 bg-brand-800 text-white text-xs font-semibold rounded-lg hover:bg-brand-700 transition">
                            + Agregar ítem
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Descripción</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-slate-600 w-24">Cantidad</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-slate-600 w-32">Precio unit.</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-slate-600 w-20">%Dto</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-slate-600 w-20">%IVA</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-slate-600 w-32">Total</th>
                                    <th class="px-3 py-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(linea, idx) in lineas" :key="idx">
                                    <tr class="border-t border-slate-100">
                                        <td class="px-2 py-2">
                                            <select @change="cargarProducto($event, idx)"
                                                    class="w-full text-xs border border-slate-200 rounded px-2 py-1 mb-1">
                                                <option value="">— producto —</option>
                                                @foreach($productos as $producto)
                                                    <option value="{{ $producto->id }}"
                                                        data-nombre="{{ $producto->name }}"
                                                        data-precio="{{ $producto->sale_price }}"
                                                        data-iva="{{ $producto->tax_rate }}"
                                                        data-codigo="{{ $producto->code }}">
                                                        {{ $producto->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="text" :name="'lineas['+idx+'][descripcion]'" x-model="linea.descripcion"
                                                   required placeholder="Descripción del ítem"
                                                   class="w-full text-xs border border-slate-200 rounded px-2 py-1">
                                            <input type="hidden" :name="'lineas['+idx+'][producto_id]'" x-model="linea.producto_id">
                                            <input type="hidden" :name="'lineas['+idx+'][codigo_producto]'" x-model="linea.codigo">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" :name="'lineas['+idx+'][cantidad]'" x-model="linea.cantidad"
                                                   @input="calcularTotales()" min="0.001" step="0.001" required
                                                   class="w-full text-xs text-right border border-slate-200 rounded px-2 py-1">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" :name="'lineas['+idx+'][precio_unitario]'" x-model="linea.precio"
                                                   @input="calcularTotales()" min="0" step="1" required
                                                   class="w-full text-xs text-right border border-slate-200 rounded px-2 py-1">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="number" :name="'lineas['+idx+'][porcentaje_descuento]'" x-model="linea.descuento"
                                                   @input="calcularTotales()" min="0" max="100" step="0.1"
                                                   class="w-full text-xs text-right border border-slate-200 rounded px-2 py-1">
                                        </td>
                                        <td class="px-2 py-2">
                                            <select :name="'lineas['+idx+'][porcentaje_iva]'" x-model="linea.iva"
                                                    @change="calcularTotales()"
                                                    class="w-full text-xs border border-slate-200 rounded px-2 py-1">
                                                <option value="19">19%</option>
                                                <option value="5">5%</option>
                                                <option value="0">0%</option>
                                            </select>
                                        </td>
                                        <td class="px-2 py-2 text-right text-xs font-semibold text-slate-800">
                                            $<span x-text="formatear(linea.totalLinea)"></span>
                                        </td>
                                        <td class="px-2 py-2 text-center">
                                            <button type="button" @click="quitarLinea(idx)"
                                                    class="text-red-400 hover:text-red-600 text-xs">✕</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="border-t-2 border-slate-200 bg-slate-50">
                                <tr>
                                    <td colspan="5" class="px-3 py-2 text-right text-xs font-semibold text-slate-600">Subtotal:</td>
                                    <td class="px-3 py-2 text-right text-sm font-semibold">$<span x-text="formatear(subtotal)"></span></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="px-3 py-2 text-right text-xs font-semibold text-slate-600">IVA:</td>
                                    <td class="px-3 py-2 text-right text-sm font-semibold">$<span x-text="formatear(totalIva)"></span></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="px-3 py-2 text-right text-base font-bold text-slate-800">Total:</td>
                                    <td class="px-3 py-2 text-right text-base font-bold text-slate-800">$<span x-text="formatear(total)"></span></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Notas --}}
                <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notas / Observaciones</label>
                    <textarea name="notas" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm"
                              placeholder="Opcional">{{ old('notas') }}</textarea>
                </div>

                {{-- Botones --}}
                <div class="flex gap-3 justify-end">
                    <a href="{{ fe_route('index') }}"
                       class="px-5 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-5 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
                        Guardar como borrador
                    </button>
                </div>

            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function facturaForm() {
        return {
            clienteId: '',
            tipoDoc: '31',
            numDoc: '',
            nombre: '',
            email: '',
            telefono: '',
            direccion: '',
            formaPago: '1',
            lineas: [{ producto_id: '', codigo: '', descripcion: '', cantidad: 1, precio: 0, descuento: 0, iva: 19, totalLinea: 0 }],
            subtotal: 0,
            totalIva: 0,
            total: 0,

            cargarCliente(e) {
                const opt = e.target.selectedOptions[0];
                if (! opt.value) return;
                this.clienteId   = opt.value;
                this.tipoDoc     = opt.dataset.tipo || '31';
                this.numDoc      = opt.dataset.doc || '';
                this.nombre      = opt.dataset.nombre || '';
                this.email       = opt.dataset.email || '';
                this.telefono    = opt.dataset.telefono || '';
                this.direccion   = opt.dataset.direccion || '';
            },

            cargarProducto(e, idx) {
                const opt = e.target.selectedOptions[0];
                if (! opt.value) return;
                this.lineas[idx].producto_id  = opt.value;
                this.lineas[idx].descripcion  = opt.dataset.nombre || '';
                this.lineas[idx].precio       = parseFloat(opt.dataset.precio) || 0;
                this.lineas[idx].iva          = parseInt(opt.dataset.iva) || 19;
                this.lineas[idx].codigo       = opt.dataset.codigo || '';
                this.calcularTotales();
            },

            agregarLinea() {
                this.lineas.push({ producto_id: '', codigo: '', descripcion: '', cantidad: 1, precio: 0, descuento: 0, iva: 19, totalLinea: 0 });
            },

            quitarLinea(idx) {
                if (this.lineas.length === 1) return;
                this.lineas.splice(idx, 1);
                this.calcularTotales();
            },

            calcularTotales() {
                let sub = 0, iva = 0;
                this.lineas.forEach(l => {
                    const cant = parseFloat(l.cantidad) || 0;
                    const precio = parseFloat(l.precio) || 0;
                    const dto = parseFloat(l.descuento) || 0;
                    const piva = parseFloat(l.iva) || 0;
                    const subtotalLinea = cant * precio * (1 - dto / 100);
                    const ivaLinea = subtotalLinea * piva / 100;
                    l.totalLinea = subtotalLinea + ivaLinea;
                    sub += subtotalLinea;
                    iva += ivaLinea;
                });
                this.subtotal = sub;
                this.totalIva = iva;
                this.total    = sub + iva;
            },

            formatear(n) {
                return Math.round(n).toLocaleString('es-CO');
            }
        };
    }
    </script>
    @endpush
</x-tenant-layout>
