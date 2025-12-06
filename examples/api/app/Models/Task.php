<?php

declare(strict_types=1);

namespace Examples\Api\Models;

use Framework\Database\Model;

class Task extends Model
{
    protected static string $table = 'tasks';

    public function category(): ?Category
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Accessor for formatted status
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        $status = $this->getAttribute('status') ?? 'pending';
        return $labels[$status] ?? ucfirst($status);
    }

    // Mutator to ensure status is valid
    public function setStatusAttribute(string $value): void
    {
        $validStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        if (in_array($value, $validStatuses, true)) {
            $this->attributes['status'] = $value;
        } else {
            $this->attributes['status'] = 'pending';
        }
    }

    // Mutator to ensure priority is valid
    public function setPriorityAttribute(string $value): void
    {
        $validPriorities = ['low', 'medium', 'high', 'urgent'];
        if (in_array($value, $validPriorities, true)) {
            $this->attributes['priority'] = $value;
        } else {
            $this->attributes['priority'] = 'medium';
        }
    }
}

