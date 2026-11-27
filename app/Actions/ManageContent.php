<?php

namespace App\Actions;

use App\Models\TeamMember;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class ManageContent
{
    public function create(string $model, Closure $attributes, ?Closure $afterCreate = null): Model
    {
        return $this->write(function () use ($model, $attributes, $afterCreate): Model {
            Gate::authorize('create', $model);

            $record = $model::create($attributes() + ['display_order' => (int) $model::max('display_order') + 1]);
            $afterCreate?->__invoke($record);

            return $record;
        });
    }

    public function change(Model $record, string $ability, Closure $operation): Model
    {
        return $this->write(function () use ($record, $ability, $operation): Model {
            $query = $record->newQuery();
            if ($ability === 'restore') {
                $query->onlyTrashed();
            }

            $current = $query->lockForUpdate()->findOrFail($record->getKey());
            Gate::authorize($ability, $current);
            $operation($current);

            return $current;
        });
    }

    public function move(Model $record, int $direction): bool
    {
        $moved = false;
        $this->change($record, 'reorder', function (Model $current) use ($direction, &$moved): void {
            $records = $current->newQuery()->orderBy('display_order')->orderBy('id')->lockForUpdate()->get();
            $position = $records->search(fn (Model $candidate): bool => $candidate->is($current));
            for ($adjacent = $position + $direction; $records->has($adjacent); $adjacent += $direction) {
                $neighbor = $records[$adjacent];
                if (! $current instanceof TeamMember || $neighbor->team_group === $current->team_group) {
                    break;
                }
            }
            if (! $records->has($adjacent)) {
                return;
            }

            // Keep the exact existing order slots where they are unique, including across team groups.
            $duplicates = $records->pluck('display_order')->duplicates();
            if (! $duplicates->contains($current->display_order) && ! $duplicates->contains($neighbor->display_order)) {
                $previousOrder = $current->display_order;
                $current->update(['display_order' => $neighbor->display_order]);
                $neighbor->update(['display_order' => $previousOrder]);
            } else {
                $records[$position] = $neighbor;
                $records[$adjacent] = $current;
                foreach ($records as $index => $item) {
                    $item->display_order = $index + 1;
                    if ($item->isDirty('display_order')) {
                        $item::withoutTimestamps(fn () => $item->save());
                    }
                }
            }
            $moved = true;
        });

        return $moved;
    }

    private function write(Closure $operation): mixed
    {
        $connection = DB::connection();
        // Share the schema lock used by the reviewed content importer.
        $lock = 'kjp-content-'.substr(hash('sha256', $connection->getDatabaseName()), 0, 40);
        $result = $connection->selectOne('SELECT GET_LOCK(?, 5) AS acquired', [$lock], false);
        if ((int) $result->acquired !== 1) {
            throw ValidationException::withMessages(['data' => 'Trwa zapis treści. Spróbuj ponownie za chwilę.']);
        }

        try {
            return $connection->transaction($operation);
        } finally {
            $connection->selectOne('SELECT RELEASE_LOCK(?)', [$lock], false);
        }
    }
}
