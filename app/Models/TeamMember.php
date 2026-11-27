<?php

namespace App\Models;

use App\Content\TeamGroup;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'role', 'team_group', 'homepage_partner', 'portrait', 'portrait_source', 'display_order'])]
final class TeamMember extends Model
{
    use SoftDeletes;

    protected $attributes = ['portrait_source' => 'none'];

    public static function existingPortraitUrl(?string $filename): ?string
    {
        return $filename && preg_match('/\A[\w.-]+\.(?:avif|jpe?g|png|webp)\z/i', $filename)
            && is_file(public_path('assets/img/'.$filename)) ? '/assets/img/'.$filename : null;
    }

    public function publicPortraitUrl(bool $private = false): ?string
    {
        return match ($this->portrait_source) {
            'uploaded' => $this->portrait_media_id ? MediaImage::url($this->portrait_media_id, private: $private) : null,
            'existing' => self::existingPortraitUrl($this->portrait),
            default => null,
        };
    }

    protected function casts(): array
    {
        return [
            'team_group' => TeamGroup::class,
            'homepage_partner' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderBy('id');
    }

    public function scopeHomepagePartners(Builder $query): Builder
    {
        return $query->where('homepage_partner', true)->ordered();
    }
}
