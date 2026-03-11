<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Project\Domain\Contract\ProjectRepositoryInterface;
use App\Project\Domain\Enum\ProjectStatus;
use App\Project\Infrastructure\ApiPlatform\Resource\ProjectResource;
use App\Project\Infrastructure\ApiPlatform\State\Provider\ProjectCollectionProvider;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<ProjectResource, ProjectResource>
 */
class UpdateProjectProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ProjectResource
    {
        assert($data instanceof ProjectResource);

        $id = (string) ($uriVariables['id'] ?? '');
        $project = $this->projectRepository->findById($id);

        if (null === $project) {
            throw new NotFoundHttpException('Project not found.');
        }

        if ('' !== $data->name) {
            $project->setName($data->name);
        }

        if ('' !== $data->status) {
            $status = ProjectStatus::tryFrom($data->status);
            if (null !== $status) {
                $project->setStatus($status);
            }
        }

        $project->setDescription($data->description);

        $this->projectRepository->save($project);

        return ProjectCollectionProvider::toResource($project);
    }
}
