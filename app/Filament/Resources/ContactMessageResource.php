<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessageResource\Pages;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;
    protected static ?string $slug = 'wiadomosci';
    protected static ?string $navigationLabel = 'Wiadomości';
    protected static ?string $modelLabel = 'wiadomość';
    protected static ?string $pluralModelLabel = 'wiadomości';
    protected static ?int $navigationSort = 5;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    private const array STATUSES = ['unread' => 'Nieprzeczytana', 'read' => 'Przeczytana', 'archived' => 'Zarchiwizowana'];
    private const array DELIVERY = [
        'pending' => 'Oczekuje', 'sending' => 'Wysyłanie', 'sent' => 'Wysłana',
        'failed' => 'Błąd wysyłki', 'uncertain' => 'Wymaga sprawdzenia',
    ];

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('created_at')->label('Data zgłoszenia')->dateTime('d.m.Y, H:i', timezone: 'Europe/Warsaw')->sortable(),
            TextColumn::make('name')->label('Nadawca')->searchable()->wrap(),
            TextColumn::make('email')->label('Adres e-mail')->searchable()->wrap(),
            TextColumn::make('status')->label('Status')->badge()
                ->formatStateUsing(fn (string $state): string => self::STATUSES[$state])
                ->color(fn (string $state): string => $state === 'unread' ? 'primary' : 'gray'),
            TextColumn::make('delivery_status')->label('Wysyłka e-mail')->badge()
                ->formatStateUsing(fn (string $state): string => self::DELIVERY[$state])
                ->color(fn (string $state): string => match ($state) {
                    'sent' => 'success', 'failed', 'uncertain' => 'danger', default => 'gray',
                }),
        ])->defaultSort('created_at', 'desc')->filters([
            SelectFilter::make('status')->label('Status')->options(self::STATUSES),
            SelectFilter::make('delivery_status')->label('Wysyłka e-mail')->options(self::DELIVERY),
        ])->recordActions([ViewAction::make()->label('Otwórz')]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name')->label('Imię i nazwisko'),
            TextEntry::make('email')->label('Adres e-mail'),
            TextEntry::make('phone')->label('Telefon')->placeholder('—'),
            TextEntry::make('created_at')->label('Data zgłoszenia')->dateTime('d.m.Y, H:i', timezone: 'Europe/Warsaw'),
            TextEntry::make('status')->label('Status')->formatStateUsing(fn (string $state): string => self::STATUSES[$state]),
            TextEntry::make('delivery_status')->label('Wysyłka e-mail')->formatStateUsing(fn (string $state): string => self::DELIVERY[$state]),
            TextEntry::make('delivery_attempts')->label('Liczba prób wysyłki'),
            TextEntry::make('next_attempt_at')->label('Następna próba')->dateTime('d.m.Y, H:i', timezone: 'Europe/Warsaw')->placeholder('—'),
            TextEntry::make('sent_at')->label('Wysłano')->dateTime('d.m.Y, H:i', timezone: 'Europe/Warsaw')->placeholder('—'),
            TextEntry::make('message')->label('Treść wiadomości')->columnSpanFull()
                ->extraAttributes(['style' => 'white-space: pre-wrap; overflow-wrap: anywhere']),
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
