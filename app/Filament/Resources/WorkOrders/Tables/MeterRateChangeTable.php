<?php

namespace App\Filament\Resources\WorkOrders\Tables;

use App\Models\MeterRateChange;
use App\Services\EmployeeLookupService;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MeterRateChangeTable
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
                // Create SPUT (only when none exists)
                Action::make('create_sput')
                    ->label('Create SPUT')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->visible(fn ($record) => ! MeterRateChange::where('complaint_id', $record->id)->exists())
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
                            TextInput::make('no_sput')
                                ->label('No. SPUT')
                                ->disabled()
                                ->default(MeterRateChange::peekNextNoSPUT())
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
                                ->label('No. Sambungan')
                                ->default(fn () => $record->no_sambungan)
                                ->disabled()
                                ->dehydrated(),
                            
                            TextInput::make('nama')
                                ->label('Nama')
                                ->default(fn () => $record->nama)
                                ->disabled()
                                ->dehydrated(),
                            
                            TextInput::make('tarif_lama')
                                ->label('Tarif Lama')
                                ->required(),
                            TextInput::make('tarif_baru')
                                ->label('Tarif Baru')
                                ->required(),
                            
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
                            
                            Textarea::make('alasan_ganti_tarif')
                                ->label('Alasan Ganti Tarif')
                                ->rows(3)
                                ->required(),
                            
                            FileUpload::make('upload_ktp')
                                ->label('Upload KTP')
                                ->image()
                                ->directory('meter-rate-change/ktp'),
                            FileUpload::make('upload_kk')
                                ->label('Upload KK')
                                ->image()
                                ->directory('meter-rate-change/kk'),
                            
                            Toggle::make('is_confirmed')
                                ->label('Konfirmasi')
                                ->default(false),
                            
                            DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->default(now())
                                ->required(),
                        ];
                    })
                    ->action(function ($record, array $data) {
                        MeterRateChange::create([
                            'complaint_id' => $record->id,
                            'pegawai_id' => $data['pegawai_id'],
                            'nama_pegawai' => $data['nama_pegawai'] ?? null,
                            'no_sambungan' => $record->no_sambungan,
                            'nama' => $record->nama,
                            'tarif_lama' => $data['tarif_lama'],
                            'tarif_baru' => $data['tarif_baru'],
                            'alamat' => $record->alamat,
                            'email' => $record->email,
                            'no_hp' => $record->no_hp,
                            'latitude' => $data['latitude'],
                            'longitude' => $data['longitude'],
                            'alasan_ganti_tarif' => $data['alasan_ganti_tarif'],
                            'upload_ktp' => $data['upload_ktp'] ?? null,
                            'upload_kk' => $data['upload_kk'] ?? null,
                            'is_confirmed' => $data['is_confirmed'] ?? false,
                            'tanggal' => $data['tanggal'],
                        ]);

                        Notification::make()
                            ->title('SPUT Berhasil Dibuat')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Buat Surat Perintah Ubah Tarif')
                    ->modalWidth('2xl'),
                // View SPUT (only when exists)
                Action::make('view_sput')
                    ->label('View SPUT')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->visible(fn ($record) => MeterRateChange::where('complaint_id', $record->id)->exists())
                    ->form(function ($record) {
                        $sput = MeterRateChange::where('complaint_id', $record->id)->latest()->first();

                        return [
                            TextInput::make('no_sput')
                                ->label('No. SPUT')
                                ->default($sput?->no_sput)
                                ->disabled(),
                            TextInput::make('nama_pegawai')
                                ->label('Nama Pegawai')
                                ->default($sput?->nama_pegawai)
                                ->disabled(),
                            TextInput::make('no_sambungan')
                                ->label('No. Sambungan')
                                ->default($sput?->no_sambungan)
                                ->disabled(),
                            TextInput::make('nama')
                                ->label('Nama')
                                ->default($sput?->nama)
                                ->disabled(),
                            TextInput::make('tarif_lama')
                                ->label('Tarif Lama')
                                ->default($sput?->tarif_lama)
                                ->disabled(),
                            TextInput::make('tarif_baru')
                                ->label('Tarif Baru')
                                ->default($sput?->tarif_baru)
                                ->disabled(),
                            TextInput::make('latitude')
                                ->label('Latitude')
                                ->default($sput?->latitude)
                                ->disabled(),
                            TextInput::make('longitude')
                                ->label('Longitude')
                                ->default($sput?->longitude)
                                ->disabled(),
                            DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->default($sput?->tanggal)
                                ->disabled(),
                            Textarea::make('alasan_ganti_tarif')
                                ->label('Alasan Ganti Tarif')
                                ->default($sput?->alasan_ganti_tarif)
                                ->rows(3)
                                ->disabled(),
                            FileUpload::make('upload_ktp')
                                ->label('Upload KTP')
                                ->image()
                                ->downloadable()
                                ->openable()
                                ->imagePreviewHeight('250')
                                ->panelLayout('grid')
                                ->disabled(),
                            FileUpload::make('upload_kk')
                                ->label('Upload KK')
                                ->image()
                                ->downloadable()
                                ->openable()
                                ->imagePreviewHeight('250')
                                ->panelLayout('grid')
                                ->disabled(),
                            
                            Toggle::make('is_confirmed')
                                ->label('Konfirmasi')
                                ->disabled(),
                        ];
                    })
                    ->modalHeading('Detail SPUT')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->defaultSort('tanggal', 'desc');
    }
}
