<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

abstract class AbstractDomainEvent implements DomainEventInterface
{
    private \DateTimeImmutable $occurredAt;

    public function __construct()
    {
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
