<?php

declare(strict_types=1);

namespace App\Project\Domain\Contract;

use App\Project\Domain\Entity\Milestone;

interface MilestoneRepositoryInterface
{
    public function findById(string $id): ?Milestone;

    /** @return list<Milestone> */
    public function findByProject(string $projectId): array;

    public function save(Milestone $milestone): void;

    public function remove(Milestone $milestone): void;
}
