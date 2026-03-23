@extends('errors.layout')

@section('code', '404')
@section('title', 'Página no encontrada')
@section('description', 'La página que buscas no existe o fue movida. Verifica la URL e intenta de nuevo.')

@section('actions')
    @if(auth('student')->check())
        <a href="{{ route('student.dashboard') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
            Ir a mi empresa
        </a>
    @elseif(auth('web')->check())
        @php $role = auth('web')->user()->role; @endphp
        <a href="{{ $role === 'superadmin' ? route('admin.dashboard') : route('teacher.dashboard') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
            Ir al panel
        </a>
    @else
        <a href="{{ route('login') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
            Iniciar sesión
        </a>
        <a href="{{ url('/') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-cream-300 text-slate-700 text-sm font-semibold rounded-xl hover:bg-cream-100 transition">
            Volver al inicio
        </a>
    @endif
@endsection
