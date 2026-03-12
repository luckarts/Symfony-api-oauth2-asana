<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Mapper;

final class EntityDtoMapper
{
    public function __construct(
        private readonly MappingConfigLoaderInterface $configLoader,
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

        if (null === $config) {
            throw new \InvalidArgumentException(\sprintf('No mapping config found for class "%s".', $dtoClass));
        }

        $dto = new $dtoClass();

        foreach ($config['fields'] as $field => $fieldConfig) {
            if (!\in_array('read', $fieldConfig['operations'], true)) {
                continue;
            }

            $found = false;
            $value = $this->getEntityValue($entity, $field, $found);

            if (!$found) {
                continue;
            }

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

        if (null === $config) {
            throw new \InvalidArgumentException(\sprintf('No mapping config found for class "%s".', $dto::class));
        }

        foreach ($config['fields'] as $field => $fieldConfig) {
            if (!\in_array('write', $fieldConfig['operations'], true)) {
                continue;
            }

            $value = $dto->{$field};

            if (isset($fieldConfig['transform'])) {
                $value = $this->applyReverseTransform($value, $fieldConfig['transform']);
            }

            $setter = 'set'.ucfirst($field);
            if (method_exists($entity, $setter)) {
                $entity->{$setter}($value);
            }
        }
    }

    private function getEntityValue(object $entity, string $field, bool &$found = false): mixed
    {
        $found = true;

        if (method_exists($entity, $field)) {
            return $entity->{$field}();
        }

        $getter = 'get'.ucfirst($field);
        if (method_exists($entity, $getter)) {
            return $entity->{$getter}();
        }

        $isser = 'is'.ucfirst($field);
        if (method_exists($entity, $isser)) {
            return $entity->{$isser}();
        }

        $found = false;

        return null;
    }

    private function applyTransform(mixed $value, mixed $transform): mixed
    {
        if (\is_string($transform) && str_starts_with($transform, 'datetime:')) {
            $format = substr($transform, 9);

            if (!$value instanceof \DateTimeInterface) {
                return $value;
            }

            $constant = \DateTimeInterface::class.'::'.$format;

            return $value->format(\defined($constant) ? \constant($constant) : $format);
        }

        if (\is_array($transform)) {
            $type = $transform['type'] ?? null;
            if ('datetime' === $type) {
                $format = $transform['format'] ?? \DateTimeInterface::ATOM;
                if (!$value instanceof \DateTimeInterface) {
                    return $value;
                }

                return $value->format($format);
            }
            if ('enum' === $type && $value instanceof \BackedEnum) {
                return $value->value;
            }
        }

        return $value;
    }

    private function applyReverseTransform(mixed $value, mixed $transform): mixed
    {
        if (null === $value) {
            return null;
        }

        if (\is_array($transform) && isset($transform['type']) && 'enum' === $transform['type']) {
            $class = $transform['class'] ?? null;
            if ($class && \is_string($class) && \enum_exists($class) && \method_exists($class, 'from')) {
                return $class::from($value);
            }
        }

        return $value;
    }
}
