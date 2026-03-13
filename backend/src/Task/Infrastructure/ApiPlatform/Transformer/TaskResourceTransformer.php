<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\ApiPlatform\Transformer;

use App\Shared\Infrastructure\Mapper\EntityDtoMapper;
use App\Task\Domain\Entity\Task;
use App\Task\Infrastructure\ApiPlatform\Resource\TaskResource;

final class TaskResourceTransformer
{
    public function __construct(
        private readonly EntityDtoMapper $mapper,
    ) {
    }

    public function toResource(Task $task): TaskResource
    {
        /** @var TaskResource $resource */
        $resource = $this->mapper->toDto($task, TaskResource::class);
        $resource->id = (string) $task->getId();
        $resource->projectId = (string) $task->getProject()->getId();
        $resource->columnId = $task->getColumn()?->getId();
        $resource->parentTaskId = $task->getParent()?->getId();
        $resource->subtasks = array_map(
            static fn (Task $child): array => [
                'id' => (string) $child->getId(),
                'title' => $child->getTitle(),
                'status' => $child->getStatus()->value,
            ],
            $task->getChildren()->toArray(),
        );

        return $resource;
    }
}
