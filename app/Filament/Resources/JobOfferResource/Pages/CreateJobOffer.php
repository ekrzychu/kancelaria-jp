<?php

namespace App\Filament\Resources\JobOfferResource\Pages;

use App\Actions\ManageContent;
use App\Content\JobStatus;
use App\Filament\Resources\JobOfferResource;
use App\Filament\Resources\Pages\CreateContent;
use App\Filament\Resources\Pages\PublishesOnCreate;
use App\Models\JobOffer;
use Illuminate\Database\Eloquent\Model;

final class CreateJobOffer extends CreateContent
{
    use PublishesOnCreate;

    protected static string $resource = JobOfferResource::class;
    protected static ?string $title = 'Dodaj ofertę';

    protected function handleRecordCreation(array $data): Model
    {
        return app(ManageContent::class)->create(JobOffer::class, fn (): array => JobOfferResource::contentData($data)
            + ['status' => $this->publishing ? JobStatus::Open : JobStatus::Draft]);
    }
}
