<?php

declare(strict_types=1);

namespace Examples\Api\Models;

use Framework\Database\Model;

class Category extends Model
{
    protected static string $table = 'categories';

    public function tasks()
    {
        return $this->hasMany(Task::class, 'category_id');
    }
}

