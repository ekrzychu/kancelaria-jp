<?php

namespace App\Filament\Resources\ContactMessageResource\Pages;

use App\Filament\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Components\Attributes\Unexposed;
use Illuminate\Support\Facades\Gate;

final class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;
    protected static ?string $title = 'Wiadomość';

    #[Unexposed]
    public function mount(int|string $record): void
    {
        ContactMessageResource::authorizeViewAny();
        parent::mount($record);

        $message = $this->getRecord();
        Gate::authorize('manage', $message);
        if (ContactMessage::whereKey($message->getKey())->where('status', 'unread')->update(['status' => 'read'])) {
            $message->refresh();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('markUnread')->label('Oznacz jako nieprzeczytaną')
                ->authorize('manage')->visible(fn (ContactMessage $record): bool => $record->status === 'read')
                ->action(function (ContactMessage $record): void {
                    Gate::authorize('manage', $record);
                    ContactMessage::whereKey($record->id)->where('status', 'read')->update(['status' => 'unread']);
                    $this->redirect(ContactMessageResource::getUrl());
                }),
            Action::make('archive')->label('Archiwizuj')->color('gray')->requiresConfirmation()
                ->authorize('manage')->visible(fn (ContactMessage $record): bool => $record->status !== 'archived')
                ->action(function (ContactMessage $record): void {
                    Gate::authorize('manage', $record);
                    ContactMessage::whereKey($record->id)->where('status', '!=', 'archived')
                        ->update(['status' => 'archived', 'archived_at' => now()]);
                    $record->refresh();
                }),
            Action::make('retryDelivery')->label('Ponów wysyłkę')->requiresConfirmation()
                ->modalDescription('Jeśli wynik poprzedniej próby jest nieznany, najpierw sprawdź skrzynkę odbiorczą.')
                ->authorize('manage')->visible(fn (ContactMessage $record): bool => in_array($record->delivery_status, ['failed', 'uncertain'], true))
                ->action(function (ContactMessage $record): void {
                    Gate::authorize('manage', $record);
                    ContactMessage::whereKey($record->id)->whereIn('delivery_status', ['failed', 'uncertain'])
                        ->update(['next_attempt_at' => now()]);
                    $record->refresh();
                }),
        ];
    }
}
