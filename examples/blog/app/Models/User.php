<?php

declare(strict_types=1);

namespace Examples\Blog\Models;

use Framework\Database\Model;

class User extends Model
{
    protected static string $table = 'users';

    public function posts(): array
    {
        return $this->hasMany(Post::class, 'user_id');
    }
}
