<?php

namespace App\Models;

use App\Content\JobStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

#[Fillable(['title', 'description', 'url', 'status', 'display_order'])]
final class JobOffer extends Model
{
    use SoftDeletes;

    protected $attributes = ['status' => 'draft'];

    protected function casts(): array
    {
        return [
            'status' => JobStatus::class,
            'display_order' => 'integer',
        ];
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', JobStatus::Open->value)->orderBy('display_order')->orderBy('id');
    }

    protected static function booted(): void
    {
        static::saving(function (JobOffer $job): void {
            if ($job->status === JobStatus::Open && blank($job->title)) {
                throw ValidationException::withMessages(['title' => 'An open job offer requires a title.']);
            }
        });
    }
}
