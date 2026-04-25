@if(session('impersonating_admin_id'))
    <div class="bg-rose-600 text-white px-4 py-2 flex items-center justify-between text-sm font-medium shrink-0">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
            <span>
                Modo administrador &mdash; sesión como
                <strong>{{ session('impersonating_name') }}</strong>
                ({{ session('impersonating_type') === 'student' ? 'estudiante' : 'docente' }})
            </span>
        </div>
        <a href="{{ route('admin.impersonate.stop') }}"
           class="px-3 py-1 bg-white/20 hover:bg-white/30 rounded-lg text-xs font-semibold transition">
            Volver a admin
        </a>
    </div>
@endif
