<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Project\Domain\Contract\ProjectRepositoryInterface;
use App\Project\Infrastructure\ApiPlatform\Resource\ProjectResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<ProjectResource, void>
 */
class DeleteProjectProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $id = (string) ($uriVariables['id'] ?? '');
        $project = $this->projectRepository->findById($id);

        if (null === $project) {
            throw new NotFoundHttpException('Project not found.');
        }

        $this->projectRepository->remove($project);
    }
}
