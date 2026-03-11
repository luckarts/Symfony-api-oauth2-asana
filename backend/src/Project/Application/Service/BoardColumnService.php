<?php

declare(strict_types=1);

namespace App\Project\Application\Service;

use App\Project\Domain\Contract\BoardColumnRepositoryInterface;
use App\Project\Domain\Entity\BoardColumn;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class BoardColumnService
{
    public function __construct(
        private readonly BoardColumnRepositoryInterface $boardColumnRepository,
    ) {
    }

    public function nextPosition(string $projectId): int
    {
        $columns = $this->boardColumnRepository->findByProject($projectId);

        if ([] === $columns) {
            return 0;
        }

        $max = 0;
        foreach ($columns as $column) {
            if ($column->getPosition() > $max) {
                $max = $column->getPosition();
            }
        }

        return $max + 1;
    }

    public function setDefault(BoardColumn $column, string $projectId): void
    {
        $columns = $this->boardColumnRepository->findByProject($projectId);

        foreach ($columns as $existing) {
            if ($existing->isDefault() && $existing->getId() !== $column->getId()) {
                $existing->setIsDefault(false);
                $this->boardColumnRepository->save($existing);
            }
        }

        $column->setIsDefault(true);
    }

    public function guardNotLastColumn(string $projectId): void
    {
        $columns = $this->boardColumnRepository->findByProject($projectId);

        if (count($columns) <= 1) {
            throw new UnprocessableEntityHttpException('Cannot delete the last column of a project.');
        }
    }
}
