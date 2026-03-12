<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Mapper;

interface MappingConfigLoaderInterface
{
    /**
     * @return array{entity: string, fields: array<string, array<string, mixed>>}|null
     */
    public function getConfigForClass(string $className): ?array;
}
