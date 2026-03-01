<?php

namespace App\Enums;

enum MaterialAndServiceCategory: string
{
    case ClampSaddle  = 'clamp_saddle';
    case Crossing     = 'crossing';
    case Material     = 'material';
    case MaterialDinas = 'material_dinas';

    public function getLabel(): string
    {
        return match ($this) {
            self::ClampSaddle   => 'Clamp Saddle',
            self::Crossing      => 'Crossing',
            self::Material      => 'Material',
            self::MaterialDinas => 'Material Dinas',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $c) => [$c->value => $c->getLabel()])
            ->all();
    }
}
