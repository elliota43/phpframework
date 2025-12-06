<?php

declare(strict_types=1);

namespace App\Models;

use Framework\Database\Model;
use Framework\Support\Collection;

class Board extends Model
{
    protected static string $table = 'boards';

    public function columns(): Collection
    {
        // uses hasMany() on the base Model
        $columns = $this->hasMany(Column::class, 'board_id');

        $items = $columns->all();
        usort($items, function ($a, $b) {
            $posA = (int) ($a->getAttribute('position') ?? 0);
            $posB = (int) ($b->getAttribute('position') ?? 0);
            return $posA <=> $posB;
        });

        return new Collection($items);
    }
}
