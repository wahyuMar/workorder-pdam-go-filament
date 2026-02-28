<?php

namespace App\Filament\Resources\BeritaAcara\Pages;

use App\Filament\Resources\BeritaAcara\SubscriptionReopeningResource;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptionReopenings extends ListRecords
{
    protected static string $resource = SubscriptionReopeningResource::class;

    protected static ?string $title = 'Serah Terima Buka Kembali';
}
