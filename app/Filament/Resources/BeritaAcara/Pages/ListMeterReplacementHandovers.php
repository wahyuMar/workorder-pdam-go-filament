<?php

namespace App\Filament\Resources\BeritaAcara\Pages;

use App\Filament\Resources\BeritaAcara\MeterReplacementHandoverResource;
use Filament\Resources\Pages\ListRecords;

class ListMeterReplacementHandovers extends ListRecords
{
    protected static string $resource = MeterReplacementHandoverResource::class;

    protected static ?string $title = 'Serah Terima Ganti Meter';
}
