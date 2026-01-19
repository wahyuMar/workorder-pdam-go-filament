<?php

namespace App\Filament\Resources\ClampSaddles\Pages;

use App\Filament\Resources\ClampSaddles\ClampSaddleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClampSaddle extends EditRecord
{
    protected static string $resource = ClampSaddleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
