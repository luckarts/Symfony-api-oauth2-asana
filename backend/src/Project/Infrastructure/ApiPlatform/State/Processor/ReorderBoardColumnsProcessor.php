<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Project\Application\Service\BoardColumnService;
use App\Project\Domain\Contract\ProjectRepositoryInterface;
use App\Project\Domain\Entity\BoardColumn;
use App\Project\Infrastructure\ApiPlatform\Resource\BoardColumnResource;
use App\Project\Infrastructure\ApiPlatform\Resource\BoardColumnsReorderResource;
use App\Project\Infrastructure\ApiPlatform\State\Provider\BoardColumnCollectionProvider;
use App\Project\Infrastructure\Security\PersonalProjectVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<BoardColumnsReorderResource, list<BoardColumnResource>>
 */
class ReorderBoardColumnsProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly BoardColumnService $boardColumnService,
        private readonly Security $security,
    ) {
    }

    /**
     * @return list<BoardColumnResource>
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        assert($data instanceof BoardColumnsReorderResource);

        $projectId = (string) ($uriVariables['projectId'] ?? '');
        $project = $this->projectRepository->findById($projectId);

        if (null === $project) {
            throw new NotFoundHttpException('Project not found.');
        }

        if (!$this->security->isGranted(PersonalProjectVoter::PROJECT_EDIT, $project)) {
            throw new AccessDeniedHttpException();
        }

        $columns = $this->boardColumnService->reorder($projectId, $data->order);

        return array_map(
            static fn (BoardColumn $col) => BoardColumnCollectionProvider::toResource($col),
            $columns,
        );
    }
}
