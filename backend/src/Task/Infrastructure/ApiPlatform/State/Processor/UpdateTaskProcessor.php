<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Project\Domain\Contract\BoardColumnRepositoryInterface;
use App\Project\Domain\Contract\MilestoneRepositoryInterface;
use App\Task\Domain\Contract\TaskRepositoryInterface;
use App\Task\Domain\Enum\TaskStatus;
use App\Task\Infrastructure\ApiPlatform\Resource\TaskResource;
use App\Task\Infrastructure\ApiPlatform\Transformer\TaskResourceTransformer;
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
        private readonly MilestoneRepositoryInterface $milestoneRepository,
        private readonly Security $security,
        private readonly TaskResourceTransformer $transformer,
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

        if (null !== $data->milestoneId) {
            $milestone = $this->milestoneRepository->findById($data->milestoneId);
            if (null === $milestone || (string) $milestone->getProject()->getId() !== $projectId) {
                throw new UnprocessableEntityHttpException('milestoneId does not belong to this project');
            }
            $task->setMilestone($milestone);
        } else {
            $task->setMilestone(null);
        }

        if (null !== $data->parentTaskId) {
            $parent = $this->taskRepository->findById($data->parentTaskId);
            if (null === $parent || (string) $parent->getProject()->getId() !== $projectId) {
                throw new NotFoundHttpException(sprintf('Parent task "%s" not found.', $data->parentTaskId));
            }
            if (null !== $parent->getParent()) {
                throw new UnprocessableEntityHttpException('parentTaskId must be a root task (nesting limited to 1 level)');
            }
            $task->setParent($parent);
        } else {
            $task->setParent(null);
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

        return $this->transformer->toResource($task);
    }
}
