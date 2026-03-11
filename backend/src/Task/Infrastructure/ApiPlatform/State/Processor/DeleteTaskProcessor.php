<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Task\Domain\Contract\TaskRepositoryInterface;
use App\Task\Infrastructure\ApiPlatform\Resource\TaskResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<TaskResource, void>
 */
class DeleteTaskProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $id = (string) ($uriVariables['id'] ?? '');
        $task = $this->taskRepository->findById($id);

        if ($task === null) {
            throw new NotFoundHttpException(sprintf('Task "%s" not found.', $id));
        }

        $this->taskRepository->remove($task);
    }
}
