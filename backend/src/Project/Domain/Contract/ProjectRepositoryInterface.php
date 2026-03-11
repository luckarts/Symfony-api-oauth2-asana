<?php

declare(strict_types=1);

namespace App\Project\Domain\Contract;

use App\Project\Domain\Entity\Project;

interface ProjectRepositoryInterface
{
    public function findById(string $id): ?Project;

    /** @return list<Project> */
    public function findAll(): array;

    public function save(Project $project): void;

    public function remove(Project $project): void;
}
