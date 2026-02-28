<?php

namespace App\Filament\Resources\WorkOrders\Tables;

use App\Models\MeterRateChange;
use App\Services\EmployeeLookupService;
use App\Services\CustomerLookupService;
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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
                        // Fetch customer data from API
                        $customer = null;
                        try {
                            $client = new \GuzzleHttp\Client();
                            $response = $client->request('GET', 
                                config('services.billing_api.base_uri') . '/external/customers/' . $record->no_sambungan,
                                [
                                    'headers' => ['X-App-Key' => config('services.billing_api.key')],
                                    'timeout' => 10,
                                ]
                            );
                            $responseData = json_decode($response->getBody(), true);
                            $customer = $responseData['data'] ?? null;
                        } catch (\Exception $e) {
                            Log::error('Customer API Error: ' . $e->getMessage());
                        }

                        return [
                            TextInput::make('no_sput')
                                ->label('No. SPUT')
                                ->disabled()
                                ->default(MeterRateChange::peekNextNoSPUT())
                                ->dehydrated(false),

                            // Auto-populated fields from customer API
                            TextInput::make('no_sambungan')
                                ->label('No. Sambungan')
                                ->default(fn () => $record->no_sambungan)
                                ->disabled()
                                ->dehydrated(),
                            
                            TextInput::make('nama')
                                ->label('Nama')
                                ->default(fn () => $customer ? $customer['nama_pelanggan'] ?? $record->nama : $record->nama)
                                ->disabled()
                                ->dehydrated(),
                            
                            TextInput::make('alamat')
                                ->label('Alamat')
                                ->default(fn () => $customer ? ($customer['alamat_berinvestasi'] ?? $customer['alamat'] ?? '') : '')
                                ->disabled()
                                ->dehydrated(),
                            
                            TextInput::make('email')
                                ->label('Email')
                                ->default(fn () => $customer ? ($customer['email'] ?? '') : '')
                                ->disabled()
                                ->dehydrated(),
                            
                            TextInput::make('no_hp')
                                ->label('No. HP')
                                ->default(fn () => $customer ? ($customer['no_hp'] ?? '') : '')
                                ->disabled()
                                ->dehydrated(),
                            
                            TextInput::make('no_ktp')
                                ->label('No. KTP')
                                ->default(fn () => $customer ? ($customer['no_identitas'] ?? '') : '')
                                ->disabled()
                                ->dehydrated(),
                            
                            Textarea::make('alasan_ubah_status')
                                ->label('Alasan Ubah Status')
                                ->rows(3)
                                ->required(),
                            
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
                            'no_sambungan' => $data['no_sambungan'],
                            'nama' => $data['nama'],
                            'alamat' => $data['alamat'] ?? null,
                            'email' => $data['email'] ?? null,
                            'no_hp' => $data['no_hp'] ?? null,
                            'no_ktp' => $data['no_ktp'] ?? null,
                            'alasan_ganti_tarif' => $data['alasan_ubah_status'] ?? null,
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
                            TextInput::make('no_sambungan')
                                ->label('No. Sambungan')
                                ->default($sput?->no_sambungan)
                                ->disabled(),
                            TextInput::make('nama')
                                ->label('Nama')
                                ->default($sput?->nama)
                                ->disabled(),
                            TextInput::make('alamat')
                                ->label('Alamat')
                                ->default($sput?->alamat)
                                ->disabled(),
                            TextInput::make('email')
                                ->label('Email')
                                ->default($sput?->email)
                                ->disabled(),
                            TextInput::make('no_hp')
                                ->label('No. HP')
                                ->default($sput?->no_hp)
                                ->disabled(),
                            TextInput::make('no_ktp')
                                ->label('No. KTP')
                                ->default($sput?->no_ktp)
                                ->disabled(),
                            DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->default($sput?->tanggal)
                                ->disabled(),
                            Textarea::make('alasan_ganti_tarif')
                                ->label('Alasan Ubah Status')
                                ->default($sput?->alasan_ganti_tarif)
                                ->rows(3)
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
