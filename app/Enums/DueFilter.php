<?php

namespace App\Enums;

enum DueFilter: string
{
    case Today   = 'today';
    case Overdue = 'overdue';
}
