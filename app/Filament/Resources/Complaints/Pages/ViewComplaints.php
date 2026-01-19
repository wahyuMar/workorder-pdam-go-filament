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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('tindak_lanjut')
                ->label('Tindak Lanjut')
                ->color('warning')
                ->icon('heroicon-o-document-text')
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
            // EditAction::make(),
            // DeleteAction::make(),
        ];
    }
}
