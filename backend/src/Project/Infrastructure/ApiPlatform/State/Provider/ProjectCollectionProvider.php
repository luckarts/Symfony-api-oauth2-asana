<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Project\Domain\Contract\ProjectRepositoryInterface;
use App\Project\Infrastructure\ApiPlatform\Resource\ProjectResource;
use App\Project\Infrastructure\ApiPlatform\Transformer\ProjectResourceTransformer;

/**
 * @implements ProviderInterface<ProjectResource>
 */
class ProjectCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly ProjectResourceTransformer $transformer,
    ) {
    }

    /** @return list<ProjectResource> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $projects = $this->projectRepository->findAll();

        $resources = [];
        foreach ($projects as $project) {
            $resources[] = $this->transformer->toResource($project);
        }

        return $resources;
    }
}
