<?php

namespace App\Models;

use App\Support\PublicUrl;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

#[Fillable(['slug', 'title', 'category', 'summary', 'home_summary', 'body', 'scope', 'active', 'display_order'])]
final class Specialization extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'body' => 'array',
            'scope' => 'array',
            'active' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('active', true)->orderBy('display_order')->orderBy('id');
    }

    protected static function booted(): void
    {
        static::saving(function (Specialization $specialization): void {
            if (! PublicUrl::isSlug($specialization->slug)) {
                throw ValidationException::withMessages(['slug' => 'Use a valid specialization slug.']);
            }
        });
    }
}
