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

    /**
     * @param list<string> $columnIds
     *
     * @return list<BoardColumn>
     */
    public function reorder(string $projectId, array $columnIds): array
    {
        $columns = $this->boardColumnRepository->findByProject($projectId);

        if (count($columnIds) !== count($columns)) {
            throw new UnprocessableEntityHttpException('The order list must contain all columns of the project.');
        }

        /** @var array<string, BoardColumn> $columnMap */
        $columnMap = [];
        foreach ($columns as $column) {
            $columnMap[(string) $column->getId()] = $column;
        }

        /** @var list<BoardColumn> $ordered */
        $ordered = [];
        foreach ($columnIds as $position => $id) {
            $column = $columnMap[$id] ?? null;
            if (null === $column) {
                throw new UnprocessableEntityHttpException(sprintf('Column "%s" does not belong to this project.', $id));
            }
            unset($columnMap[$id]);
            $column->setPosition($position);
            $ordered[] = $column;
        }

        $this->boardColumnRepository->saveAll($ordered);

        return $ordered;
    }

    public function guardNotLastColumn(string $projectId): void
    {
        $columns = $this->boardColumnRepository->findByProject($projectId);

        if (count($columns) <= 1) {
            throw new UnprocessableEntityHttpException('Cannot delete the last column of a project.');
        }
    }
}
