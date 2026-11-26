<?php

namespace App\Models;

use App\Content\ArticleStatus;
use App\Support\PublicUrl;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

#[Fillable(['slug', 'title', 'summary', 'category', 'author', 'body_html', 'date', 'status', 'image', 'image_alt'])]
final class Article extends Model
{
    use SoftDeletes;

    protected $attributes = ['status' => 'draft'];

    protected function casts(): array
    {
        return [
            'date' => 'immutable_date:Y-m-d',
            'status' => ArticleStatus::class,
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ArticleStatus::Published->value)
            ->whereNotIn('slug', PublicUrl::RESERVED_SLUGS);
    }

    protected static function booted(): void
    {
        static::saving(function (Article $article): void {
            if (! PublicUrl::isArticleSlug($article->slug)) {
                throw ValidationException::withMessages(['slug' => 'Use a valid, non-reserved article slug.']);
            }

            if ($article->exists && $article->getOriginal('status') !== ArticleStatus::Draft) {
                if ($article->isDirty('slug')) {
                    throw ValidationException::withMessages(['slug' => 'Keep the established publication URL.']);
                }

                if ($article->status === ArticleStatus::Draft) {
                    throw ValidationException::withMessages(['status' => 'Withdraw a published article using the unpublished state.']);
                }
            }
        });
    }
}
