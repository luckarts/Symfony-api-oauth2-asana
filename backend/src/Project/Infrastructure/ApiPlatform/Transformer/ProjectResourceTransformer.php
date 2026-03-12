<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\Transformer;

use App\Project\Domain\Entity\Project;
use App\Project\Infrastructure\ApiPlatform\Resource\ProjectResource;

final class ProjectResourceTransformer
{
    public function toResource(Project $project): ProjectResource
    {
        $resource = new ProjectResource();
        $resource->id = (string) $project->getId();
        $resource->name = $project->getName();
        $resource->status = $project->getStatus()->value;
        $resource->description = $project->getDescription();
        $resource->createdAt = $project->getCreatedAt()->format(\DateTimeInterface::ATOM);
        $resource->updatedAt = $project->getUpdatedAt()->format(\DateTimeInterface::ATOM);

        return $resource;
    }
}
