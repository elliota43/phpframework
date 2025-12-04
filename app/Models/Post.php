<?php

namespace App\Models;

use Framework\Database\Model;

class Post extends Model
{
    protected static string $table = 'posts';

    public function user(): ?User
    {
        /** @var User|null */
        return $this->belongsTo(User::class, 'user_id');
    }
}