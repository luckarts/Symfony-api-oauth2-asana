<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Task\Domain\Contract\TaskRepositoryInterface;
use App\Task\Infrastructure\ApiPlatform\Resource\TaskResource;
use App\Task\Infrastructure\Security\TaskVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<TaskResource, void>
 */
class DeleteTaskProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $taskId = (string) ($uriVariables['id'] ?? '');
        $projectId = (string) ($uriVariables['projectId'] ?? '');

        $task = $this->taskRepository->findById($taskId);

        if (null === $task || (string) $task->getProject()->getId() !== $projectId) {
            throw new NotFoundHttpException(sprintf('Task "%s" not found.', $taskId));
        }

        if (!$this->security->isGranted(TaskVoter::TASK_DELETE, $task)) {
            throw new AccessDeniedHttpException();
        }

        $this->taskRepository->remove($task);
    }
}
