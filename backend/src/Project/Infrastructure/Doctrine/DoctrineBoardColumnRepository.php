<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\Doctrine;

use App\Project\Domain\Contract\BoardColumnRepositoryInterface;
use App\Project\Domain\Entity\BoardColumn;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BoardColumn>
 */
class DoctrineBoardColumnRepository extends ServiceEntityRepository implements BoardColumnRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoardColumn::class);
    }

    public function findById(string $id): ?BoardColumn
    {
        return $this->find($id);
    }

    /** @return list<BoardColumn> */
    public function findByProject(string $projectId): array
    {
        /** @var list<BoardColumn> $result */
        $result = $this->findBy(['project' => $projectId]);

        return $result;
    }

    /** @return list<BoardColumn> */
    public function findByProjectOrdered(string $projectId): array
    {
        /** @var list<BoardColumn> $result */
        $result = $this->findBy(['project' => $projectId], ['position' => 'ASC']);

        return $result;
    }

    public function save(BoardColumn $column): void
    {
        $this->getEntityManager()->persist($column);
        $this->getEntityManager()->flush();
    }

    public function remove(BoardColumn $column): void
    {
        $this->getEntityManager()->remove($column);
        $this->getEntityManager()->flush();
    }
}
