<?php

declare(strict_types=1);

namespace App\User\Domain\Event;

use App\Shared\Domain\Event\AbstractDomainEvent;

class UserDeletedEvent extends AbstractDomainEvent
{
    public function __construct(
        public readonly string $userId,
        public readonly string $email,
    ) {
        parent::__construct();
    }
}
