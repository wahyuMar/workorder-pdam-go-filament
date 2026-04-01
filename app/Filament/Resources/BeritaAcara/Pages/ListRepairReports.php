<?php

namespace App\Filament\Resources\BeritaAcara\Pages;

use App\Filament\Resources\BeritaAcara\RepairReportResource;
use Filament\Resources\Pages\ListRecords;

class ListRepairReports extends ListRecords
{
    protected static string $resource = RepairReportResource::class;

    protected static ?string $title = 'Perbaikan';
}
