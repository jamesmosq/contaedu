<?php

namespace App\Enums;

enum CommunicationAudience: string
{
    case All = 'all';
    case Coordinators = 'coordinators';
    case Teachers = 'teachers';

    public function label(): string
    {
        return match ($this) {
            self::All => 'Todos los usuarios',
            self::Coordinators => 'Solo coordinadores',
            self::Teachers => 'Solo docentes',
        };
    }
}
