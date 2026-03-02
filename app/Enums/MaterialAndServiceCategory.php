<?php

namespace App\Enums;

enum MaterialAndServiceCategory: string
{
    case ClampSaddle  = 'clamp_saddle';
    case Crossing     = 'crossing';
    case Material     = 'material';
    case MaterialDinas = 'material_dinas';
    case PekerjaanTanah = 'pekerjaan_tanah';

    public function getLabel(): string
    {
        return match ($this) {
            self::ClampSaddle   => 'Clamp Saddle',
            self::Crossing      => 'Crossing',
            self::Material      => 'Material',
            self::MaterialDinas => 'Material Dinas',
            self::PekerjaanTanah => 'Pekerjaan Tanah',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $c) => [$c->value => $c->getLabel()])
            ->all();
    }
}
