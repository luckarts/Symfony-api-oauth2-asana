<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Project\Application\Service\BoardColumnService;
use App\Project\Domain\Contract\BoardColumnRepositoryInterface;
use App\Project\Domain\Contract\ProjectRepositoryInterface;
use App\Project\Infrastructure\ApiPlatform\Resource\BoardColumnResource;
use App\Project\Infrastructure\Security\PersonalProjectVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<BoardColumnResource, void>
 */
class DeleteBoardColumnProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly BoardColumnRepositoryInterface $boardColumnRepository,
        private readonly BoardColumnService $boardColumnService,
        private readonly Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $projectId = (string) ($uriVariables['id'] ?? '');
        $colId = (string) ($uriVariables['colId'] ?? '');

        $project = $this->projectRepository->findById($projectId);

        if (null === $project) {
            throw new NotFoundHttpException('Project not found.');
        }

        if (!$this->security->isGranted(PersonalProjectVoter::PROJECT_EDIT, $project)) {
            throw new AccessDeniedHttpException();
        }

        $column = $this->boardColumnRepository->findById($colId);

        if (null === $column || (string) $column->getProject()->getId() !== $projectId) {
            throw new NotFoundHttpException('Board column not found.');
        }

        $this->boardColumnService->guardNotLastColumn($projectId);

        $this->boardColumnRepository->remove($column);
    }
}
