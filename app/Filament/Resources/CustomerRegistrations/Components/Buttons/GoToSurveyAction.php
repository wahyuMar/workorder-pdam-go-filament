<?php

namespace App\Filament\Resources\CustomerRegistrations\Components\Buttons;

use App\Helper\SurveyHelper;
use App\Models\ClampSaddle;
use App\Models\KlasifikasiSr;
use App\Models\Survey;
use Dotswan\MapPicker\Fields\Map;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
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
