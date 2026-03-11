<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Project\Domain\Contract\ProjectRepositoryInterface;
use App\Project\Domain\Entity\Project;
use App\Project\Infrastructure\ApiPlatform\Resource\ProjectResource;

/**
 * @implements ProviderInterface<ProjectResource>
 */
class ProjectCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {
    }

    /** @return list<ProjectResource> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $projects = $this->projectRepository->findAll();

        return array_map(static fn (Project $p) => self::toResource($p), $projects);
    }

    public static function toResource(Project $project): ProjectResource
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
