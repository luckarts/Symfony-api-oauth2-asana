<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Task\Domain\Contract\TaskRepositoryInterface;
use App\Task\Domain\Entity\Task;
use App\Task\Infrastructure\ApiPlatform\Resource\TaskResource;

/**
 * @implements ProviderInterface<TaskResource>
 */
class TaskCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
    ) {
    }

    /** @return list<TaskResource> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $tasks = $this->taskRepository->findAll();

        return array_map(static fn (Task $t) => self::toResource($t), $tasks);
    }

    public static function toResource(Task $task): TaskResource
    {
        $resource = new TaskResource();
        $resource->id = (string) $task->getId();
        $resource->title = $task->getTitle();
        $resource->status = $task->getStatus()->value;
        $resource->createdAt = $task->getCreatedAt()->format(\DateTimeInterface::ATOM);

        return $resource;
    }
}
