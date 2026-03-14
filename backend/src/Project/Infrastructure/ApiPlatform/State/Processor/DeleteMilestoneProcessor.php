<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Project\Domain\Contract\MilestoneRepositoryInterface;
use App\Project\Domain\Contract\ProjectRepositoryInterface;
use App\Project\Infrastructure\ApiPlatform\Resource\MilestoneResource;
use App\Project\Infrastructure\Security\PersonalProjectVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<MilestoneResource, void>
 */
class DeleteMilestoneProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly MilestoneRepositoryInterface $milestoneRepository,
        private readonly Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $projectId = (string) ($uriVariables['projectId'] ?? '');
        $milestoneId = (string) ($uriVariables['id'] ?? '');

        $project = $this->projectRepository->findById($projectId);

        if (null === $project) {
            throw new NotFoundHttpException('Project not found.');
        }

        if (!$this->security->isGranted(PersonalProjectVoter::PROJECT_EDIT, $project)) {
            throw new AccessDeniedHttpException();
        }

        $milestone = $this->milestoneRepository->findById($milestoneId);

        if (null === $milestone || (string) $milestone->getProject()->getId() !== $projectId) {
            throw new NotFoundHttpException('Milestone not found.');
        }

        $this->milestoneRepository->remove($milestone);
    }
}
