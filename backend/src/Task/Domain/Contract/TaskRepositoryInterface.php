<?php

declare(strict_types=1);

namespace App\Task\Domain\Contract;

use App\Task\Domain\Entity\Task;

interface TaskRepositoryInterface
{
    public function findById(string $id): ?Task;

    /** @return list<Task> */
    public function findAll(): array;

    public function save(Task $task): void;

    public function remove(Task $task): void;
}
