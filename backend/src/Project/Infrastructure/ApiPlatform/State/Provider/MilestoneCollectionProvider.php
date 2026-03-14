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
class MilestoneCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly MilestoneRepositoryInterface $milestoneRepository,
        private readonly MilestoneResourceTransformer $transformer,
        private readonly Security $security,
    ) {
    }

    /** @return list<MilestoneResource> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $projectId = (string) ($uriVariables['projectId'] ?? '');
        $project = $this->projectRepository->findById($projectId);

        if (null === $project) {
            throw new NotFoundHttpException('Project not found.');
        }

        if (!$this->security->isGranted(PersonalProjectVoter::PROJECT_VIEW, $project)) {
            throw new AccessDeniedHttpException();
        }

        $milestones = $this->milestoneRepository->findByProject($projectId);

        $resources = [];
        foreach ($milestones as $milestone) {
            $resources[] = $this->transformer->toResource($milestone);
        }

        return $resources;
    }
}
