<?php

namespace App\Enums;

enum EventoReceptorEnum: string
{
    case AcuseRecibo = '030';
    case ReciboBien = '032';
    case AceptacionExpresa = '033';
    case Reclamo = '031';

    public function label(): string
    {
        return match ($this) {
            self::AcuseRecibo => '030 — Acuse de Recibo',
            self::ReciboBien => '032 — Recibo del Bien o Servicio',
            self::AceptacionExpresa => '033 — Aceptación Expresa',
            self::Reclamo => '031 — Reclamo',
        };
    }

    public function descripcion(): string
    {
        return match ($this) {
            self::AcuseRecibo => 'El adquirente confirma haber recibido la factura electrónica.',
            self::ReciboBien => 'El adquirente confirma la recepción física del bien o la prestación del servicio.',
            self::AceptacionExpresa => 'El adquirente acepta la factura sin objeciones. A partir de este momento no puede reclamar.',
            self::Reclamo => 'El adquirente rechaza parcial o totalmente la factura. Debe hacerse dentro de los 3 días hábiles.',
        };
    }
}
