<?php

namespace App\Filament\Resources\WorkOrders\Tables;

use App\Models\MeterAddressChange;
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

class MeterAddressChangeTable
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
                // Create SPUA (only when none exists)
                Action::make('create_spua')
                    ->label('Create SPUA')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->visible(fn ($record) => ! MeterAddressChange::where('complaint_id', $record->id)->exists())
                    ->form(function ($record) {
                        $employeeService = app(EmployeeLookupService::class);
                        $employeesData = $employeeService->fetchEmployees();
                        
                        $employees = collect($employeesData['data'] ?? []);

                        $employeeOptions = $employees
                            ->mapWithKeys(fn ($employee) => [
                                $employee['id'] => $employee['nama_pegawai'] ?? $employee['nama'] ?? $employee['name'] ?? 'Unknown',
                            ])
                            ->all();

                        // Fetch customer data
                        $customerService = app(\App\Services\CustomerLookupService::class);
                        $customerData = $customerService->fetchByNoSambungan($record->no_sambungan);
                        $customer = $customerData['data'] ?? null;

                        return [
                            TextInput::make('no_spua')
                                ->label('No. SPUA')
                                ->disabled()
                                ->default(MeterAddressChange::peekNextNoSPUA())
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
                            
                            // Data Lama (Old Data) - Auto-populated from API
                            TextInput::make('id_unit_lama')
                                ->label('Unit Lama')
                                ->default(fn () => $customer ? ($customer['collector']['rt_rw']['desa']['unit']['id_unit'] . ' - ' . $customer['collector']['rt_rw']['desa']['unit']['nama_unit']) : null)
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('id_desa_lama')
                                ->label('Desa/Kelurahan Lama')
                                ->default(fn () => $customer ? ($customer['collector']['rt_rw']['desa']['id_desa'] . ' - ' . $customer['collector']['rt_rw']['desa']['desa']) : null)
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('id_rt_rw_lama')
                                ->label('RT/RW Lama')
                                ->default(fn () => $customer ? ($customer['collector']['rt_rw']['id_rt_rw'] . ' - RT.' . ($customer['collector']['rt_rw']['rt'] ?? '') . '/RW.' . ($customer['collector']['rt_rw']['rw'] ?? '')) : null)
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('id_wilayah_lama')
                                ->label('Wilayah Lama')
                                ->default(fn () => $customer ? ($customer['wilayah']['id_wilayah'] . ' - ' . $customer['wilayah']['wilayah']) : null)
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('id_jalan_lama')
                                ->label('Jalan Lama')
                                ->default(fn () => $customer ? ($customer['jalan']['id_jalan'] . ' - ' . $customer['jalan']['nama_jalan']) : null)
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('id_kolektor_lama')
                                ->label('Kolektor Lama')
                                ->default(fn () => $customer ? ($customer['collector']['id_collector'] . ' - ' . $customer['collector']['nama_collector']) : null)
                                ->disabled()
                                ->dehydrated(),
                            
                            // Data Baru (New Data) - Cascading selects via CustomerLookupService
                            Select::make('id_unit_baru')
                                ->label('Unit Baru')
                                ->options(function () use ($customerService) {
                                    $units = $customerService->fetchUnits();
                                    return collect($units)?->mapWithKeys(fn ($unit) => [
                                        "{$unit['id_unit']} - {$unit['nama_unit']}" => "{$unit['id_unit']} - {$unit['nama_unit']}",
                                    ])->toArray() ?? [];
                                })
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn (callable $set) => $set('id_wilayah_baru', null)),
                            
                            Select::make('id_desa_baru')
                                ->label('Desa/Kelurahan Baru')
                                ->options(function (callable $get) use ($customerService) {
                                    $unitId = $get('id_unit_baru');
                                    if (!$unitId) return [];
                                    
                                    // Extract just the ID from the full "ID - Name" format
                                    $unitIdOnly = (int)explode(' - ', $unitId)[0];
                                    $desaList = $customerService->fetchDesaByUnit($unitIdOnly);
                                    return collect($desaList)?->mapWithKeys(fn ($desa) => [
                                        "{$desa['id_desa']} - {$desa['nama_desa']}" => "{$desa['id_desa']} - {$desa['nama_desa']}",
                                    ])->toArray() ?? [];
                                })
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn (callable $set) => $set('id_rt_rw_baru', null)),
                            
                            Select::make('id_rt_rw_baru')
                                ->label('RT/RW Baru')
                                ->options(function (callable $get) use ($customerService) {
                                    $desaId = $get('id_desa_baru');
                                    if (!$desaId) return [];
                                    
                                    // Extract just the ID from the full "ID - Name" format
                                    $desaIdOnly = (int)explode(' - ', $desaId)[0];
                                    $rtRwList = $customerService->fetchRtRwByDesa($desaIdOnly);
                                    return collect($rtRwList)?->mapWithKeys(fn ($rtRw) => [
                                        "{$rtRw['id_rt_rw']} - {$rtRw['formatted']}" => "{$rtRw['id_rt_rw']} - {$rtRw['formatted']}",
                                    ])->toArray() ?? [];
                                })
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn (callable $set) => $set('id_kolektor_baru', null)),
                            
                            Select::make('id_kolektor_baru')
                                ->label('Kolektor Baru')
                                ->options(function (callable $get) use ($customerService) {
                                    $rtRwId = $get('id_rt_rw_baru');
                                    if (!$rtRwId) return [];
                                    
                                    // Extract just the ID from the full "ID - Name" format
                                    $rtRwIdOnly = (int)explode(' - ', $rtRwId)[0];
                                    $rtRwList = $customerService->fetchRtRwByDesa($rtRwIdOnly);
                                    $rtRw = collect($rtRwList)->first();
                                    $collectors = $rtRw['collectors'] ?? [];
                                    
                                    return collect($collectors)?->mapWithKeys(fn ($collector) => [
                                        "{$collector['id_collector']} - {$collector['nama_collector']}" => "{$collector['id_collector']} - {$collector['nama_collector']}",
                                    ])->toArray() ?? [];
                                })
                                ->searchable()
                                ->required()
                                ->live(),
                            
                            Select::make('id_wilayah_baru')
                                ->label('Wilayah Baru')
                                ->options(function (callable $get) use ($customerService) {
                                    $unitId = $get('id_unit_baru');
                                    if (!$unitId) return [];
                                    
                                    // Extract just the ID from the full "ID - Name" format
                                    $unitIdOnly = (int)explode(' - ', $unitId)[0];
                                    $wilayahList = $customerService->fetchWilayahByUnit($unitIdOnly);
                                    return collect($wilayahList)?->mapWithKeys(fn ($wilayah) => [
                                        "{$wilayah['id_wilayah']} - {$wilayah['wilayah']}" => "{$wilayah['id_wilayah']} - {$wilayah['wilayah']}",
                                    ])->toArray() ?? [];
                                })
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn (callable $set) => $set('id_jalan_baru', null)),
                            
                            Select::make('id_jalan_baru')
                                ->label('Jalan Baru')
                                ->options(function (callable $get) use ($customerService) {
                                    $wilayahId = $get('id_wilayah_baru');
                                    if (!$wilayahId) return [];
                                    
                                    // Extract just the ID from the full "ID - Name" format
                                    $wilayahIdOnly = (int)explode(' - ', $wilayahId)[0];
                                    $jalanList = $customerService->fetchJalanByWilayah($wilayahIdOnly);
                                    return collect($jalanList)?->mapWithKeys(fn ($jalan) => [
                                        "{$jalan['id_jalan']} - {$jalan['nama_jalan']}" => "{$jalan['id_jalan']} - {$jalan['nama_jalan']}",
                                    ])->toArray() ?? [];
                                })
                                ->searchable()
                                ->required()
                                ->live(),
                            
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
                            
                            TextInput::make('biaya_ubah_alamat')
                                ->label('Biaya Ubah Alamat')
                                ->numeric()
                                ->default(0),
                            
                            Textarea::make('alasan_ubah_alamat')
                                ->label('Alasan Ubah Alamat')
                                ->rows(3)
                                ->required(),
                            
                            FileUpload::make('upload_ktp')
                                ->label('Upload KTP')
                                ->image()
                                ->directory('meter-address-change/ktp'),
                            FileUpload::make('upload_kk')
                                ->label('Upload KK')
                                ->image()
                                ->directory('meter-address-change/kk'),
                            
                            Toggle::make('is_confirmed')
                                ->label('Konfirmasi')
                                ->hint('Tandai untuk mengkonfirmasi perubahan alamat')
                                ->default(false)
                                ->visible(true),
                            
                            DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->default(now())
                                ->required(),
                        ];
                    })
                    ->action(function ($record, array $data) {
                        $customerService = app(\App\Services\CustomerLookupService::class);
                        
                        // Helper to extract ID from display string like "1 - Ponorogo"
                        $extractId = fn ($value) => is_string($value) && strpos($value, ' - ') !== false 
                            ? (int)explode(' - ', $value)[0] 
                            : (int)$value;
                        
                        // Helper to extract name from display string like "1 - Ponorogo"
                        $extractName = fn ($value) => is_string($value) && strpos($value, ' - ') !== false
                            ? trim(substr($value, strpos($value, ' - ') + 3))
                            : '';
                        
                        // Build lookup arrays for unit, desa, wilayah, jalan
                        $unitId = $extractId($data['id_unit_baru'] ?? null);
                        $unitsList = collect($customerService->fetchUnits() ?? [])->keyBy('id_unit')->toArray();
                        $unitName = $unitsList[$unitId]['nama_unit'] ?? $extractName($data['id_unit_baru'] ?? null);
                        
                        $desaId = $extractId($data['id_desa_baru'] ?? null);
                        $desaList = collect($customerService->fetchDesaByUnit($unitId) ?? [])->keyBy('id_desa')->toArray();
                        $desaName = $desaList[$desaId]['nama_desa'] ?? $extractName($data['id_desa_baru'] ?? null);
                        
                        $wilayahId = $extractId($data['id_wilayah_baru'] ?? null);
                        $wilayahList = collect($customerService->fetchWilayahByUnit($unitId) ?? [])->keyBy('id_wilayah')->toArray();
                        $wilayahName = $wilayahList[$wilayahId]['wilayah'] ?? $extractName($data['id_wilayah_baru'] ?? null);
                        
                        $jalanId = $extractId($data['id_jalan_baru'] ?? null);
                        $jalanList = collect($customerService->fetchJalanByWilayah($wilayahId) ?? [])->keyBy('id_jalan')->toArray();
                        $jalanName = $jalanList[$jalanId]['nama_jalan'] ?? $extractName($data['id_jalan_baru'] ?? null);
                        
                        $rtRwId = $extractId($data['id_rt_rw_baru'] ?? null);
                        $rtRwList = collect($customerService->fetchRtRwByDesa($desaId) ?? [])->keyBy('id_rt_rw')->toArray();
                        $rtRw = $rtRwList[$rtRwId] ?? [];
                        $rtRwName = $rtRw['formatted'] ?? $extractName($data['id_rt_rw_baru'] ?? null);
                        
                        $kolektorId = $extractId($data['id_kolektor_baru'] ?? null);
                        $collectors = collect($rtRw['collectors'] ?? [])->keyBy('id_collector')->toArray();
                        $kolektorName = $collectors[$kolektorId]['nama_collector'] ?? $extractName($data['id_kolektor_baru'] ?? null);

                        MeterAddressChange::create([
                            'complaint_id' => $record->id,
                            'pegawai_id' => $data['pegawai_id'],
                            'nama_pegawai' => $data['nama_pegawai'] ?? null,
                            'no_sambungan' => $record->no_sambungan,
                            'nama' => $record->nama,
                            
                            // Old data - ID and name
                            'id_unit_lama' => $extractId($data['id_unit_lama'] ?? null),
                            'nama_unit_lama' => $extractName($data['id_unit_lama'] ?? null),
                            'id_desa_lama' => $extractId($data['id_desa_lama'] ?? null),
                            'nama_desa_lama' => $extractName($data['id_desa_lama'] ?? null),
                            'id_wilayah_lama' => $extractId($data['id_wilayah_lama'] ?? null),
                            'nama_wilayah_lama' => $extractName($data['id_wilayah_lama'] ?? null),
                            'id_jalan_lama' => $extractId($data['id_jalan_lama'] ?? null),
                            'nama_jalan_lama' => $extractName($data['id_jalan_lama'] ?? null),
                            'id_rt_rw_lama' => $extractId($data['id_rt_rw_lama'] ?? null),
                            'nama_rt_rw_lama' => $extractName($data['id_rt_rw_lama'] ?? null),
                            'id_kolektor_lama' => $extractId($data['id_kolektor_lama'] ?? null),
                            'nama_kolektor_lama' => $extractName($data['id_kolektor_lama'] ?? null),
                            
                            // New data - ID and name fetched from API
                            'id_unit_baru' => $unitId,
                            'nama_unit_baru' => $unitName,
                            'id_desa_baru' => $desaId,
                            'nama_desa_baru' => $desaName,
                            'id_wilayah_baru' => $wilayahId,
                            'nama_wilayah_baru' => $wilayahName,
                            'id_jalan_baru' => $jalanId,
                            'nama_jalan_baru' => $jalanName,
                            'id_rt_rw_baru' => $rtRwId,
                            'nama_rt_rw_baru' => $rtRwName,
                            'id_kolektor_baru' => $kolektorId,
                            'nama_kolektor_baru' => $kolektorName,
                            
                            'latitude' => $data['latitude'],
                            'longitude' => $data['longitude'],
                            'biaya_ubah_alamat' => $data['biaya_ubah_alamat'] ?? 0,
                            'alasan_ubah_alamat' => $data['alasan_ubah_alamat'],
                            'upload_ktp' => $data['upload_ktp'] ?? null,
                            'upload_kk' => $data['upload_kk'] ?? null,
                            'is_confirmed' => $data['is_confirmed'] ?? false,
                            'tanggal' => $data['tanggal'],
                        ]);

                        Notification::make()
                            ->title('SPUA Berhasil Dibuat')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Buat Surat Perintah Ubah Alamat')
                    ->modalWidth('2xl'),
                // View SPUA (only when exists)
                Action::make('view_spua')
                    ->label('View SPUA')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->visible(fn ($record) => MeterAddressChange::where('complaint_id', $record->id)->exists())
                    ->form(function ($record) {
                        $spua = MeterAddressChange::where('complaint_id', $record->id)->latest()->first();

                        return [
                            TextInput::make('no_spua')
                                ->label('No. SPUA')
                                ->default($spua?->no_spua)
                                ->disabled(),
                            TextInput::make('nama_pegawai')
                                ->label('Nama Pegawai')
                                ->default($spua?->nama_pegawai)
                                ->disabled(),
                            TextInput::make('no_sambungan')
                                ->label('No. Sambungan')
                                ->default($spua?->no_sambungan)
                                ->disabled(),
                            TextInput::make('nama')
                                ->label('Nama')
                                ->default($spua?->nama)
                                ->disabled(),
                            
                            // ===== ALAMAT LAMA =====
                            TextInput::make('nama_unit_lama')
                                ->label('Unit Lama')
                                ->default($spua?->nama_unit_lama)
                                ->disabled(),
                            TextInput::make('nama_wilayah_lama')
                                ->label('Wilayah Lama')
                                ->default($spua?->nama_wilayah_lama)
                                ->disabled(),
                            TextInput::make('nama_jalan_lama')
                                ->label('Jalan Lama')
                                ->default($spua?->nama_jalan_lama)
                                ->disabled(),
                            TextInput::make('nama_desa_lama')
                                ->label('Desa Lama')
                                ->default($spua?->nama_desa_lama)
                                ->disabled(),
                            TextInput::make('nama_rt_rw_lama')
                                ->label('RT/RW Lama')
                                ->default($spua?->nama_rt_rw_lama)
                                ->disabled(),
                            TextInput::make('nama_kolektor_lama')
                                ->label('Kolektor Lama')
                                ->default($spua?->nama_kolektor_lama)
                                ->disabled(),
                            
                            // ===== ALAMAT BARU =====
                            TextInput::make('nama_unit_baru')
                                ->label('Unit Baru')
                                ->default($spua?->nama_unit_baru)
                                ->disabled(),
                            TextInput::make('nama_wilayah_baru')
                                ->label('Wilayah Baru')
                                ->default($spua?->nama_wilayah_baru)
                                ->disabled(),
                            TextInput::make('nama_jalan_baru')
                                ->label('Jalan Baru')
                                ->default($spua?->nama_jalan_baru)
                                ->disabled(),
                            TextInput::make('nama_desa_baru')
                                ->label('Desa Baru')
                                ->default($spua?->nama_desa_baru)
                                ->disabled(),
                            TextInput::make('nama_rt_rw_baru')
                                ->label('RT/RW Baru')
                                ->default($spua?->nama_rt_rw_baru)
                                ->disabled(),
                            TextInput::make('nama_kolektor_baru')
                                ->label('Kolektor Baru')
                                ->default($spua?->nama_kolektor_baru)
                                ->disabled(),
                            
                            TextInput::make('latitude')
                                ->label('Latitude')
                                ->default($spua?->latitude)
                                ->disabled(),
                            TextInput::make('longitude')
                                ->label('Longitude')
                                ->default($spua?->longitude)
                                ->disabled(),
                            DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->default($spua?->tanggal)
                                ->disabled(),
                            TextInput::make('biaya_ubah_alamat')
                                ->label('Biaya Ubah Alamat')
                                ->default($spua?->biaya_ubah_alamat)
                                ->disabled(),
                            Textarea::make('alasan_ubah_alamat')
                                ->label('Alasan Ubah Alamat')
                                ->default($spua?->alasan_ubah_alamat)
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
                    ->modalHeading('Detail SPUA')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->defaultSort('tanggal', 'desc');
    }
}
