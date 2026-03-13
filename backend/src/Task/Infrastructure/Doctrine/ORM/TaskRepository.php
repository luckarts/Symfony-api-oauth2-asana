<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Doctrine\ORM;

use App\Task\Domain\Contract\TaskRepositoryInterface;
use App\Task\Domain\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository implements TaskRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findById(string $id): ?Task
    {
        return $this->find($id);
    }

    /** @return list<Task> */
    public function findByProject(string $projectId): array
    {
        /** @var list<Task> $result */
        $result = $this->findBy(['project' => $projectId], ['orderIndex' => 'ASC', 'createdAt' => 'ASC']);

        return $result;
    }

    /** @return list<Task> */
    public function findSubtasks(string $parentId): array
    {
        /** @var list<Task> $result */
        $result = $this->findBy(['parent' => $parentId], ['orderIndex' => 'ASC']);

        return $result;
    }

    public function save(Task $task): void
    {
        $this->getEntityManager()->persist($task);
        $this->getEntityManager()->flush();
    }

    public function remove(Task $task): void
    {
        $this->getEntityManager()->remove($task);
        $this->getEntityManager()->flush();
    }
}
