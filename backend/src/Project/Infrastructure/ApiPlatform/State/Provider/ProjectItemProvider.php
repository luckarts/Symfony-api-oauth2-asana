<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Project\Domain\Contract\ProjectRepositoryInterface;
use App\Project\Infrastructure\ApiPlatform\Resource\ProjectResource;
use App\Project\Infrastructure\ApiPlatform\Transformer\ProjectResourceTransformer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<ProjectResource>
 */
class ProjectItemProvider implements ProviderInterface
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly ProjectResourceTransformer $transformer,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ProjectResource
    {
        $id = (string) ($uriVariables['id'] ?? '');
        $project = $this->projectRepository->findById($id);

        if (null === $project) {
            throw new NotFoundHttpException('Project not found.');
        }

        return $this->transformer->toResource($project);
    }
}
