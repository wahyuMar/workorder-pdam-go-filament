<?php

namespace App\Filament\Resources\CustomerRegistrations\Components\Buttons;

use Filament\Actions\Action;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;

class GoToSurveyAction extends Action
{
    public static function make(?string $name = null): static
    {
        $name ??= 'createSurvey';

        return parent::make($name)
            ->label('Go To Survey')
            ->icon(Heroicon::ClipboardDocumentCheck)
            ->color(Color::Emerald)
            ->url(fn($record) => route('filament.admin.resources.surveys.view', $record->survey));
    }
}
