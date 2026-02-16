<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Validation;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class MappingValidationCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('validator.builder')) {
            return;
        }

        $validatorBuilder = $container->findDefinition('validator.builder');
        $validatorBuilder->addMethodCall('addLoader', [new Reference(MappingConstraintLoader::class)]);
    }
}
