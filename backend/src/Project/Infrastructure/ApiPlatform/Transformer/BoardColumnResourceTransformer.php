<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\Transformer;

use App\Project\Domain\Entity\BoardColumn;
use App\Project\Infrastructure\ApiPlatform\Resource\BoardColumnResource;

final class BoardColumnResourceTransformer
{
    public function toResource(BoardColumn $boardColumn): BoardColumnResource
    {
        $resource = new BoardColumnResource();
        $resource->id = (string) $boardColumn->getId();
        $resource->title = $boardColumn->getTitle();
        $resource->position = $boardColumn->getPosition();
        $resource->wipLimit = $boardColumn->getWipLimit();
        $resource->isDefault = $boardColumn->isDefault();
        $resource->projectId = (string) $boardColumn->getProject()->getId();
        $resource->createdAt = $boardColumn->getCreatedAt()->format(\DateTimeInterface::ATOM);
        $resource->updatedAt = $boardColumn->getUpdatedAt()->format(\DateTimeInterface::ATOM);

        return $resource;
    }
}
