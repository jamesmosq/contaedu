<?php

// ─── Servicios ──────────────────────────────────────────────────────────────

arch('servicios están en el namespace correcto')
    ->expect('App\Services')
    ->toBeClasses();

arch('servicios no extienden nada (sin herencia innecesaria)')
    ->expect('App\Services')
    ->toExtendNothing();

// ─── Excepciones ────────────────────────────────────────────────────────────

arch('excepciones de la aplicación extienden RuntimeException o Exception')
    ->expect('App\Exceptions')
    ->toBeClasses();

arch('AccountingImbalanceException extiende RuntimeException')
    ->expect('App\Exceptions\AccountingImbalanceException')
    ->toExtend(RuntimeException::class);

// ─── Modelos centrales ───────────────────────────────────────────────────────

arch('modelos centrales están en App\Models\Central o App\Models')
    ->expect('App\Models\Central')
    ->toBeClasses();

// ─── Enums ──────────────────────────────────────────────────────────────────

arch('enums de la aplicación están en App\Enums')
    ->expect('App\Enums')
    ->toBeEnums();

// ─── Controladores ──────────────────────────────────────────────────────────

arch('controladores tienen el sufijo Controller')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller');

// ─── Middleware ─────────────────────────────────────────────────────────────

arch('middlewares están en el namespace correcto')
    ->expect('App\Http\Middleware')
    ->toBeClasses();

// ─── Componentes Livewire ────────────────────────────────────────────────────

arch('componentes Livewire no usan HasFactory')
    ->expect('App\Livewire')
    ->not->toUse('Illuminate\Database\Eloquent\Factories\HasFactory');

arch('componentes Livewire del tenant están en el namespace Tenant')
    ->expect('App\Livewire\Tenant')
    ->toBeClasses();

arch('componentes Livewire del docente están en el namespace Teacher')
    ->expect('App\Livewire\Teacher')
    ->toBeClasses();

// ─── Seguridad ───────────────────────────────────────────────────────────────

arch('no hay llamadas a eval() en el código de producción')
    ->expect('App')
    ->not->toUse('eval');

arch('no hay dump o dd en código de producción')
    ->expect('App')
    ->not->toUse(['dump', 'dd', 'var_dump', 'print_r', 'ray']);
