<?php

namespace App\Enums;

enum StudentActivityStatus: string
{
    /** Tiene actividad reciente (mismo año, menos de 120 días). */
    case Active = 'active';

    /** Último año diferente al actual, o +120 días sin actividad en el mismo año. */
    case Inactive = 'inactive';

    /** Nunca realizó ninguna acción en la plataforma. */
    case NeverActive = 'never_active';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activo',
            self::Inactive => 'Inactivo',
            self::NeverActive => 'Nunca activo',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Active => 'bg-green-100 text-green-800',
            self::Inactive => 'bg-slate-100 text-slate-600',
            self::NeverActive => 'bg-yellow-100 text-yellow-700',
        };
    }

    /** Indica si el estudiante puede ser reclamado directamente sin aprobación del superadmin. */
    public function isFree(): bool
    {
        return $this !== self::Active;
    }
}
