<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Task\Domain\Contract\TaskRepositoryInterface;
use App\Task\Domain\Enum\TaskStatus;
use App\Task\Infrastructure\ApiPlatform\Resource\TaskResource;
use App\Task\Infrastructure\ApiPlatform\State\Provider\TaskCollectionProvider;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<TaskResource, TaskResource>
 */
class UpdateTaskProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TaskResource
    {
        assert($data instanceof TaskResource);

        $id = (string) ($uriVariables['id'] ?? '');
        $task = $this->taskRepository->findById($id);

        if ($task === null) {
            throw new NotFoundHttpException(sprintf('Task "%s" not found.', $id));
        }

        if ($data->title !== '') {
            $task->setTitle($data->title);
        }

        $status = TaskStatus::tryFrom($data->status);
        if ($status !== null) {
            $task->setStatus($status);
        }

        $this->taskRepository->save($task);

        return TaskCollectionProvider::toResource($task);
    }
}
