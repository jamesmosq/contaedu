<?php

use App\Models\User;

// La gestión de perfil (/profile) no está implementada en esta app.
// La administración de usuarios se realiza por el superadmin desde el panel.
// Estos tests verifican que las rutas de perfil no existen (404) como se espera.

test('profile page returns 404 since it is not implemented', function () {
    $user = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($user)
        ->get('/profile')
        ->assertNotFound();
});

test('profile update route returns 404 since it is not implemented', function () {
    $user = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($user)
        ->patch('/profile', ['name' => 'Test User', 'email' => 'test@example.com'])
        ->assertNotFound();
});
