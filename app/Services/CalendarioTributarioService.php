<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Genera el calendario de obligaciones tributarias colombianas.
 *
 * Las fechas límite de la DIAN para RteFte e IVA se escalonan según
 * los dos últimos dígitos del NIT (art. 1 Decreto DIAN vencimientos).
 * Esta implementación usa las fechas del calendario tributario 2025.
 */
class CalendarioTributarioService
{
    /**
     * Mapa: último dígito NIT → día límite del mes.
     * Fuente: calendario tributario DIAN 2025.
     */
    private const DIAS_POR_DIGITO = [
        '01' => 9,  '02' => 9,  '03' => 10, '04' => 10,
        '05' => 11, '06' => 11, '07' => 12, '08' => 12,
        '09' => 13, '10' => 13, '11' => 14, '12' => 14,
        '13' => 14, '14' => 15, '15' => 15, '16' => 16,
        '17' => 16, '18' => 17, '19' => 17, '20' => 18,
        '21' => 18, '22' => 19, '23' => 19, '24' => 20,
        '25' => 20, '26' => 21, '27' => 21, '28' => 22,
        '29' => 22, '30' => 23,
        // Resto → día 22
    ];

    /**
     * Colores e iconos por tipo de obligación.
     */
    private const TIPOS = [
        'retenciones' => ['label' => 'Retención en la Fuente', 'color' => 'blue',   'icon' => '🏦'],
        'iva_bimestre' => ['label' => 'IVA Bimestral',          'color' => 'violet', 'icon' => '📊'],
        'iva_cuatri' => ['label' => 'IVA Cuatrimestral',      'color' => 'purple', 'icon' => '📊'],
        'iva_anual' => ['label' => 'IVA Anual',              'color' => 'purple', 'icon' => '📊'],
        'renta' => ['label' => 'Declaración de Renta',   'color' => 'red',    'icon' => '📋'],
        'ica' => ['label' => 'ICA',                    'color' => 'amber',  'icon' => '🏙️'],
        'exogena' => ['label' => 'Información Exógena',    'color' => 'slate',  'icon' => '📁'],
    ];

    /**
     * Genera los eventos del calendario para el año dado.
     *
     * @return Collection<int, array{fecha: Carbon, tipo: string, descripcion: string, aplica: bool, color: string, icon: string, estado: string}>
     */
    public function generar(int $year, string $nit, string $regimen): Collection
    {
        $eventos = collect();
        $diaLimite = $this->diaLimite($nit);

        // ── Retención en la Fuente — mensual (meses 1-12, se declara el mes siguiente)
        if ($regimen !== 'simplificado') {
            for ($mes = 1; $mes <= 12; $mes++) {
                $mesPago = $mes === 12 ? 1 : $mes + 1;
                $yearPago = $mes === 12 ? $year + 1 : $year;
                $fecha = $this->siguienteHabil(Carbon::create($yearPago, $mesPago, $diaLimite));

                $eventos->push([
                    'fecha' => $fecha,
                    'tipo' => 'retenciones',
                    'descripcion' => 'RteFte período '.Carbon::create($year, $mes, 1)->translatedFormat('F Y'),
                    'aplica' => true,
                    'color' => 'blue',
                    'icon' => '🏦',
                    'estado' => $this->estado($fecha),
                ]);
            }
        }

        // ── IVA — según régimen
        if ($regimen === 'gran_contribuyente') {
            // Bimestral: periodos Ene-Feb / Mar-Abr / May-Jun / Jul-Ago / Sep-Oct / Nov-Dic
            $bimestres = [
                [1, 2, 'Ene-Feb'], [3, 4, 'Mar-Abr'], [5, 6, 'May-Jun'],
                [7, 8, 'Jul-Ago'], [9, 10, 'Sep-Oct'], [11, 12, 'Nov-Dic'],
            ];
            foreach ($bimestres as [$ini, $fin, $etiqueta]) {
                $yearFecha = $fin === 12 ? $year + 1 : $year;
                $fecha = $this->siguienteHabil(Carbon::create($yearFecha, $fin === 12 ? 1 : $fin + 1, $diaLimite));

                $eventos->push([
                    'fecha' => $fecha,
                    'tipo' => 'iva_bimestre',
                    'descripcion' => 'IVA Bimestral período '.$etiqueta.' '.$year,
                    'aplica' => true,
                    'color' => 'violet',
                    'icon' => '📊',
                    'estado' => $this->estado($fecha),
                ]);
            }
        } elseif ($regimen === 'comun') {
            // Cuatrimestral: Ene-Abr / May-Ago / Sep-Dic
            $cuatrimestres = [
                [1, 4, 'Ene-Abr', 5], [5, 8, 'May-Ago', 9], [9, 12, 'Sep-Dic', 1],
            ];
            foreach ($cuatrimestres as [$ini, $fin, $etiqueta, $mesPago]) {
                $yearPago = $fin === 12 ? $year + 1 : $year;
                $fecha = $this->siguienteHabil(Carbon::create($yearPago, $mesPago, $diaLimite));

                $eventos->push([
                    'fecha' => $fecha,
                    'tipo' => 'iva_cuatri',
                    'descripcion' => 'IVA Cuatrimestral período '.$etiqueta.' '.$year,
                    'aplica' => true,
                    'color' => 'purple',
                    'icon' => '📊',
                    'estado' => $this->estado($fecha),
                ]);
            }
        } else {
            // Simplificado: no declara IVA
            $eventos->push([
                'fecha' => Carbon::create($year, 12, 31),
                'tipo' => 'iva_anual',
                'descripcion' => 'Régimen Simplificado — No presenta declaración de IVA',
                'aplica' => false,
                'color' => 'slate',
                'icon' => '📊',
                'estado' => 'no_aplica',
            ]);
        }

        // ── Declaración de Renta — anual (persona jurídica: abril-mayo del año siguiente)
        $fechaRenta = $this->siguienteHabil(Carbon::create($year + 1, 4, $diaLimite));
        $eventos->push([
            'fecha' => $fechaRenta,
            'tipo' => 'renta',
            'descripcion' => 'Declaración de Renta año gravable '.$year,
            'aplica' => true,
            'color' => 'red',
            'icon' => '📋',
            'estado' => $this->estado($fechaRenta),
        ]);

        // ── ICA — tipicamente anual (varies per municipio, usamos fecha referencia)
        $fechaIca = $this->siguienteHabil(Carbon::create($year + 1, 3, 15));
        $eventos->push([
            'fecha' => $fechaIca,
            'tipo' => 'ica',
            'descripcion' => 'ICA (Ind. y Comercio) vigencia '.$year.' — verificar fecha con tu municipio',
            'aplica' => $regimen !== 'simplificado',
            'color' => 'amber',
            'icon' => '🏙️',
            'estado' => $regimen !== 'simplificado' ? $this->estado($fechaIca) : 'no_aplica',
        ]);

        // ── Información Exógena — anual
        $fechaExogena = Carbon::create($year + 1, 5, 1);
        $eventos->push([
            'fecha' => $fechaExogena,
            'tipo' => 'exogena',
            'descripcion' => 'Información Exógena (medios magnéticos) año gravable '.$year,
            'aplica' => $regimen !== 'simplificado',
            'color' => 'slate',
            'icon' => '📁',
            'estado' => $regimen !== 'simplificado' ? $this->estado($fechaExogena) : 'no_aplica',
        ]);

        return $eventos->sortBy('fecha')->values();
    }

    /**
     * Determina el día límite de pago según los dos últimos dígitos del NIT.
     * El NIT se limpia de puntos y dígito de verificación antes de procesar.
     */
    public function diaLimite(string $nit): int
    {
        $limpio = preg_replace('/[^0-9]/', '', $nit);
        $ultimos = str_pad(substr($limpio, -2), 2, '0', STR_PAD_LEFT);

        return self::DIAS_POR_DIGITO[$ultimos] ?? 22;
    }

    /**
     * Estado del evento: vencido | proximo | futuro | no_aplica.
     */
    private function estado(Carbon $fecha): string
    {
        $hoy = now();

        if ($fecha->isPast()) {
            return 'vencido';
        }

        if ($fecha->diffInDays($hoy) <= 30) {
            return 'proximo';
        }

        return 'futuro';
    }

    /**
     * Si la fecha cae en fin de semana, avanza al siguiente lunes.
     */
    private function siguienteHabil(Carbon $fecha): Carbon
    {
        while ($fecha->isWeekend()) {
            $fecha->addDay();
        }

        return $fecha;
    }
}
