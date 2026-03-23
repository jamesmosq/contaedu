<?php

namespace App\Enums;

enum ConceptoRetencion: string
{
    case ComprasGenerales = 'compras_generales';
    case Servicios = 'servicios';
    case Honorarios = 'honorarios';
    case Comisiones = 'comisiones';
    case Arrendamientos = 'arrendamientos';
    case Transporte = 'transporte';

    /**
     * Porcentaje de retención en la fuente (tarifa estándar 2024).
     * Fuente: Art. 392, 401 E.T. y tablas DIAN vigentes.
     */
    public function porcentaje(): float
    {
        return match ($this) {
            self::ComprasGenerales => 3.5,
            self::Servicios => 4.0,
            self::Honorarios => 11.0,
            self::Comisiones => 11.0,
            self::Arrendamientos => 3.5,
            self::Transporte => 3.5,
        };
    }

    /**
     * Base mínima en pesos COP para que aplique la retención (UVT 2024 = $47.065).
     * Por debajo de este monto NO se retiene.
     */
    public function baseMinima(): float
    {
        return match ($this) {
            self::ComprasGenerales => 1_128_000.0,  // 27 UVT
            self::Servicios => 128_000.0,     // 4 UVT
            self::Honorarios => 0.0,           // Sin base mínima
            self::Comisiones => 0.0,           // Sin base mínima
            self::Arrendamientos => 853_000.0,     // 27 UVT (inmuebles)
            self::Transporte => 128_000.0,     // 4 UVT
        };
    }

    /**
     * Cuenta PUC donde se acredita la retención retenida (pasivo a pagar a DIAN).
     */
    public function cuentaContable(): string
    {
        return '2365';
    }

    public function label(): string
    {
        return match ($this) {
            self::ComprasGenerales => 'Compras generales ('.$this->porcentaje().'%)',
            self::Servicios => 'Servicios ('.$this->porcentaje().'%)',
            self::Honorarios => 'Honorarios y comisiones ('.$this->porcentaje().'%)',
            self::Comisiones => 'Comisiones ('.$this->porcentaje().'%)',
            self::Arrendamientos => 'Arrendamientos ('.$this->porcentaje().'%)',
            self::Transporte => 'Transporte ('.$this->porcentaje().'%)',
        };
    }
}
