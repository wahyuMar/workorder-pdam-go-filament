<?php

namespace App\Filament\Resources\WorkOrders\Tables;

use App\Models\MeterReplacement;
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

class MeterReplacementTable
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
                    ->color('success'),
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
                Action::make('create_spgm')
                    ->label('Create SPGM')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->form(function ($record) {
                        $employeeService = app(EmployeeLookupService::class);
                        $employeesData = $employeeService->fetchEmployees();
                        
                        $employeeOptions = [];
                        if ($employeesData['data']) {
                            foreach ($employeesData['data'] as $employee) {
                                $employeeOptions[$employee['id']] = $employee['nama_pegawai'] ?? $employee['name'] ?? 'Unknown';
                            }
                        }

                        return [
                            TextInput::make('no_spgm')
                                ->label('No. SPGM')
                                ->disabled()
                                ->default(MeterReplacement::peekNextNoSPGM())
                                ->dehydrated(false),
                            Select::make('pegawai_id')
                                ->label('Pegawai')
                                ->options($employeeOptions)
                                ->searchable()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) use ($employeesData) {
                                    if ($employeesData['data']) {
                                        $employee = collect($employeesData['data'])->firstWhere('id', $state);
                                        if ($employee) {
                                            $set('nama_pegawai', $employee['nama_pegawai'] ?? $employee['name'] ?? null);
                                        }
                                    }
                                }),
                            TextInput::make('no_sambungan')
                                ->label('No Sambungan')
                                ->default(fn () => $record->no_sambungan)
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('nama')
                                ->label('Nama')
                                ->default(fn () => $record->nama)
                                ->required(),
                            TextInput::make('alamat')
                                ->label('Alamat')
                                ->default(fn () => $record->alamat),
                            TextInput::make('latitude')
                                ->label('Latitude')
                                ->numeric()
                                ->default(fn () => $record->latitude),
                            TextInput::make('longitude')
                                ->label('Longitude')
                                ->numeric()
                                ->default(fn () => $record->longitude),
                            Textarea::make('alasan_penggantian')
                                ->label('Alasan Penggantian')
                                ->rows(3),
                            TextInput::make('biaya_ganti_meter')
                                ->label('Biaya Ganti Meter')
                                ->numeric()
                                ->prefix('Rp')
                                ->required(),
                            DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->default(now())
                                ->required(),
                            TextInput::make('nama_pegawai')
                                ->hidden()
                                ->dehydrated(),
                        ];
                    })
                    ->action(function ($record, array $data) {
                        MeterReplacement::create([
                            'complaint_id' => $record->id,
                            'pegawai_id' => $data['pegawai_id'],
                            'nama_pegawai' => $data['nama_pegawai'],
                            'no_sambungan' => $record->no_sambungan,
                            'nama' => $data['nama'],
                            'alamat' => $data['alamat'],
                            'latitude' => $data['latitude'],
                            'longitude' => $data['longitude'],
                            'alasan_penggantian' => $data['alasan_penggantian'],
                            'biaya_ganti_meter' => $data['biaya_ganti_meter'],
                            'tanggal' => $data['tanggal'],
                        ]);

                        Notification::make()
                            ->title('SPGM Berhasil Dibuat')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Buat Surat Perintah Ganti Meter')
                    ->modalWidth('2xl'),
                // ViewAction::make()
                //     ->url(fn ($record) => route('filament.admin.resources.complaints.view', ['record' => $record])),
            ])
            ->defaultSort('tanggal', 'desc');
    }
}
