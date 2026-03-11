<?php

declare(strict_types=1);

namespace App\Project\Domain\Enum;

enum ProjectStatus: string
{
    case ACTIVE = 'active';
    case ON_HOLD = 'on_hold';
    case COMPLETED = 'completed';
}
