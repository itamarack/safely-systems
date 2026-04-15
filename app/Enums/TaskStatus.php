<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending      = 'pending';
    case Completed    = 'completed';
    case NonCompliant = 'non_compliant';

    public function label(): string
    {
        return match($this) {
            self::Pending      => 'Pending',
            self::Completed    => 'Completed',
            self::NonCompliant => 'Non-Compliant',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Pending      => 'bg-secondary',
            self::Completed    => 'bg-success',
            self::NonCompliant => 'bg-danger',
        };
    }
}
