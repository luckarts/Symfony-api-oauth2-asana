<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Task\Domain\Contract\TaskRepositoryInterface;
use App\Task\Infrastructure\ApiPlatform\Resource\TaskResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<TaskResource>
 */
class TaskItemProvider implements ProviderInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TaskResource
    {
        $id = (string) ($uriVariables['id'] ?? '');
        $task = $this->taskRepository->findById($id);

        if ($task === null) {
            throw new NotFoundHttpException(sprintf('Task "%s" not found.', $id));
        }

        return TaskCollectionProvider::toResource($task);
    }
}
