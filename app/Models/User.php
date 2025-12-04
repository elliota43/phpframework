<?php

namespace App\Models;

use Framework\Database\Model;
use Framework\Support\Collection;

class User extends Model
{
    protected static string $table = 'users';

    public function posts(): Collection
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->attributes['first_name'] ?? '') . ' ' . ($this->attributes['last_name'] ?? ''));
    }

    public function setEmailAttribute(string $value): void
    {
        $this->attributes['email'] = strtolower($value);
    }
}