<?php

namespace App\Enums;

enum EstadoFacturaEnum: string
{
    case Borrador = 'borrador';
    case Generada = 'generada';
    case Enviada = 'enviada';
    case Validada = 'validada';
    case Rechazada = 'rechazada';
    case Anulada = 'anulada';

    public function label(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Generada => 'Generada',
            self::Enviada => 'Enviada',
            self::Validada => 'Validada',
            self::Rechazada => 'Rechazada',
            self::Anulada => 'Anulada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Borrador => 'slate',
            self::Generada => 'blue',
            self::Enviada => 'yellow',
            self::Validada => 'green',
            self::Rechazada => 'red',
            self::Anulada => 'gray',
        };
    }

    public function esTerminal(): bool
    {
        return in_array($this, [self::Validada, self::Anulada]);
    }

    public function puedeTransicionarA(self $nuevo): bool
    {
        return match ($this) {
            self::Borrador => $nuevo === self::Generada,
            self::Generada => in_array($nuevo, [self::Enviada, self::Anulada]),
            self::Enviada => in_array($nuevo, [self::Validada, self::Rechazada]),
            self::Rechazada => $nuevo === self::Generada,
            self::Validada => $nuevo === self::Anulada,
            self::Anulada => false,
        };
    }
}
