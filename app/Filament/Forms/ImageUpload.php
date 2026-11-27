<?php

namespace App\Filament\Forms;

use App\Media\MediaPurpose;
use Filament\Forms\Components\Field;

final class ImageUpload extends Field
{
    protected string $view = 'filament.forms.image-upload';

    private MediaPurpose $purpose;

    public function purpose(MediaPurpose $purpose): static
    {
        $this->purpose = $purpose;

        return $this;
    }

    public function uploadUrl(): string
    {
        return route('filament.admin.media.upload', ['purpose' => $this->purpose]);
    }

    public function previewPrefix(): string
    {
        return '/media-panel/';
    }
}
