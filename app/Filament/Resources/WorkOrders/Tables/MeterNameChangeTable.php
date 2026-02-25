<?php

namespace App\Filament\Resources\WorkOrders\Tables;

use App\Models\MeterNameChange;
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

class MeterNameChangeTable
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
                    ->color('warning'),
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
                // Create SPUN (only when none exists)
                Action::make('create_spun')
                    ->label('Create SPUN')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->visible(fn ($record) => ! MeterNameChange::where('complaint_id', $record->id)->exists())
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
                            TextInput::make('no_spun')
                                ->label('No. SPUN')
                                ->disabled()
                                ->default(MeterNameChange::peekNextNoSPUN())
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
                            
                            // Data Lama (Old Data)
                            TextInput::make('nama_lama')
                                ->label('Nama Lama')
                                ->default(fn () => $record->nama)
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('alamat_lama')
                                ->label('Alamat Lama')
                                ->default(fn () => $record->alamat)
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('email_lama')
                                ->label('Email Lama')
                                ->default(fn () => $record->email)
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('no_hp_lama')
                                ->label('No. HP Lama')
                                ->default(fn () => $record->no_hp)
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('no_ktp_lama')
                                ->label('No. KTP Lama')
                                ->default(fn () => $record->no_ktp)
                                ->disabled()
                                ->dehydrated(),
                            
                            // Data Baru (New Data)
                            TextInput::make('nama_baru')
                                ->label('Nama Baru')
                                ->required(),
                            TextInput::make('alamat_baru')
                                ->label('Alamat Baru'),
                            TextInput::make('email_baru')
                                ->label('Email Baru')
                                ->email(),
                            TextInput::make('no_hp_baru')
                                ->label('No. HP Baru'),
                            TextInput::make('no_ktp_baru')
                                ->label('No. KTP Baru'),
                            
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
                            
                            Textarea::make('alasan_ubah_nama')
                                ->label('Alasan Ubah Nama')
                                ->rows(3)
                                ->required(),
                            
                            FileUpload::make('upload_ktp')
                                ->label('Upload KTP')
                                ->image()
                                ->directory('meter-name-change/ktp'),
                            FileUpload::make('upload_kk')
                                ->label('Upload KK')
                                ->image()
                                ->directory('meter-name-change/kk'),
                            
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
                        MeterNameChange::create([
                            'complaint_id' => $record->id,
                            'pegawai_id' => $data['pegawai_id'],
                            'nama_pegawai' => $data['nama_pegawai'] ?? null,
                            'no_sambungan' => $record->no_sambungan,
                            'nama_lama' => $record->nama,
                            'nama_baru' => $data['nama_baru'],
                            'alamat_lama' => $record->alamat,
                            'alamat_baru' => $data['alamat_baru'] ?? null,
                            'email_lama' => $record->email,
                            'email_baru' => $data['email_baru'] ?? null,
                            'no_hp_lama' => $record->no_hp,
                            'no_hp_baru' => $data['no_hp_baru'] ?? null,
                            'no_ktp_lama' => $record->no_ktp,
                            'no_ktp_baru' => $data['no_ktp_baru'] ?? null,
                            'latitude' => $data['latitude'],
                            'longitude' => $data['longitude'],
                            'alasan_ubah_nama' => $data['alasan_ubah_nama'],
                            'upload_ktp' => $data['upload_ktp'] ?? null,
                            'upload_kk' => $data['upload_kk'] ?? null,
                            'is_confirmed' => $data['is_confirmed'] ?? false,
                            'tanggal' => $data['tanggal'],
                        ]);

                        Notification::make()
                            ->title('SPUN Berhasil Dibuat')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Buat Surat Perintah Ubah Nama')
                    ->modalWidth('2xl'),
                // View SPUN (only when exists)
                Action::make('view_spun')
                    ->label('View SPUN')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->visible(fn ($record) => MeterNameChange::where('complaint_id', $record->id)->exists())
                    ->form(function ($record) {
                        $spun = MeterNameChange::where('complaint_id', $record->id)->latest()->first();

                        return [
                            TextInput::make('no_spun')
                                ->label('No. SPUN')
                                ->default($spun?->no_spun)
                                ->disabled(),
                            TextInput::make('nama_pegawai')
                                ->label('Nama Pegawai')
                                ->default($spun?->nama_pegawai)
                                ->disabled(),
                            TextInput::make('no_sambungan')
                                ->label('No. Sambungan')
                                ->default($spun?->no_sambungan)
                                ->disabled(),
                            
                            // Data Lama (Old Data)
                            TextInput::make('nama_lama')
                                ->label('Nama Lama')
                                ->default($spun?->nama_lama)
                                ->disabled(),
                            TextInput::make('alamat_lama')
                                ->label('Alamat Lama')
                                ->default($spun?->alamat_lama)
                                ->disabled(),
                            TextInput::make('email_lama')
                                ->label('Email Lama')
                                ->default($spun?->email_lama)
                                ->disabled(),
                            TextInput::make('no_hp_lama')
                                ->label('No. HP Lama')
                                ->default($spun?->no_hp_lama)
                                ->disabled(),
                            TextInput::make('no_ktp_lama')
                                ->label('No. KTP Lama')
                                ->default($spun?->no_ktp_lama)
                                ->disabled(),
                            
                            // Data Baru (New Data)
                            TextInput::make('nama_baru')
                                ->label('Nama Baru')
                                ->default($spun?->nama_baru)
                                ->disabled(),
                            TextInput::make('alamat_baru')
                                ->label('Alamat Baru')
                                ->default($spun?->alamat_baru)
                                ->disabled(),
                            TextInput::make('email_baru')
                                ->label('Email Baru')
                                ->default($spun?->email_baru)
                                ->disabled(),
                            TextInput::make('no_hp_baru')
                                ->label('No. HP Baru')
                                ->default($spun?->no_hp_baru)
                                ->disabled(),
                            TextInput::make('no_ktp_baru')
                                ->label('No. KTP Baru')
                                ->default($spun?->no_ktp_baru)
                                ->disabled(),
                            
                            TextInput::make('latitude')
                                ->label('Latitude')
                                ->default($spun?->latitude)
                                ->disabled(),
                            TextInput::make('longitude')
                                ->label('Longitude')
                                ->default($spun?->longitude)
                                ->disabled(),
                            DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->default($spun?->tanggal)
                                ->disabled(),
                            Textarea::make('alasan_ubah_nama')
                                ->label('Alasan Ubah Nama')
                                ->default($spun?->alasan_ubah_nama)
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
                    ->modalHeading('Detail SPUN')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->defaultSort('tanggal', 'desc');
    }
}
