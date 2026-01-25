<?php

namespace App\Filament\Resources\WorkOrders\Tables;

use App\Models\MeterClosed;
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

class MeterClosedTable
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
                    ->color('danger'),
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
                // Create SPTL (only when none exists)
                Action::make('create_sptl')
                    ->label('Create SPTL')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->visible(fn ($record) => ! MeterClosed::where('complaint_id', $record->id)->exists())
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
                            TextInput::make('no_sptl')
                                ->label('No. SPTL')
                                ->disabled()
                                ->default(MeterClosed::peekNextNoSPTL())
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
                            Textarea::make('alasan_tutup')
                                ->label('Alasan Tutup')
                                ->rows(3)
                                ->required(),
                            DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->default(now())
                                ->required(),
                        ];
                    })
                    ->action(function ($record, array $data) {
                        MeterClosed::create([
                            'complaint_id' => $record->id,
                            'pegawai_id' => $data['pegawai_id'],
                            'nama_pegawai' => $data['nama_pegawai'] ?? null,
                            'no_sambungan' => $record->no_sambungan,
                            'nama' => $data['nama'],
                            'alamat' => $data['alamat'],
                            'latitude' => $data['latitude'],
                            'longitude' => $data['longitude'],
                            'alasan_tutup' => $data['alasan_tutup'],
                            'tanggal' => $data['tanggal'],
                        ]);

                        Notification::make()
                            ->title('SPTL Berhasil Dibuat')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Buat Surat Perintah Tutup Langganan')
                    ->modalWidth('2xl'),
                // View SPTL (only when exists)
                Action::make('view_sptl')
                    ->label('View SPTL')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->visible(fn ($record) => MeterClosed::where('complaint_id', $record->id)->exists())
                    ->form(function ($record) {
                        $sptl = MeterClosed::where('complaint_id', $record->id)->latest()->first();

                        return [
                            TextInput::make('no_sptl')
                                ->label('No. SPTL')
                                ->default($sptl?->no_sptl)
                                ->disabled(),
                            TextInput::make('nama_pegawai')
                                ->label('Nama Pegawai')
                                ->default($sptl?->nama_pegawai)
                                ->disabled(),
                            TextInput::make('no_sambungan')
                                ->label('No Sambungan')
                                ->default($sptl?->no_sambungan)
                                ->disabled(),
                            TextInput::make('nama')
                                ->label('Nama')
                                ->default($sptl?->nama)
                                ->disabled(),
                            TextInput::make('alamat')
                                ->label('Alamat')
                                ->default($sptl?->alamat)
                                ->disabled(),
                            TextInput::make('latitude')
                                ->label('Latitude')
                                ->default($sptl?->latitude)
                                ->disabled(),
                            TextInput::make('longitude')
                                ->label('Longitude')
                                ->default($sptl?->longitude)
                                ->disabled(),
                            DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->default($sptl?->tanggal)
                                ->disabled(),
                            Textarea::make('alasan_tutup')
                                ->label('Alasan Tutup')
                                ->default($sptl?->alasan_tutup)
                                ->rows(3)
                                ->disabled(),
                        ];
                    })
                    ->modalHeading('Detail SPTL')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->defaultSort('tanggal', 'desc');
    }
}
