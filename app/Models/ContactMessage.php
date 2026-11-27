<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class ContactMessage extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'email', 'phone', 'message', 'next_attempt_at'];

    protected $hidden = ['name', 'email', 'phone', 'message'];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
            'next_attempt_at' => 'datetime',
            'last_attempt_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }
}
