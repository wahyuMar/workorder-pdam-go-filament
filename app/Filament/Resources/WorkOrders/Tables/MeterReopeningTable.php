<?php

namespace App\Filament\Resources\WorkOrders\Tables;

use App\Models\MeterReopening;
use App\Services\EmployeeLookupService;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MeterReopeningTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_pengaduan')
                    ->label('No Pengaduan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tanggal')
                    ->label('Tanggal Pengaduan')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('complaintType.name')
                    ->label('Jenis Pengaduan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('judul_pengaduan')
                    ->label('Judul Pengaduan')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('no_sambungan')
                    ->label('No. Sambungan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('followUps.work_order')
                    ->label('Work Order')
                    ->badge()
                    ->color('info'),
                TextColumn::make('followUps.follow_up_at')
                    ->label('Tanggal Tindak Lanjut')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in_progress' => 'warning',
                        'resolved' => 'success',
                        'closed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('no_hp')
                    ->label('Phone No.')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('complaint_type_id')
                    ->label('Complaint Type')
                    ->relationship('complaintType', 'name'),
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ]),
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn ($record) => route('filament.admin.resources.complaints.view', ['record' => $record])),
                // Create SPBK (only when none exists)
                Action::make('create_spbk')
                    ->label('Create SPBK')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->visible(fn ($record) => ! MeterReopening::where('complaint_id', $record->id)->exists())
                    ->form(function ($record) {
                        $employeeService = app(EmployeeLookupService::class);
                        $employeesData = $employeeService->fetchEmployees();
                        
                        $employees = collect($employeesData['data'] ?? []);

                        $employeeOptions = $employees
                            ->mapWithKeys(fn ($employee) => [
                                $employee['id'] => $employee['nama_pegawai'] ?? $employee['nama'] ?? $employee['name'] ?? 'Unknown',
                            ])
                            ->all();

                        return [
                            TextInput::make('no_spbk')
                                ->label('No. SPBK')
                                ->disabled()
                                ->default(MeterReopening::peekNextNoSPBK())
                                ->dehydrated(false),
                            Select::make('pegawai_id')
                                ->label('Pegawai')
                                ->options($employeeOptions)
                                ->searchable()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) use ($employees) {
                                    $employee = $employees->firstWhere('id', $state);
                                    if ($employee) {
                                        $set('nama_pegawai', $employee['nama_pegawai'] ?? $employee['nama'] ?? $employee['name'] ?? null);
                                    }
                                }),
                            TextInput::make('nama_pegawai')
                                ->label('Nama Pegawai')
                                ->disabled()
                                ->dehydrated()
                                ->default(fn (callable $get) => $get('nama_pegawai')),

                            TextInput::make('no_sambungan')
                                ->label('No Sambungan')
                                ->default(fn () => $record->no_sambungan)
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('nama')
                                ->label('Nama')
                                ->default(fn () => $record->nama)
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('alamat')
                                ->label('Alamat')
                                ->disabled()
                                ->dehydrated()
                                ->default(fn () => $record->alamat),
                            TextInput::make('latitude')
                                ->label('Latitude')
                                ->numeric()
                                ->default(fn () => $record->latitude)
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('longitude')
                                ->label('Longitude')
                                ->numeric()
                                ->disabled()
                                ->dehydrated()
                                ->default(fn () => $record->longitude),
                            Textarea::make('alasan_buka_kembali')
                                ->label('Alasan Buka Kembali')
                                ->rows(3)
                                ->required(),
                            TextInput::make('biaya_buka_kembali')
                                ->label('Biaya Buka Kembali')
                                ->numeric()
                                ->prefix('Rp')
                                ->required(),
                            DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->default(now())
                                ->required(),
                        ];
                    })
                    ->action(function ($record, array $data) {
                        MeterReopening::create([
                            'complaint_id' => $record->id,
                            'pegawai_id' => $data['pegawai_id'],
                            'nama_pegawai' => $data['nama_pegawai'] ?? null,
                            'no_sambungan' => $record->no_sambungan,
                            'nama' => $data['nama'],
                            'alamat' => $data['alamat'],
                            'latitude' => $data['latitude'],
                            'longitude' => $data['longitude'],
                            'alasan_buka_kembali' => $data['alasan_buka_kembali'],
                            'biaya_buka_kembali' => $data['biaya_buka_kembali'],
                            'tanggal' => $data['tanggal'],
                        ]);

                        Notification::make()
                            ->title('SPBK Berhasil Dibuat')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Buat Surat Perintah Buka Kembali')
                    ->modalWidth('2xl'),
                // View SPBK (only when exists)
                Action::make('view_spbk')
                    ->label('View SPBK')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->visible(fn ($record) => MeterReopening::where('complaint_id', $record->id)->exists())
                    ->form(function ($record) {
                        $spbk = MeterReopening::where('complaint_id', $record->id)->latest()->first();

                        return [
                            TextInput::make('no_spbk')
                                ->label('No. SPBK')
                                ->default($spbk?->no_spbk)
                                ->disabled(),
                            TextInput::make('nama_pegawai')
                                ->label('Nama Pegawai')
                                ->default($spbk?->nama_pegawai)
                                ->disabled(),
                            TextInput::make('no_sambungan')
                                ->label('No Sambungan')
                                ->default($spbk?->no_sambungan)
                                ->disabled(),
                            TextInput::make('nama')
                                ->label('Nama')
                                ->default($spbk?->nama)
                                ->disabled(),
                            TextInput::make('alamat')
                                ->label('Alamat')
                                ->default($spbk?->alamat)
                                ->disabled(),
                            TextInput::make('latitude')
                                ->label('Latitude')
                                ->default($spbk?->latitude)
                                ->disabled(),
                            TextInput::make('longitude')
                                ->label('Longitude')
                                ->default($spbk?->longitude)
                                ->disabled(),
                            TextInput::make('biaya_buka_kembali')
                                ->label('Biaya Buka Kembali')
                                ->default($spbk?->biaya_buka_kembali)
                                ->disabled(),
                            DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->default($spbk?->tanggal)
                                ->disabled(),
                            Textarea::make('alasan_buka_kembali')
                                ->label('Alasan Buka Kembali')
                                ->default($spbk?->alasan_buka_kembali)
                                ->rows(3)
                                ->disabled(),
                        ];
                    })
                    ->modalHeading('Detail SPBK')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->defaultSort('tanggal', 'desc');
    }
}
