<?php

namespace App\Filament\Resources\BeritaAcara\Tables;

use App\Models\MaterialAndService;
use App\Models\RepairReport;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RepairReportTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_pengaduan')
                    ->label('No. Pengaduan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('no_sambungan')
                    ->label('No. Sambungan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('meterRepair.no_spp')
                    ->label('No. SPP')
                    ->searchable(),
            ])
            ->actions([
                Action::make('view_bap')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn ($record) => RepairReport::where('complaint_id', $record->id)->exists())
                    ->form(function ($record): array {
                        $bap = RepairReport::where('complaint_id', $record->id)->first();

                        return [
                            TextInput::make('no_bap')
                                ->label('No. BAP')
                                ->default($bap?->no_bap)
                                ->disabled(),
                            TextInput::make('no_sambungan')
                                ->label('No. Sambungan')
                                ->default($bap?->no_sambungan)
                                ->disabled(),
                            TextInput::make('nama')
                                ->label('Nama')
                                ->default($bap?->nama)
                                ->disabled(),
                            Textarea::make('alamat')
                                ->label('Alamat')
                                ->default($bap?->alamat)
                                ->disabled(),
                            TextInput::make('lokasi')
                                ->label('Lokasi')
                                ->default($bap?->lokasi)
                                ->disabled(),
                            Repeater::make('items')
                                ->label('Barang, Jasa, dan Alat')
                                ->default($bap?->items ?? [])
                                ->schema([
                                    TextInput::make('item_type')
                                        ->label('Jenis Item')
                                        ->disabled(),
                                    TextInput::make('material_name')
                                        ->label('Material/Jasa/Alat')
                                        ->disabled(),
                                    TextInput::make('quantity')
                                        ->label('Qty')
                                        ->disabled(),
                                    TextInput::make('unit')
                                        ->label('Satuan')
                                        ->disabled(),
                                ])
                                ->columnSpanFull()
                                ->disabled(),
                            Textarea::make('catatan')
                                ->label('Catatan')
                                ->default($bap?->catatan)
                                ->disabled(),
                            DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->default($bap?->tanggal)
                                ->disabled(),
                        ];
                    })
                    ->modalHeading('Detail Berita Acara Perbaikan')
                    ->modalWidth('3xl')
                    ->closeModalByClickingAway(false),
                Action::make('create_bap')
                    ->label('Create BAP')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->visible(fn ($record) => ! RepairReport::where('complaint_id', $record->id)->exists())
                    ->form(function ($record): array {
                        $materialOptions = MaterialAndService::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all();

                        return [
                            TextInput::make('no_bap')
                                ->label('No. BAP')
                                ->disabled()
                                ->dehydrated(false)
                                ->default(RepairReport::peekNextNoBAP()),
                            TextInput::make('no_sambungan')
                                ->label('No. Sambungan')
                                ->default($record->no_sambungan)
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('nama')
                                ->label('Nama')
                                ->default($record->nama)
                                ->disabled()
                                ->dehydrated(),
                            Textarea::make('alamat')
                                ->label('Alamat')
                                ->default($record->alamat)
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('lokasi')
                                ->label('Lokasi')
                                ->required(),
                            FileUpload::make('foto_sebelum')
                                ->label('Foto Sebelum')
                                ->directory('berita-acara/perbaikan'),
                            FileUpload::make('foto_sesudah')
                                ->label('Foto Sesudah')
                                ->directory('berita-acara/perbaikan'),
                            Repeater::make('items')
                                ->label('Barang, Jasa, dan Alat')
                                ->schema([
                                    Select::make('item_type')
                                        ->label('Jenis Item')
                                        ->options([
                                            'Barang' => 'Barang',
                                            'Jasa' => 'Jasa',
                                            'Alat' => 'Alat',
                                        ])
                                        ->required(),
                                    Select::make('material_and_service_id')
                                        ->label('Material/Jasa/Alat')
                                        ->options($materialOptions)
                                        ->searchable()
                                        ->required(),
                                    TextInput::make('quantity')
                                        ->label('Qty')
                                        ->numeric()
                                        ->default(1)
                                        ->required(),
                                ])
                                ->defaultItems(1)
                                ->columnSpanFull()
                                ->required(),
                            Textarea::make('catatan')
                                ->label('Catatan')
                                ->rows(3),
                            DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->required()
                                ->default(now()),
                        ];
                    })
                    ->action(function ($record, array $data): void {
                        $materialLookup = MaterialAndService::query()
                            ->whereIn('id', collect($data['items'] ?? [])->pluck('material_and_service_id')->filter()->all())
                            ->get()
                            ->keyBy('id');

                        $items = collect($data['items'] ?? [])
                            ->map(function (array $item) use ($materialLookup): array {
                                $material = $materialLookup->get((int) ($item['material_and_service_id'] ?? 0));

                                return [
                                    'item_type' => $item['item_type'] ?? null,
                                    'material_and_service_id' => $material?->id,
                                    'material_name' => $material?->name,
                                    'quantity' => (float) ($item['quantity'] ?? 0),
                                    'unit' => $material?->unit,
                                ];
                            })
                            ->values()
                            ->all();

                        RepairReport::create([
                            'complaint_id' => $record->id,
                            'no_sambungan' => $data['no_sambungan'],
                            'nama' => $data['nama'],
                            'alamat' => $data['alamat'] ?? null,
                            'lokasi' => $data['lokasi'],
                            'foto_sebelum' => $data['foto_sebelum'] ?? null,
                            'foto_sesudah' => $data['foto_sesudah'] ?? null,
                            'items' => $items,
                            'catatan' => $data['catatan'] ?? null,
                            'tanggal' => $data['tanggal'],
                        ]);

                        Notification::make()
                            ->title('Berita Acara Perbaikan Berhasil Dibuat')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Buat Berita Acara Perbaikan')
                    ->modalWidth('3xl'),
            ])
            ->defaultSort('tanggal', 'desc');
    }

    public static function getFormSchema(): array
    {
        return [
            TextInput::make('no_bap')
                ->label('No. BAP')
                ->disabled()
                ->dehydrated(false)
                ->default(function () {
                    return RepairReport::peekNextNoBAP();
                }),
            TextInput::make('no_sambungan')
                ->label('No. Sambungan')
                ->required()
                ->searchable(),
            TextInput::make('nama')
                ->label('Nama')
                ->required(),
            Textarea::make('alamat')
                ->label('Alamat')
                ->required(),
            TextInput::make('lokasi')
                ->label('Lokasi')
                ->required(),
            DateTimePicker::make('tanggal')
                ->label('Tanggal')
                ->required()
                ->default(now()),
        ];
    }
}
