<?php

namespace App\Enums;

enum WorkOrderEnum: string
{
    case GANTI_METER = 'Ganti Meter';
    case TUTUP = 'Tutup';
    case BUKA_KEMBALI = 'Buka Kembali';
    case CABUT = 'Cabut';
    case UBAH_NAMA = 'Ubah Nama';
    case GANTI_ALAMAT = 'Ganti Alamat';
    case GANTI_TARIF = 'Ganti Tarif';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value', 'value');
    }
}
