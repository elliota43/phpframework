<?php

declare(strict_types=1);

namespace Examples\Blog\Models;

use Framework\Database\Model;

class Post extends Model
{
    protected static string $table = 'posts';

    public function user(): ?User
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
