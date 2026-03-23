@extends('errors.layout')

@section('code', '503')
@section('title', 'Plataforma en mantenimiento')
@section('description', 'ContaEdu está realizando mantenimiento programado. Volveremos pronto. Disculpa las molestias.')

@section('actions')
    <a href="javascript:window.location.reload()"
        class="inline-flex items-center gap-2 px-5 py-2.5 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
        Intentar de nuevo
    </a>
@endsection
