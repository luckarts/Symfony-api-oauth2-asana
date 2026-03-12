<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Project\Domain\Contract\BoardColumnRepositoryInterface;
use App\Project\Domain\Contract\ProjectRepositoryInterface;
use App\Project\Infrastructure\ApiPlatform\Resource\BoardColumnResource;
use App\Project\Infrastructure\ApiPlatform\Transformer\BoardColumnResourceTransformer;
use App\Project\Infrastructure\Security\PersonalProjectVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<BoardColumnResource>
 */
class BoardColumnCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly BoardColumnRepositoryInterface $boardColumnRepository,
        private readonly BoardColumnResourceTransformer $transformer,
        private readonly Security $security,
    ) {
    }

    /** @return list<BoardColumnResource> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $projectId = (string) ($uriVariables['id'] ?? '');
        $project = $this->projectRepository->findById($projectId);

        if (null === $project) {
            throw new NotFoundHttpException('Project not found.');
        }

        if (!$this->security->isGranted(PersonalProjectVoter::PROJECT_VIEW, $project)) {
            throw new AccessDeniedHttpException();
        }

        $columns = $this->boardColumnRepository->findByProjectOrdered($projectId);

        $resources = [];
        foreach ($columns as $column) {
            $resources[] = $this->transformer->toResource($column);
        }

        return $resources;
    }
}
