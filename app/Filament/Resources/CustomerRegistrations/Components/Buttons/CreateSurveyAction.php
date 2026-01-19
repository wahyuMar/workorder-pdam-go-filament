<?php
namespace App\Filament\Resources\CustomerRegistrations\Components\Buttons;

use App\Models\ClampSaddle;
use App\Models\KlasifikasiSr;
use Dotswan\MapPicker\Fields\Map;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class CreateSurveyAction extends Action
{
    public static function make(?string $name = null): static
    {
        $name ??= 'createSurvey';

        return parent::make($name)
            ->label('Create Survey')
            ->icon(Heroicon::ClipboardDocumentCheck)
            ->schema(function ($record) {
                return [
                    Section::make('Pipa Distribusi')
                        ->hiddenLabel()
                        ->schema([
                            Map::make('location')
                                ->label('Survey Location')
                                ->defaultLocation(
                                    latitude: $record->latitude ?? (float) env('DEFAULT_LATITUDE'),
                                    longitude: $record->longitude ?? (float) env('DEFAULT_LONGITUDE')
                                )
                                ->afterStateUpdated(function (Set $set, ?array $state): void {
                                    $set('lokasi_pipa_distribusi_lat', $state['lat']);
                                    $set('lokasi_pipa_distribusi_lng', $state['lng']);
                                })
                                ->columnSpanFull(),
                            TextInput::make('lokasi_pipa_distribusi_lat')
                                ->label('Latitude')
                                ->default($record->latitude)
                                ->required()
                                ->readOnly(),
                            TextInput::make('lokasi_pipa_distribusi_lng')
                                ->label('Longitude')
                                ->required()
                                ->default($record->longitude)
                                ->readOnly(),

                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                        Section::make('Pipa SR dan Clamp Saddle')
                            ->hiddenLabel()
                            ->schema([
                                TextInput::make('panjang_pipa_sr')
                                    ->label('Panjang Pipa (SR)')
                                    ->required()
                                    ->numeric()
                                    ->suffix('meter'),
                                Select::make('clamp_saddle_id')
                                    ->label('Clamp Saddle')
                                    ->required()
                                    ->options(function () {
                                        $result = [];
                                        foreach (ClampSaddle::all() as $item) {
                                            $result[$item->id] = $item->name . ' (' . $item->brand . ')';
                                        }
                                        return $result;
                                    }),
                                Map::make('lokasi_sr')
                                    ->label('Lokasi SR')
                                    ->defaultLocation(
                                        latitude: $record->latitude ?? (float) env('DEFAULT_LATITUDE'),
                                        longitude: $record->longitude ?? (float) env('DEFAULT_LONGITUDE')
                                    )
                                    ->afterStateUpdated(function (Set $set, ?array $state): void {
                                        $set('lokasi_sr_lat', $state['lat']);
                                        $set('lokasi_sr_lng', $state['lng']);
                                    })
                                    ->columnSpanFull(),
                                TextInput::make('lokasi_sr_lat')
                                    ->label('Latitude')
                                    ->default($record->latitude)
                                    ->required()
                                    ->readOnly(),
                                TextInput::make('lokasi_sr_lng')
                                    ->label('Longitude')
                                    ->required()
                                    ->default($record->longitude)
                                    ->readOnly(),
                                Select::make('klasifikasi_sr_id')
                                    ->label('Klasifikasi SR')
                                    ->options(fn () => KlasifikasiSr::all()->pluck('name', 'id'))
                                    ->required(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                ];
            })
            ->action(function (array $data, $record) {
                dd($record, $data);
            })
            ->modalWidth(Width::SevenExtraLarge);
    }
}
