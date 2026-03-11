<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Task\Domain\Contract\TaskRepositoryInterface;
use App\Task\Domain\Entity\Task;
use App\Task\Infrastructure\ApiPlatform\Resource\TaskResource;
use App\Task\Infrastructure\ApiPlatform\State\Provider\TaskCollectionProvider;

/**
 * @implements ProcessorInterface<TaskResource, TaskResource>
 */
class CreateTaskProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TaskResource
    {
        assert($data instanceof TaskResource);

        $task = new Task($data->title);
        $this->taskRepository->save($task);

        return TaskCollectionProvider::toResource($task);
    }
}
