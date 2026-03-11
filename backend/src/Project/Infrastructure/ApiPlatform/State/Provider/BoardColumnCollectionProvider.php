<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Project\Domain\Contract\BoardColumnRepositoryInterface;
use App\Project\Domain\Contract\ProjectRepositoryInterface;
use App\Project\Domain\Entity\BoardColumn;
use App\Project\Infrastructure\ApiPlatform\Resource\BoardColumnResource;
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

        return array_map(static fn (BoardColumn $col) => self::toResource($col), $columns);
    }

    public static function toResource(BoardColumn $column): BoardColumnResource
    {
        $resource = new BoardColumnResource();
        $resource->id = (string) $column->getId();
        $resource->title = $column->getTitle();
        $resource->position = $column->getPosition();
        $resource->wipLimit = $column->getWipLimit();
        $resource->isDefault = $column->isDefault();
        $resource->projectId = (string) $column->getProject()->getId();
        $resource->createdAt = $column->getCreatedAt()->format(\DateTimeInterface::ATOM);
        $resource->updatedAt = $column->getUpdatedAt()->format(\DateTimeInterface::ATOM);

        return $resource;
    }
}
