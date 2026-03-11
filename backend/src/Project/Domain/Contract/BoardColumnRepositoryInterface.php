<?php

declare(strict_types=1);

namespace App\Project\Domain\Contract;

use App\Project\Domain\Entity\BoardColumn;

interface BoardColumnRepositoryInterface
{
    public function findById(string $id): ?BoardColumn;

    /** @return list<BoardColumn> */
    public function findByProject(string $projectId): array;

    /** @return list<BoardColumn> */
    public function findByProjectOrdered(string $projectId): array;

    public function save(BoardColumn $column): void;

    public function remove(BoardColumn $column): void;
}
