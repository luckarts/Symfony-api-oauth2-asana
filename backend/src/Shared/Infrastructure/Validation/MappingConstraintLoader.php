<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Validation;

use App\Shared\Infrastructure\Mapper\MappingConfigLoader;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Loader\LoaderInterface;

final class MappingConstraintLoader implements LoaderInterface
{
    public function __construct(
        private readonly MappingConfigLoader $configLoader,
    ) {
    }

    public function loadClassMetadata(ClassMetadata $metadata): bool
    {
        $config = $this->configLoader->getConfigForClass($metadata->getClassName());

        if ($config === null) {
            return false;
        }

        $loaded = false;

        foreach ($config['fields'] as $field => $fieldConfig) {
            $validationRules = $fieldConfig['validation']['write'] ?? [];

            if ($validationRules === []) {
                continue;
            }

            $constraints = $this->buildConstraints($validationRules);

            if ($constraints !== []) {
                $metadata->addPropertyConstraints($field, $constraints);
                $loaded = true;
            }
        }

        return $loaded;
    }

    /**
     * @param list<array<string, mixed>> $rules
     *
     * @return list<\Symfony\Component\Validator\Constraint>
     */
    private function buildConstraints(array $rules): array
    {
        $constraints = [];

        foreach ($rules as $rule) {
            foreach ($rule as $name => $options) {
                $fqcn = 'Symfony\\Component\\Validator\\Constraints\\' . $name;

                if (!class_exists($fqcn)) {
                    throw new \InvalidArgumentException(\sprintf('Unknown validation constraint "%s".', $name));
                }

                $constraints[] = $options === null ? new $fqcn() : new $fqcn(...(array) $options);
            }
        }

        return $constraints;
    }
}
