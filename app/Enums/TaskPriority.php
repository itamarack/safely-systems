<?php

namespace App\Enums;

enum TaskPriority: string
{
    case Low    = 'low';
    case Medium = 'medium';
    case High   = 'high';

    public function label(): string
    {
        return match($this) {
            self::Low    => 'Low',
            self::Medium => 'Medium',
            self::High   => 'High',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Low    => 'badge-priority-low',
            self::Medium => 'badge-priority-medium',
            self::High   => 'badge-priority-high',
        };
    }
}
