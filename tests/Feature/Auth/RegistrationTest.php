<?php

// El registro público está deshabilitado en esta app.
// Los docentes son creados por el superadmin y los estudiantes por el docente.
// Se mantienen estos tests adaptados al comportamiento real de la app.

test('registration screen can be rendered', function () {
    // La ruta /register existe pero no es parte del flujo normal de esta app
    $this->get('/register')->assertStatus(200);
});

test('registration redirects to login since public registration is not enabled', function () {
    $response = $this->post('/register', [
        'name'                  => 'Test User',
        'email'                 => 'test@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ]);

    // El controlador redirige al login al ser una funcionalidad no habilitada
    $response->assertRedirect(route('login'));
});
