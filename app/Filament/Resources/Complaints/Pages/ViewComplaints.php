<?php

namespace App\Filament\Resources\Complaints\Pages;

use App\Enums\WorkOrderEnum;
use App\Filament\Resources\Complaints\ComplaintsResource;
use App\Models\ComplaintFollowUp;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewComplaints extends ViewRecord
{
    protected static string $resource = ComplaintsResource::class;

    public function getTitle(): string
    {
        return 'Detail Pengaduan - ' . $this->record->no_pengaduan;
    }

    public function getBreadcrumb(): string
    {
        return $this->record->no_pengaduan;
    }

    protected function getHeaderActions(): array
    {
        $hasFollowUp = $this->record->followUps()->exists();
        
        return [
            Action::make('tindak_lanjut')
                ->label($hasFollowUp ? 'Lihat Tindak Lanjut' : 'Tindak Lanjut')
                ->color($hasFollowUp ? 'info' : 'warning')
                ->icon($hasFollowUp ? 'heroicon-o-eye' : 'heroicon-o-document-text')
                ->visible(fn () => !$hasFollowUp)
                ->form([
                    TextInput::make('complaint_number')
                        ->label('No. Pengaduan')
                        ->default(fn () => $this->record->no_pengaduan)
                        ->disabled()
                        ->dehydrated(),
                    TagsInput::make('carbon_copies')
                        ->label('CC')
                        ->placeholder('Tambah nama CC')
                        ->helperText('Tekan Enter untuk menambah'),
                    Select::make('work_order')
                        ->label('Work Order')
                        ->options(WorkOrderEnum::toArray())
                        ->searchable()
                        ->required(),
                    Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(4)
                        ->required(),
                    FileUpload::make('photos')
                        ->label('Foto')
                        ->image()
                        ->multiple()
                        ->maxFiles(5)
                        ->directory('complaint-followups'),
                    DateTimePicker::make('follow_up_at')
                        ->label('Tanggal')
                        ->default(now())
                        ->required(),
                ])
                ->action(function (array $data) {
                    ComplaintFollowUp::create([
                        'complaint_id' => $this->record->id,
                        'complaint_number' => $data['complaint_number'],
                        'carbon_copies' => $data['carbon_copies'] ?? null,
                        'work_order' => $data['work_order'],
                        'notes' => $data['notes'],
                        'photos' => $data['photos'] ?? null,
                        'follow_up_at' => $data['follow_up_at'],
                    ]);

                    $this->record->update(['status' => 'in_progress']);
                    $this->record->refresh();

                    Notification::make()
                        ->title('Tindak lanjut berhasil disimpan')
                        ->body('Data tindak lanjut telah ditambahkan.')
                        ->success()
                        ->send();
                }),
            Action::make('lihat_tindak_lanjut')
                ->label('Lihat Tindak Lanjut')
                ->color('info')
                ->icon('heroicon-o-eye')
                ->visible(fn () => $hasFollowUp)
                ->modalHeading(fn () => 'Detail Tindak Lanjut - ' . $this->record->no_pengaduan)
                ->modalWidth('2xl')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->fillForm(function () {
                    $followUp = $this->record->followUps()->latest()->first();
                    return [
                        'complaint_number' => $followUp->complaint_number,
                        'carbon_copies' => $followUp->carbon_copies,
                        'work_order' => $followUp->work_order,
                        'notes' => $followUp->notes,
                        'photos' => $followUp->photos,
                        'follow_up_at' => $followUp->follow_up_at,
                    ];
                })
                ->form([
                    TextInput::make('complaint_number')
                        ->label('No. Pengaduan')
                        ->disabled(),
                    TagsInput::make('carbon_copies')
                        ->label('CC')
                        ->disabled(),
                    Select::make('work_order')
                        ->label('Work Order')
                        ->options(WorkOrderEnum::toArray())
                        ->disabled(),
                    Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(4)
                        ->disabled(),
                    FileUpload::make('photos')
                        ->label('Foto')
                        ->image()
                        ->multiple()
                        ->openable()
                        ->downloadable()
                        ->imagePreviewHeight('250')
                        ->panelLayout('grid')
                        ->disabled(),
                    DateTimePicker::make('follow_up_at')
                        ->label('Tanggal')
                        ->disabled(),
                ]),
        ];
    }
}
