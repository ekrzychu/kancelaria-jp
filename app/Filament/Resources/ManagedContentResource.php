<?php

namespace App\Filament\Resources;

use App\Actions\ManageContent;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Validator;

abstract class ManagedContentResource extends Resource
{
    protected const array FILTERS_BLOCKING_MOVES = [];

    abstract public static function contentData(array $data, ?Model $record = null): array;

    protected static function validateContent(array $data, array $rules): array
    {
        $paths = array_map(fn (string $key): string => 'data.'.$key, array_keys($rules));

        return Validator::make(['data' => $data], array_combine($paths, $rules))->validate()['data'];
    }

    protected static function directoryTable(Table $table, array $filters, array $actions = []): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withoutGlobalScope(SoftDeletingScope::class))
            ->stackedOnMobile()
            ->defaultSort('display_order')
            ->filters([...$filters, TrashedFilter::make()->label('Kosz')])
            ->recordActions([
                EditAction::make()->label('Edytuj')->icon(null)->hidden(fn (Model $record): bool => $record->trashed()),
                ...$actions,
                ActionGroup::make([
                    static::moveAction(-1),
                    static::moveAction(1),
                    DeleteAction::make()->label('Przenieś do kosza')
                        ->using(fn (Model $record): bool => app(ManageContent::class)
                            ->change($record, 'delete', fn (Model $current) => $current->delete())->trashed()),
                    RestoreAction::make()->label('Przywróć')
                        ->modalDescription(static::restoreDescription())
                        ->using(function (Model $record): bool {
                            return ! app(ManageContent::class)->change($record, 'restore', function (Model $current): void {
                                static::beforeRestore($current);
                                $current->restore();
                            })->trashed();
                        }),
                ])->label('Więcej działań')->icon(Heroicon::EllipsisHorizontal)->color('gray'),
            ]);
    }

    protected static function beforeRestore(Model $record): void {}

    protected static function restoreDescription(): string
    {
        return 'Element zostanie przywrócony. Jego widoczność ustawisz osobno.';
    }

    private static function moveAction(int $direction): Action
    {
        return Action::make($direction < 0 ? 'moveUp' : 'moveDown')
            ->label($direction < 0 ? 'Przesuń wyżej' : 'Przesuń niżej')
            ->authorize('reorder')
            ->visible(fn (Model $record, HasTable $livewire): bool => ! $record->trashed() && static::canMoveRows($livewire))
            ->action(function (Model $record, HasTable $livewire) use ($direction): void {
                abort_unless(static::canMoveRows($livewire), 403);
                if (! app(ManageContent::class)->move($record, $direction)) {
                    Notification::make()->title('Brak pozycji w tym kierunku')->send();
                }
            });
    }

    private static function canMoveRows(HasTable $livewire): bool
    {
        if ($livewire->hasTableSearch()) {
            return false;
        }
        foreach ([...static::FILTERS_BLOCKING_MOVES, 'trashed'] as $filter) {
            if (filled($livewire->getTableFilterState($filter)['value'] ?? null)) {
                return false;
            }
        }

        return true;
    }
}
