<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ActivityState: string implements HasLabel, HasColor
{
    case PROGRAMADO = 'programado';
    case EN_PROCESO = 'en_proceso';
    case EJECUTADO = 'ejecutado';
    case NO_CUMPLIO = 'no_cumplio';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PROGRAMADO => 'Programado',
            self::EN_PROCESO => 'En Proceso',
            self::EJECUTADO => 'Ejecutado',
            self::NO_CUMPLIO => 'No Cumplió',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PROGRAMADO => 'warning',
            self::EN_PROCESO => 'info',
            self::EJECUTADO => 'success',
            self::NO_CUMPLIO => 'danger',
        };
    }
}
