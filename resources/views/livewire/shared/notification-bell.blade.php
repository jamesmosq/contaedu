<div class="relative" x-data="{ open: @entangle('open') }">

    {{-- Botón campana --}}
    <button x-ref="bell"
            @click="open = !open"
            class="relative p-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>

        @if($this->unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 flex items-center justify-center
                         w-4 h-4 rounded-full bg-red-500 text-white text-[10px] font-bold leading-none">
                {{ $this->unreadCount > 9 ? '9+' : $this->unreadCount }}
            </span>
        @endif
    </button>

    {{-- Panel de notificaciones --}}
    {{--
        Posicionamiento:
        - Mobile  (<lg): ocupa todo el ancho de la pantalla menos 12px de margen a cada lado,
                         anclado a 16px del fondo.
        - Desktop (≥lg): aparece a la derecha del sidebar (sidebar = 256px, gap = 16px → left = 272px),
                         ancho fijo 320px, a 80px del fondo (zona del footer del sidebar).
    --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.outside="open = false"
         class="fixed z-[100] bg-white rounded-xl shadow-2xl border border-slate-100
                inset-x-3 bottom-4
                lg:inset-x-auto lg:left-[17rem] lg:w-80 lg:bottom-20"
         style="display:none">

        {{-- Cabecera --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
            <span class="text-sm font-semibold text-slate-700">Notificaciones</span>
            <div class="flex items-center gap-3">
                @if($this->unreadCount > 0)
                    <button wire:click="markAllRead"
                            class="text-xs text-blue-600 hover:underline">
                        Marcar todas leídas
                    </button>
                @endif
                {{-- Cerrar en mobile --}}
                <button @click="open = false"
                        class="lg:hidden p-1 rounded text-slate-400 hover:text-slate-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Lista --}}
        <div class="max-h-72 overflow-y-auto divide-y divide-slate-50">
            @forelse($this->notifications as $notification)
                <div wire:key="notif-{{ $notification->id }}"
                     wire:click="markRead({{ $notification->id }})"
                     class="px-4 py-3 cursor-pointer hover:bg-slate-50 transition
                            {{ $notification->isUnread() ? 'bg-blue-50/40' : '' }}">

                    <div class="flex items-start gap-2">
                        {{-- Indicador no leído --}}
                        <span class="mt-1.5 flex-shrink-0 w-2 h-2 rounded-full
                                     {{ $notification->isUnread() ? 'bg-blue-500' : 'bg-transparent' }}">
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-slate-700 truncate">
                                {{ $notification->subject }}
                            </p>
                            <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">
                                {{ $notification->body }}
                            </p>
                            <p class="text-[10px] text-slate-400 mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center text-sm text-slate-400">
                    Sin notificaciones
                </div>
            @endforelse
        </div>
    </div>
</div>
