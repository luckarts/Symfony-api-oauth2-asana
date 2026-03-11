<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\Doctrine;

use App\Project\Domain\Contract\ProjectRepositoryInterface;
use App\Project\Domain\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class DoctrineProjectRepository extends ServiceEntityRepository implements ProjectRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function findById(string $id): ?Project
    {
        return $this->find($id);
    }

    /** @return list<Project> */
    public function findAll(): array
    {
        /** @var list<Project> $result */
        $result = $this->findBy([], ['createdAt' => 'ASC']);

        return $result;
    }

    public function save(Project $project): void
    {
        $this->getEntityManager()->persist($project);
        $this->getEntityManager()->flush();
    }

    public function remove(Project $project): void
    {
        $this->getEntityManager()->remove($project);
        $this->getEntityManager()->flush();
    }
}
