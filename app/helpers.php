<?php

/**
 * Genera la URL correcta para rutas de Facturación Electrónica
 * según el contexto: docente en modo demo o estudiante.
 *
 * @param  string  $name    Sufijo de la ruta (ej: 'index', 'show', 'resoluciones.index')
 * @param  mixed   $params  Parámetros adicionales de la ruta
 */
function fe_route(string $name, mixed $params = []): string
{
    if (auth('web')->check() && ($demoId = request()->route('demoId'))) {
        $extra = is_array($params) ? $params : [$params];
        return route('teacher.demo.fe.'.$name, array_merge(['demoId' => $demoId], $extra));
    }

    return route('student.fe.'.$name, $params);
}
