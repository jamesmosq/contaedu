@extends('errors.layout')

@section('code', '500')
@section('title', 'Error del servidor')
@section('description', 'Ocurrió un error inesperado en el servidor. El equipo técnico ha sido notificado. Intenta de nuevo en unos minutos.')

@section('actions')
    <a href="javascript:history.back()"
        class="inline-flex items-center gap-2 px-5 py-2.5 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
        Volver atrás
    </a>
    <a href="{{ url('/') }}"
        class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-cream-300 text-slate-700 text-sm font-semibold rounded-xl hover:bg-cream-100 transition">
        Ir al inicio
    </a>
@endsection
