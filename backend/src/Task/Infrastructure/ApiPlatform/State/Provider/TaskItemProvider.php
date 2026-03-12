<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Task\Domain\Contract\TaskRepositoryInterface;
use App\Task\Infrastructure\ApiPlatform\Resource\TaskResource;
use App\Task\Infrastructure\Security\TaskVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<TaskResource>
 */
class TaskItemProvider implements ProviderInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly Security $security,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TaskResource
    {
        $taskId = (string) ($uriVariables['id'] ?? '');
        $projectId = (string) ($uriVariables['projectId'] ?? '');

        $task = $this->taskRepository->findById($taskId);

        if (null === $task || (string) $task->getProject()->getId() !== $projectId) {
            throw new NotFoundHttpException(sprintf('Task "%s" not found.', $taskId));
        }

        if (!$this->security->isGranted(TaskVoter::TASK_VIEW, $task)) {
            throw new AccessDeniedHttpException();
        }

        return TaskCollectionProvider::toResource($task);
    }
}
