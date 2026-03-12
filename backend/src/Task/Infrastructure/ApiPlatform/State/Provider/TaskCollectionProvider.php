<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Project\Domain\Contract\ProjectRepositoryInterface;
use App\Project\Infrastructure\Security\PersonalProjectVoter;
use App\Task\Domain\Contract\TaskRepositoryInterface;
use App\Task\Infrastructure\ApiPlatform\Resource\TaskResource;
use App\Task\Infrastructure\ApiPlatform\Transformer\TaskResourceTransformer;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<TaskResource>
 */
class TaskCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly Security $security,
        private readonly TaskResourceTransformer $transformer,
    ) {
    }

    /** @return list<TaskResource> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $projectId = (string) ($uriVariables['projectId'] ?? '');

        $project = $this->projectRepository->findById($projectId);
        if (null === $project) {
            throw new NotFoundHttpException(sprintf('Project "%s" not found.', $projectId));
        }

        if (!$this->security->isGranted(PersonalProjectVoter::PROJECT_VIEW, $project)) {
            throw new AccessDeniedHttpException();
        }

        $tasks = $this->taskRepository->findByProject($projectId);

        $resources = [];
        foreach ($tasks as $task) {
            $resources[] = $this->transformer->toResource($task);
        }

        return $resources;
    }
}
