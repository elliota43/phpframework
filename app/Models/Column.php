<?php

declare(strict_types=1);

namespace App\Models;

use Framework\Database\Model;
use Framework\Support\Collection;

class Column extends Model
{
    protected static string $table = 'columns';

    public function board(): ?Board
    {
        // belongsTo() on base Model
        return $this->belongsTo(Board::class, 'board_id');
    }

    public function tasks(): Collection
    {
        // this is what your controller calls: $column->tasks()->all()
        $tasks = $this->hasMany(Task::class, 'column_id');

        $items = $tasks->all();
        usort($items, function ($a, $b) {
            $posA = (int) ($a->getAttribute('position') ?? 0);
            $posB = (int) ($b->getAttribute('position') ?? 0);
            return $posA <=> $posB;
        });

        return new Collection($items);
    }
}
