<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Project\Domain\Contract\ProjectRepositoryInterface;
use App\Project\Infrastructure\ApiPlatform\Resource\ProjectResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<ProjectResource>
 */
class ProjectItemProvider implements ProviderInterface
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ProjectResource
    {
        $id = (string) ($uriVariables['id'] ?? '');
        $project = $this->projectRepository->findById($id);

        if (null === $project) {
            throw new NotFoundHttpException('Project not found.');
        }

        return ProjectCollectionProvider::toResource($project);
    }
}
