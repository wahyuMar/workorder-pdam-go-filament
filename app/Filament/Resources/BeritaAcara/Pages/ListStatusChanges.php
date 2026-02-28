<?php

namespace App\Filament\Resources\BeritaAcara\Pages;

use App\Filament\Resources\BeritaAcara\StatusChangeResource;
use Filament\Resources\Pages\ListRecords;

class ListStatusChanges extends ListRecords
{
    protected static string $resource = StatusChangeResource::class;

    protected static ?string $title = 'Ubah Status';
}
