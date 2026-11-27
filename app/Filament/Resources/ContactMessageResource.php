<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessageResource\Pages;
use App\Models\ContactMessage;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;
use Illuminate\Support\HtmlString;

final class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;
    protected static ?string $slug = 'wiadomosci';
    protected static ?string $navigationLabel = 'Wiadomości';
    protected static ?string $modelLabel = 'wiadomość';
    protected static ?string $pluralModelLabel = 'wiadomości';
    protected static string|UnitEnum|null $navigationGroup = 'Kontakt';
    protected static ?int $navigationSort = 5;

    private const array STATUSES = ['unread' => 'Nieprzeczytana', 'read' => 'Przeczytana', 'archived' => 'Zarchiwizowana'];
    private const array DELIVERY = [
        'pending' => 'Oczekuje', 'sending' => 'Wysyłanie', 'sent' => 'Wysłana',
        'failed' => 'Błąd wysyłki', 'uncertain' => 'Wymaga sprawdzenia',
    ];

    public static function table(Table $table): Table
    {
        return $table->recordClasses(fn (ContactMessage $record): ?string => 'panel-message-'.$record->status)->columns([
            TextColumn::make('name')->extraAttributes(['class' => 'panel-record-title'])->label('Nadawca')->searchable()->wrap(),
            TextColumn::make('email')->label('Adres e-mail')->searchable()->wrap(),
            TextColumn::make('created_at')->label('Data zgłoszenia')->dateTime('d.m.Y, H:i', timezone: 'Europe/Warsaw')->sortable(),
            TextColumn::make('status')->label('Status')->extraAttributes(['class' => 'panel-table-status'])
                ->formatStateUsing(fn (string $state): string => self::STATUSES[$state]),
            TextColumn::make('delivery_status')->label('Wysyłka e-mail')->extraAttributes(['class' => 'panel-table-status'])
                ->formatStateUsing(fn (string $state): string => self::DELIVERY[$state])
                ->color(fn (string $state): string => match ($state) {
                    'sent' => 'success', 'failed', 'uncertain' => 'danger', default => 'gray',
                }),
        ])->stackedOnMobile()->defaultSort('created_at', 'desc')->filters([
            SelectFilter::make('status')->label('Status')->options(self::STATUSES),
            SelectFilter::make('delivery_status')->label('Wysyłka e-mail')->options(self::DELIVERY),
        ])->recordActions([ViewAction::make()->label('Otwórz')->icon(null)])->emptyStateHeading('Brak wiadomości');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Wiadomość')->schema([
                TextEntry::make('message')->hiddenLabel()->formatStateUsing(fn (string $state): HtmlString => new HtmlString(
                    '<span class="panel-message-text">'.e($state).'</span>',
                )),
            ])->columnSpanFull(),
            Section::make('Dane kontaktowe')->schema([
                TextEntry::make('email')->label('Adres e-mail'),
                TextEntry::make('phone')->label('Telefon')->placeholder('—'),
                TextEntry::make('created_at')->label('Data zgłoszenia')->dateTime('d.m.Y, H:i', timezone: 'Europe/Warsaw'),
            ])->columnSpanFull()->columns(3),
            Section::make('Status i wysyłka e-mail')->schema([
                TextEntry::make('status')->label('Status')->formatStateUsing(fn (string $state): string => self::STATUSES[$state]),
                TextEntry::make('delivery_status')->label('Wysyłka e-mail')->formatStateUsing(fn (string $state): string => self::DELIVERY[$state]),
                TextEntry::make('delivery_attempts')->label('Liczba prób wysyłki'),
                TextEntry::make('last_attempt_at')->label('Ostatnia próba')->dateTime('d.m.Y, H:i', timezone: 'Europe/Warsaw')->placeholder('—'),
                TextEntry::make('next_attempt_at')->label('Następna próba')->dateTime('d.m.Y, H:i', timezone: 'Europe/Warsaw')->placeholder('—'),
                TextEntry::make('sent_at')->label('Wysłano')->dateTime('d.m.Y, H:i', timezone: 'Europe/Warsaw')->placeholder('—'),
            ])->columnSpanFull()->columns(3)->collapsible(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactMessages::route('/'),
            'view' => Pages\ViewContactMessage::route('/{record}'),
        ];
    }
}
