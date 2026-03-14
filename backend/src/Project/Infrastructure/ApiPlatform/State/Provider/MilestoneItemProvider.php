<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Project\Domain\Contract\MilestoneRepositoryInterface;
use App\Project\Domain\Contract\ProjectRepositoryInterface;
use App\Project\Infrastructure\ApiPlatform\Resource\MilestoneResource;
use App\Project\Infrastructure\ApiPlatform\Transformer\MilestoneResourceTransformer;
use App\Project\Infrastructure\Security\PersonalProjectVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<MilestoneResource>
 */
class MilestoneItemProvider implements ProviderInterface
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly MilestoneRepositoryInterface $milestoneRepository,
        private readonly MilestoneResourceTransformer $transformer,
        private readonly Security $security,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): MilestoneResource
    {
        $projectId = (string) ($uriVariables['projectId'] ?? '');
        $milestoneId = (string) ($uriVariables['id'] ?? '');

        $project = $this->projectRepository->findById($projectId);

        if (null === $project) {
            throw new NotFoundHttpException('Project not found.');
        }

        if (!$this->security->isGranted(PersonalProjectVoter::PROJECT_VIEW, $project)) {
            throw new AccessDeniedHttpException();
        }

        $milestone = $this->milestoneRepository->findById($milestoneId);

        if (null === $milestone || (string) $milestone->getProject()->getId() !== $projectId) {
            throw new NotFoundHttpException('Milestone not found.');
        }

        return $this->transformer->toResource($milestone);
    }
}
