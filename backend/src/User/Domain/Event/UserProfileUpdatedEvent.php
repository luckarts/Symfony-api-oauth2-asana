<?php

declare(strict_types=1);

namespace App\User\Domain\Event;

use App\Shared\Domain\Event\AbstractDomainEvent;

class UserProfileUpdatedEvent extends AbstractDomainEvent
{
    public function __construct(
        public readonly string $userId,
    ) {
        parent::__construct();
    }
}
