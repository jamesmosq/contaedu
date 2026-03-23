@extends('errors.layout')

@section('code', '403')
@section('title', 'Acceso denegado')
@section('description', 'No tienes permisos para acceder a este recurso. Si crees que es un error, contacta a tu docente.')

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
    @endif
@endsection
