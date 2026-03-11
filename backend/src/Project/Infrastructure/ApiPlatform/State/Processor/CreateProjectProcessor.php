<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Project\Domain\Contract\ProjectRepositoryInterface;
use App\Project\Domain\Entity\Project;
use App\Project\Domain\Enum\ProjectStatus;
use App\Project\Infrastructure\ApiPlatform\Resource\ProjectResource;

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

        $resource = new ProjectResource();
        $resource->id = (string) $project->getId();
        $resource->name = $project->getName();
        $resource->status = $project->getStatus()->value;
        $resource->description = $project->getDescription();
        $resource->createdAt = $project->getCreatedAt()->format(\DateTimeInterface::ATOM);
        $resource->updatedAt = $project->getUpdatedAt()->format(\DateTimeInterface::ATOM);

        return $resource;
    }
}
