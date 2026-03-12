<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Project\Domain\Contract\ProjectRepositoryInterface;
use App\Project\Infrastructure\Security\PersonalProjectVoter;
use App\Task\Domain\Contract\TaskRepositoryInterface;
use App\Task\Domain\Entity\Task;
use App\Task\Infrastructure\ApiPlatform\Resource\TaskResource;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<TaskResource>
 */
class TaskCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly Security $security,
    ) {
    }

    /** @return list<TaskResource> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $projectId = (string) ($uriVariables['projectId'] ?? '');

        $project = $this->projectRepository->findById($projectId);
        if (null === $project) {
            throw new NotFoundHttpException(sprintf('Project "%s" not found.', $projectId));
        }

        if (!$this->security->isGranted(PersonalProjectVoter::PROJECT_VIEW, $project)) {
            throw new AccessDeniedHttpException();
        }

        $tasks = $this->taskRepository->findByProject($projectId);

        return array_map(static fn (Task $t) => self::toResource($t), $tasks);
    }

    public static function toResource(Task $task): TaskResource
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

        return $resource;
    }
}
