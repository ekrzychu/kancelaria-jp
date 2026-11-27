<?php

namespace App\Models;

use App\Media\MediaPurpose;
use Illuminate\Database\Eloquent\Model;

final class MediaImage extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return ['purpose' => MediaPurpose::class, 'record_id' => 'integer', 'user_id' => 'integer', 'session_version' => 'integer'];
    }

    public static function url(string $id, bool $private = false): string
    {
        return ($private ? '/media-panel/' : '/media/').$id;
    }

    public function filename(): string
    {
        return $this->id.'.webp';
    }
}
