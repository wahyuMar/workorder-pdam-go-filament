<?php

namespace App\Filament\Resources\BeritaAcara\Pages;

use App\Filament\Resources\BeritaAcara\TeraMeterReportResource;
use Filament\Resources\Pages\ListRecords;

class ListTeraMeterReports extends ListRecords
{
    protected static string $resource = TeraMeterReportResource::class;

    protected static ?string $title = 'Tera Meter';
}
