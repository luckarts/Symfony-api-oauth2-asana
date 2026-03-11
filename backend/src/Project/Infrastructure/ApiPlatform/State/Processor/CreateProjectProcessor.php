<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Project\Domain\Contract\ProjectRepositoryInterface;
use App\Project\Domain\Entity\Project;
use App\Project\Domain\Enum\ProjectStatus;
use App\Project\Infrastructure\ApiPlatform\Resource\ProjectResource;
use App\Project\Infrastructure\ApiPlatform\State\Provider\ProjectCollectionProvider;

/**
 * @implements ProcessorInterface<ProjectResource, ProjectResource>
 */
class CreateProjectProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ProjectResource
    {
        assert($data instanceof ProjectResource);

        $project = new Project($data->name);

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
