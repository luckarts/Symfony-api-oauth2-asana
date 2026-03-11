<?php

declare(strict_types=1);

namespace App\Task\Domain\Enum;

enum TaskStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';
}
