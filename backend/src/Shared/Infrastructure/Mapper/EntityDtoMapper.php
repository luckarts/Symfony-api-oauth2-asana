<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Mapper;

final class EntityDtoMapper
{
    public function __construct(
        private readonly MappingConfigLoader $configLoader,
    ) {
    }

    /**
     * Map an entity to a DTO (for Providers / output).
     * Only maps fields with "read" in their operations.
     *
     * @template T of object
     *
     * @param class-string<T> $dtoClass
     *
     * @return T
     */
    public function toDto(object $entity, string $dtoClass): object
    {
        $config = $this->configLoader->getConfigForClass($dtoClass);

        if ($config === null) {
            throw new \InvalidArgumentException(\sprintf('No mapping config found for class "%s".', $dtoClass));
        }

        $dto = new $dtoClass();

        foreach ($config['fields'] as $field => $fieldConfig) {
            if (!\in_array('read', $fieldConfig['operations'], true)) {
                continue;
            }

            $value = $this->getEntityValue($entity, $field);

            if (isset($fieldConfig['transform'])) {
                $value = $this->applyTransform($value, $fieldConfig['transform']);
            }

            $dto->{$field} = $value;
        }

        return $dto;
    }

    /**
     * Map writable DTO fields onto an entity (for Processors / input).
     * Only maps fields with "write" in their operations.
     */
    public function toEntity(object $dto, object $entity): void
    {
        $config = $this->configLoader->getConfigForClass($dto::class);

        if ($config === null) {
            throw new \InvalidArgumentException(\sprintf('No mapping config found for class "%s".', $dto::class));
        }

        foreach ($config['fields'] as $field => $fieldConfig) {
            if (!\in_array('write', $fieldConfig['operations'], true)) {
                continue;
            }

            $setter = 'set' . ucfirst($field);
            if (method_exists($entity, $setter)) {
                $entity->{$setter}($dto->{$field});
            }
        }
    }

    private function getEntityValue(object $entity, string $field): mixed
    {
        $getter = 'get' . ucfirst($field);
        if (method_exists($entity, $getter)) {
            return $entity->{$getter}();
        }

        $isser = 'is' . ucfirst($field);
        if (method_exists($entity, $isser)) {
            return $entity->{$isser}();
        }

        return null;
    }

    private function applyTransform(mixed $value, string $transform): mixed
    {
        if (str_starts_with($transform, 'datetime:')) {
            $format = substr($transform, 9);

            if (!$value instanceof \DateTimeInterface) {
                return $value;
            }

            $constant = \DateTimeInterface::class . '::' . $format;

            return $value->format(\defined($constant) ? \constant($constant) : $format);
        }

        return $value;
    }
}
