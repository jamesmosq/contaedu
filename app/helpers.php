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

/**
 * Convierte un valor numérico (pesos colombianos) a texto en español.
 * Ejemplo: 357000 → "Trescientos cincuenta y siete mil pesos m/cte"
 */
function numero_a_letras(float $numero): string
{
    $n = (int) round($numero);

    if ($n === 0) {
        return 'Cero pesos m/cte';
    }

    $u = [
        '', 'un', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve',
        'diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete',
        'dieciocho', 'diecinueve', 'veinte', 'veintiún', 'veintidós', 'veintitrés',
        'veinticuatro', 'veinticinco', 'veintiséis', 'veintisiete', 'veintiocho', 'veintinueve',
    ];
    $d = ['', '', '', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
    $c = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos',
          'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];

    // Closure recursiva para números < 1000
    $menor1000 = null;
    $menor1000 = function (int $x) use ($u, $d, $c, &$menor1000): string {
        if ($x === 0)   return '';
        if ($x === 100) return 'cien';
        if ($x < 30)    return $u[$x];
        if ($x < 100) {
            $dec = (int) ($x / 10);
            $uni = $x % 10;
            return $d[$dec] . ($uni ? ' y ' . $u[$uni] : '');
        }
        $cen  = (int) ($x / 100);
        $rest = $x % 100;
        return $c[$cen] . ($rest ? ' ' . $menor1000($rest) : '');
    };

    $millones = (int) ($n / 1_000_000);
    $miles    = (int) (($n % 1_000_000) / 1_000);
    $resto    = $n % 1_000;

    $partes = [];

    if ($millones > 0) {
        $partes[] = $millones === 1 ? 'un millón' : $menor1000($millones) . ' millones';
    }
    if ($miles > 0) {
        $partes[] = $miles === 1 ? 'mil' : $menor1000($miles) . ' mil';
    }
    if ($resto > 0) {
        $partes[] = $menor1000($resto);
    }

    $texto   = implode(' ', $partes);
    $dePesos = ($millones > 0 && $miles === 0 && $resto === 0) ? ' de' : '';

    return ucfirst($texto) . $dePesos . ' pesos m/cte';
}

/**
 * Retorna el modo contable activo ('real' | 'sandbox').
 * Se determina por el prefijo de la ruta actual.
 */
function modoContable(): string
{
    return request()->is('aprendizaje/*') ? 'sandbox' : 'real';
}

/**
 * Formatea un NIT para mostrar.
 * Si ya tiene guión lo devuelve igual. Si no, lo devuelve tal cual.
 */
function formatearNit(string $nit): string
{
    if (str_contains($nit, '-')) {
        return $nit;
    }
    return $nit;
}
