<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Project\Domain\Contract\BoardColumnRepositoryInterface;
use App\Project\Domain\Contract\ProjectRepositoryInterface;
use App\Project\Infrastructure\Security\PersonalProjectVoter;
use App\Task\Domain\Contract\TaskRepositoryInterface;
use App\Task\Domain\Entity\Task;
use App\Task\Infrastructure\ApiPlatform\Resource\TaskResource;
use App\Task\Infrastructure\ApiPlatform\State\Provider\TaskCollectionProvider;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @implements ProcessorInterface<TaskResource, TaskResource>
 */
class CreateTaskProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly BoardColumnRepositoryInterface $columnRepository,
        private readonly Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): TaskResource
    {
        assert($data instanceof TaskResource);

        $projectId = (string) ($uriVariables['projectId'] ?? '');
        $project = $this->projectRepository->findById($projectId);

        if (null === $project) {
            throw new NotFoundHttpException(sprintf('Project "%s" not found.', $projectId));
        }

        if (!$this->security->isGranted(PersonalProjectVoter::PROJECT_EDIT, $project)) {
            throw new AccessDeniedHttpException();
        }

        $task = new Task($data->title, $project);

        if (null !== $data->columnId) {
            $column = $this->columnRepository->findById($data->columnId);
            if (null === $column || (string) $column->getProject()->getId() !== $projectId) {
                throw new UnprocessableEntityHttpException('columnId does not belong to this project');
            }
            $task->setColumn($column);
        }

        $task->setOrderIndex($data->orderIndex);

        $this->taskRepository->save($task);

        return TaskCollectionProvider::toResource($task);
    }
}
