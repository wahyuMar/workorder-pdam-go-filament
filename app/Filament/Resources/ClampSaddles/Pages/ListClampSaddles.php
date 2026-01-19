<?php

namespace App\Filament\Resources\ClampSaddles\Pages;

use App\Filament\Resources\ClampSaddles\ClampSaddleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClampSaddles extends ListRecords
{
    protected static string $resource = ClampSaddleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
