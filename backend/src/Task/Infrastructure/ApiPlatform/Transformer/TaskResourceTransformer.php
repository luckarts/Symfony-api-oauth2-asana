<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\ApiPlatform\Transformer;

use App\Task\Domain\Entity\Task;
use App\Task\Infrastructure\ApiPlatform\Resource\TaskResource;

final class TaskResourceTransformer
{
    public function toResource(Task $task): TaskResource
    {
        $resource = new TaskResource();
        $resource->id = (string) $task->getId();
        $resource->projectId = (string) $task->getProject()->getId();
        $resource->columnId = $task->getColumn()?->getId();
        $resource->title = $task->getTitle();
        $resource->status = $task->getStatus()->value;
        $resource->isCompleted = $task->isCompleted();
        $resource->dueDate = $task->getDueDate()?->format(\DateTimeInterface::ATOM);
        $resource->orderIndex = $task->getOrderIndex();
        $resource->createdAt = $task->getCreatedAt()->format(\DateTimeInterface::ATOM);
        $resource->updatedAt = $task->getUpdatedAt()->format(\DateTimeInterface::ATOM);

        return $resource;
    }
}
