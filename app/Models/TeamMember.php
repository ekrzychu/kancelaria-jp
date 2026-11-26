<?php

namespace App\Models;

use App\Content\TeamGroup;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'role', 'team_group', 'homepage_partner', 'portrait', 'display_order'])]
final class TeamMember extends Model
{
    use SoftDeletes;

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
