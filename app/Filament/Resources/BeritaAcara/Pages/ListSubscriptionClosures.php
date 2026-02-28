<?php

namespace App\Filament\Resources\BeritaAcara\Pages;

use App\Filament\Resources\BeritaAcara\SubscriptionClosureResource;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptionClosures extends ListRecords
{
    protected static string $resource = SubscriptionClosureResource::class;

    protected static ?string $title = 'Tutup Langganan';
}
