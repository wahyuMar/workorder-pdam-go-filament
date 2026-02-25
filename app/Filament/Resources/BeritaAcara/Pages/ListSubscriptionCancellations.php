<?php

namespace App\Filament\Resources\BeritaAcara\Pages;

use App\Filament\Resources\BeritaAcara\SubscriptionCancellationResource;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptionCancellations extends ListRecords
{
    protected static string $resource = SubscriptionCancellationResource::class;

    protected static ?string $title = 'Cabut Langganan';
}
