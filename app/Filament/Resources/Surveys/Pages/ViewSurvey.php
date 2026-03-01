<?php

namespace App\Filament\Resources\Surveys\Pages;

use App\Filament\Resources\Surveys\Components\Buttons\CreateBudgetingAction;
use App\Filament\Resources\Surveys\SurveyResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSurvey extends ViewRecord
{
    protected static string $resource = SurveyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateBudgetingAction::make()
                ->visible(fn() => $this->record->budgeting === null),
        ];
    }
}
