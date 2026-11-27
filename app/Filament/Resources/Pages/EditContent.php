<?php

namespace App\Filament\Resources\Pages;

use App\Actions\ManageContent;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;

abstract class EditContent extends EditRecord
{
    protected Width|string|null $maxContentWidth = Width::FiveExtraLarge;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $resource = static::getResource();
        $current = app(ManageContent::class)->change($record, 'update',
            fn (Model $current) => $current->update($resource::contentData($data, $current)));
        $record->setRawAttributes($current->getAttributes(), sync: true);

        return $record;
    }
}
