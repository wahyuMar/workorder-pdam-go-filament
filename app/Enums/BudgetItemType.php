<?php

namespace App\Enums;

enum BudgetItemType: string
{
    case PekerjaanPipaDinas = 'pekerjaan_pipa_dinas';
    case PekerjaanPipaInstalasi = 'pekerjaan_pipa_instalasi';

    public function getLabel(): string
    {
        return match ($this) {
            self::PekerjaanPipaDinas => 'Pekerjaan Pipa Dinas',
            self::PekerjaanPipaInstalasi => 'Pekerjaan Pipa Instalasi',
        };
    }
}
