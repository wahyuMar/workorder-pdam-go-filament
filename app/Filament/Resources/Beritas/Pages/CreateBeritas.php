<?php

namespace App\Filament\Resources\Beritas\Pages;

use App\Filament\Resources\Beritas\BeritasResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBeritas extends CreateRecord
{
    protected static string $resource = BeritasResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
