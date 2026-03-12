<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Project\Domain\Contract\BoardColumnRepositoryInterface;
use App\Task\Domain\Contract\TaskRepositoryInterface;
use App\Task\Domain\Enum\TaskStatus;
use App\Task\Infrastructure\ApiPlatform\Resource\TaskResource;
use App\Task\Infrastructure\ApiPlatform\State\Provider\TaskCollectionProvider;
use App\Task\Infrastructure\Security\TaskVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @implements ProcessorInterface<TaskResource, TaskResource>
 */
class UpdateTaskProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly BoardColumnRepositoryInterface $columnRepository,
        private readonly Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TaskResource
    {
        assert($data instanceof TaskResource);

        $taskId = (string) ($uriVariables['id'] ?? '');
        $projectId = (string) ($uriVariables['projectId'] ?? '');

        $task = $this->taskRepository->findById($taskId);

        if (null === $task || (string) $task->getProject()->getId() !== $projectId) {
            throw new NotFoundHttpException(sprintf('Task "%s" not found.', $taskId));
        }

        if (!$this->security->isGranted(TaskVoter::TASK_EDIT, $task)) {
            throw new AccessDeniedHttpException();
        }

        if ('' !== $data->title) {
            $task->setTitle($data->title);
        }

        $status = TaskStatus::tryFrom($data->status);
        if (null !== $status) {
            $task->setStatus($status);
        }

        if (null !== $data->columnId) {
            $column = $this->columnRepository->findById($data->columnId);
            if (null === $column || (string) $column->getProject()->getId() !== $projectId) {
                throw new UnprocessableEntityHttpException('columnId does not belong to this project');
            }
            $task->setColumn($column);
        } else {
            $task->setColumn(null);
        }

        $task->setOrderIndex($data->orderIndex);
        $task->setIsCompleted($data->isCompleted);

        if (null !== $data->dueDate) {
            $parsed = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $data->dueDate);
            if (false === $parsed) {
                throw new UnprocessableEntityHttpException('dueDate must be a valid ISO 8601 date');
            }
            $task->setDueDate($parsed);
        } else {
            $task->setDueDate(null);
        }

        $this->taskRepository->save($task);

        return TaskCollectionProvider::toResource($task);
    }
}
