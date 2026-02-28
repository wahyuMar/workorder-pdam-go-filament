<?php

namespace App\Filament\Resources\CustomerRegistrations\Components\Buttons;

use App\Helper\SurveyHelper;
use App\Models\ClampSaddle;
use App\Models\Crossing;
use App\Models\KlasifikasiSr;
use App\Models\Survey;
use Dotswan\MapPicker\Fields\Map;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
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
                    // Pipa Distribusi
                    Section::make('Pipa Distribusi')
                        ->hiddenLabel()
                        ->schema([
                            Map::make('location')
                                ->label('Lokasi Pipa Distribusi')
                                ->defaultLocation(
                                    latitude: $record->latitude ?? (float) env('DEFAULT_LATITUDE'),
                                    longitude: $record->longitude ?? (float) env('DEFAULT_LONGITUDE')
                                )
                                ->afterStateUpdated(function (Set $set, ?array $state): void {
                                    $set('lokasi_pipa_distribusi_lat', (string) $state['lat']);
                                    $set('lokasi_pipa_distribusi_lng', (string) $state['lng']);
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
                    // Pipa SR dan Clamp Saddle
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
                                    $set('lokasi_sr_lat', (string) $state['lat']);
                                    $set('lokasi_sr_lng', (string) $state['lng']);
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
                                ->options(fn() => KlasifikasiSr::all()->pluck('name', 'id'))
                                ->required(),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                    Section::make('Rabatan')
                        ->hiddenLabel()
                        ->schema([
                            Map::make('lokasi_rabatan')
                                ->label('Lokasi Rabatan')
                                ->defaultLocation(
                                    latitude: $record->latitude ?? (float) env('DEFAULT_LATITUDE'),
                                    longitude: $record->longitude ?? (float) env('DEFAULT_LONGITUDE')
                                )
                                ->afterStateUpdated(function (Set $set, ?array $state): void {
                                    $set('lokasi_rabatan_lat', (string) $state['lat']);
                                    $set('lokasi_rabatan_lng', (string) $state['lng']);
                                })
                                ->columnSpanFull(),
                            TextInput::make('lokasi_rabatan_lat')
                                ->label('Latitude')
                                ->default($record->latitude)
                                ->required()
                                ->readOnly(),
                            TextInput::make('lokasi_rabatan_lng')
                                ->label('Longitude')
                                ->required()
                                ->default($record->longitude)
                                ->readOnly(),
                            TextInput::make('panjang_rabatan')
                                ->label('Panjang Rabatan')
                                ->required()
                                ->numeric()
                                ->suffix('meter')
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                    Section::make('Crossing')
                        ->hiddenLabel()
                        ->schema([
                            Map::make('lokasi_crossing')
                                ->label('Lokasi Crossing')
                                ->defaultLocation(
                                    latitude: $record->latitude ?? (float) env('DEFAULT_LATITUDE'),
                                    longitude: $record->longitude ?? (float) env('DEFAULT_LONGITUDE')
                                )
                                ->afterStateUpdated(function (Set $set, ?array $state): void {
                                    $set('lokasi_crossing_lat', (string) $state['lat']);
                                    $set('lokasi_crossing_lng', (string) $state['lng']);
                                })
                                ->columnSpanFull(),
                            TextInput::make('lokasi_crossing_lat')
                                ->label('Latitude')
                                ->default($record->latitude)
                                ->required()
                                ->readOnly(),
                            TextInput::make('lokasi_crossing_lng')
                                ->label('Longitude')
                                ->required()
                                ->default($record->longitude)
                                ->readOnly(),
                            TextInput::make('panjang_crossing')
                                ->label('Panjang Crossing')
                                ->required()
                                ->numeric()
                                ->suffix('meter')
                                ->columnSpanFull(),
                            Select::make('crossing_id')
                                ->label('Jenis Crossing')
                                ->options(fn() => Crossing::all()->pluck('name', 'id'))
                                ->required()
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                    Section::make('Foto')
                        ->hiddenLabel()
                        ->schema([
                            FileUpload::make('foto_rumah')
                                ->label('Foto Rumah')
                                ->image()
                                ->disk('public')
                                ->directory('surveys')
                                ->visibility('public')
                                ->required(),
                            FileUpload::make('foto_penghuni')
                                ->label('Foto Penghuni')
                                ->image()
                                ->disk('public')
                                ->directory('surveys')
                                ->visibility('public')
                                ->required(),
                            FileUpload::make('foto_lokasi_water_meter')
                                ->label('Foto Lokasi Water Meter')
                                ->image()
                                ->disk('public')
                                ->directory('surveys')
                                ->visibility('public')
                                ->required(),
                        ])
                        ->columns(3)
                        ->columnSpanFull(),
                ];
            })
            ->action(function (array $data, $record) {
                $clampSaddle = ClampSaddle::findOrFail($data['clamp_saddle_id']);

                Survey::create([
                    'no_survey' => SurveyHelper::generateNoSurvey(),
                    'lokasi_pipa_distribusi_lat' => $data['lokasi_pipa_distribusi_lat'],
                    'lokasi_pipa_distribusi_long' => $data['lokasi_pipa_distribusi_lng'],
                    'panjang_pipa_sr' => $data['panjang_pipa_sr'],
                    'ukuran_clamp_sadel' => $clampSaddle->name,
                    'lokasi_sr_lat' => $data['lokasi_sr_lat'],
                    'lokasi_sr_long' => $data['lokasi_sr_lng'],
                    'foto_rumah' => $data['foto_rumah'],
                    'foto_penghuni' => $data['foto_penghuni'],
                    'foto_lokasi_wm' => $data['foto_lokasi_water_meter'],
                    'lokasi_rabatan_lat' => $data['lokasi_rabatan_lat'],
                    'lokasi_rabatan_long' => $data['lokasi_rabatan_lng'],
                    'panjang_rabatan' => $data['panjang_rabatan'],
                    'lokasi_crossing_lat' => $data['lokasi_crossing_lat'],
                    'lokasi_crossing_long' => $data['lokasi_crossing_lng'],
                    'panjang_crossing' => $data['panjang_crossing'],
                    'crossing_id' => $data['crossing_id'],
                    'tanggal_survey' => now()->format('Y-m-d'),
                    'customer_registration_id' => $record->id,
                    'clamp_saddle_id' => $data['clamp_saddle_id'],
                    'clamp_saddle_price' => $clampSaddle->price,
                    'klasifikasi_sr_id' => $data['klasifikasi_sr_id'],
                    'created_by' => auth()->id(),
                ]);

                Notification::make()
                    ->title('Survey berhasil dibuat')
                    ->success()
                    ->send();

                $record->refesh();
            })
            ->modalWidth(Width::SevenExtraLarge);
    }
}
