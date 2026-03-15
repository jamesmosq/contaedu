<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $this->get('/login')->assertStatus(200);
});

test('superadmin is redirected to admin dashboard after login', function () {
    $user = User::factory()->create(['role' => 'superadmin']);

    $this->post('/login', [
        'email'    => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticated();
});

test('teacher is redirected to teacher dashboard after login', function () {
    $user = User::factory()->create(['role' => 'teacher']);

    $this->post('/login', [
        'email'    => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('teacher.dashboard'));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email'    => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($user)->post('/logout')->assertRedirect('/');

    $this->assertGuest();
});
