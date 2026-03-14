<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\Transformer;

use App\Project\Domain\Entity\Milestone;
use App\Project\Infrastructure\ApiPlatform\Resource\MilestoneResource;

final class MilestoneResourceTransformer
{
    public function toResource(Milestone $milestone): MilestoneResource
    {
        $resource = new MilestoneResource();
        $resource->id = (string) $milestone->getId();
        $resource->projectId = (string) $milestone->getProject()->getId();
        $resource->title = $milestone->getTitle();
        $resource->status = $milestone->getStatus()->value;
        $resource->dueDate = $milestone->getDueDate()?->format(\DateTimeInterface::ATOM);
        $resource->createdAt = $milestone->getCreatedAt()->format(\DateTimeInterface::ATOM);
        $resource->updatedAt = $milestone->getUpdatedAt()->format(\DateTimeInterface::ATOM);

        return $resource;
    }
}
