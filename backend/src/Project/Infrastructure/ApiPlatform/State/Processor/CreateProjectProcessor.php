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
use App\Security\SecurityUser;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @implements ProcessorInterface<ProjectResource, ProjectResource>
 */
class CreateProjectProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ProjectResource
    {
        assert($data instanceof ProjectResource);

        $securityUser = $this->security->getUser();
        if (!$securityUser instanceof SecurityUser) {
            throw new AccessDeniedHttpException();
        }

        $project = new Project($data->name, (string) $securityUser->getUser()->getId());

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
