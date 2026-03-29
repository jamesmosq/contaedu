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

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Borrador => 'bg-slate-100 text-slate-800',
            self::Generada => 'bg-blue-100 text-blue-800',
            self::Enviada => 'bg-yellow-100 text-yellow-800',
            self::Validada => 'bg-green-100 text-green-800',
            self::Rechazada => 'bg-red-100 text-red-800',
            self::Anulada => 'bg-gray-100 text-gray-800',
        };
    }

    public function messageClasses(): string
    {
        return match ($this) {
            self::Borrador => 'bg-slate-50 border-slate-200 text-slate-800',
            self::Generada => 'bg-blue-50 border-blue-200 text-blue-800',
            self::Enviada => 'bg-yellow-50 border-yellow-200 text-yellow-800',
            self::Validada => 'bg-green-50 border-green-200 text-green-800',
            self::Rechazada => 'bg-red-50 border-red-200 text-red-800',
            self::Anulada => 'bg-gray-50 border-gray-200 text-gray-800',
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
