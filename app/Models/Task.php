<?php

declare(strict_types=1);

namespace App\Models;

use Framework\Database\Model;

class Task extends Model
{
    protected static string $table = 'tasks';

    public function board(): ?Board
    {
        return $this->belongsTo(Board::class, 'board_id');
    }

    public function column(): ?Column
    {
        return $this->belongsTo(Column::class, 'column_id');
    }

    // optional: nice accessor for display
    public function getDisplayTitleAttribute(): string
    {
        $title = $this->getAttribute('title') ?? '';
        $priority = $this->getAttribute('priority') ?? null;

        return $priority ? '[' . strtoupper($priority) . '] ' . $title : $title;
    }

    public function setPriorityAttribute(?string $value): void
    {
        if ($value === null) {
            $this->attributes['priority'] = null;
            return;
        }

        $valid = ['low', 'medium', 'high', 'urgent'];
        $value = strtolower(trim($value));

        $this->attributes['priority'] = in_array($value, $valid, true)
            ? $value
            : 'medium';
    }
}
