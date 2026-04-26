<?php

namespace App\Enums;

enum BeritaKategori: string
{
    case News = 'news';
    case Pengumuman = 'pengumuman';

    public function getLabel(): string
    {
        return match ($this) {
            self::News => 'News',
            self::Pengumuman => 'Pengumuman',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->getLabel()])
            ->all();
    }
}
