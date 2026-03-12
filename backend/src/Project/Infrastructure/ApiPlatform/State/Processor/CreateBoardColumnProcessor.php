<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Project\Application\Service\BoardColumnService;
use App\Project\Domain\Contract\BoardColumnRepositoryInterface;
use App\Project\Domain\Contract\ProjectRepositoryInterface;
use App\Project\Domain\Entity\BoardColumn;
use App\Project\Infrastructure\ApiPlatform\Resource\BoardColumnResource;
use App\Project\Infrastructure\ApiPlatform\Transformer\BoardColumnResourceTransformer;
use App\Project\Infrastructure\Security\PersonalProjectVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<BoardColumnResource, BoardColumnResource>
 */
class CreateBoardColumnProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly BoardColumnRepositoryInterface $boardColumnRepository,
        private readonly BoardColumnService $boardColumnService,
        private readonly BoardColumnResourceTransformer $boardColumnResourceTransformer,
        private readonly Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): BoardColumnResource
    {
        assert($data instanceof BoardColumnResource);

        $projectId = (string) ($uriVariables['id'] ?? '');
        $project = $this->projectRepository->findById($projectId);

        if (null === $project) {
            throw new NotFoundHttpException('Project not found.');
        }

        if (!$this->security->isGranted(PersonalProjectVoter::PROJECT_EDIT, $project)) {
            throw new AccessDeniedHttpException();
        }

        $position = $this->boardColumnService->nextPosition($projectId);
        $column = new BoardColumn($project, $data->title, $position);

        if (null !== $data->wipLimit) {
            $column->setWipLimit($data->wipLimit);
        }

        if ($data->isDefault) {
            $this->boardColumnService->setDefault($column, $projectId);
        }

        $this->boardColumnRepository->save($column);

        return $this->boardColumnResourceTransformer->toResource($column);
    }
}
