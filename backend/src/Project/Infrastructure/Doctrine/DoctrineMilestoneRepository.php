<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\Doctrine;

use App\Project\Domain\Contract\MilestoneRepositoryInterface;
use App\Project\Domain\Entity\Milestone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Milestone>
 */
class DoctrineMilestoneRepository extends ServiceEntityRepository implements MilestoneRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Milestone::class);
    }

    public function findById(string $id): ?Milestone
    {
        return $this->find($id);
    }

    /** @return list<Milestone> */
    public function findByProject(string $projectId): array
    {
        /** @var list<Milestone> $result */
        $result = $this->createQueryBuilder('m')
            ->where('m.project = :projectId')
            ->setParameter('projectId', $projectId)
            ->orderBy('m.dueDate', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function save(Milestone $milestone): void
    {
        $this->getEntityManager()->persist($milestone);
        $this->getEntityManager()->flush();
    }

    public function remove(Milestone $milestone): void
    {
        $this->getEntityManager()->remove($milestone);
        $this->getEntityManager()->flush();
    }
}
