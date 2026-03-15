<?php

use App\Livewire\Teacher\Dashboard;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    Event::fake([TenantCreated::class]);
});

it('abre modal en modo bulk con propiedades correctas', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    Livewire::actingAs($teacher)
        ->test(Dashboard::class)
        ->call('openCreate', 'bulk')
        ->assertSet('showCreateForm', true)
        ->assertSet('createMode', 'bulk')
        ->assertSet('bulkPreview', [])
        ->assertSet('bulkResults', []);
});

it('el HTML en modo bulk contiene el botón Vista previa', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $html = Livewire::actingAs($teacher)
        ->test(Dashboard::class)
        ->call('openCreate', 'bulk')
        ->html();

    expect($html)->toContain('Vista previa');
});

it('switchMode a bulk muestra botón Vista previa', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $html = Livewire::actingAs($teacher)
        ->test(Dashboard::class)
        ->call('openCreate', 'single')
        ->call('switchMode', 'bulk')
        ->html();

    expect($html)->toContain('Vista previa');
});
