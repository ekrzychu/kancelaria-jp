<?php

namespace App\Filament\Resources\Pages;

use Filament\Actions\Action;
use Filament\Support\Components\Attributes\Exposed;

trait PublishesOnCreate
{
    protected bool $publishing = false;

    #[Exposed]
    public function publish(): void
    {
        $this->publishing = true;
        try {
            $this->create();
        } finally {
            $this->publishing = false;
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('publish')->label('Opublikuj')->action('publish'),
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Zapisz szkic')->color('gray');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return $this->publishing ? 'Opublikowano' : 'Zapisano szkic';
    }
}
