<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Mapper;

use Symfony\Component\Yaml\Yaml;

final class MappingConfigLoader implements MappingConfigLoaderInterface
{
    /** @var array<string, array<string, mixed>> class FQCN => resource config */
    private array $configs = [];

    private bool $loaded = false;

    /**
     * @param list<string> $configDirs
     */
    public function __construct(
        private readonly array $configDirs,
    ) {
    }

    /**
     * @return array{entity: string, fields: array<string, array<string, mixed>>}|null
     */
    public function getConfigForClass(string $className): ?array
    {
        $this->load();

        return $this->configs[$className] ?? null;
    }

    /**
     * @return array<string, array{entity: string, fields: array<string, array<string, mixed>>}>
     */
    public function getAllConfigs(): array
    {
        $this->load();

        return $this->configs;
    }

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        foreach ($this->configDirs as $dir) {
            $this->loadDir($dir);
        }

        $this->loaded = true;
    }

    private function loadDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = glob($dir.'/*_mapping.yaml');
        if (false === $files) {
            return;
        }

        foreach ($files as $file) {
            $this->loadFile($file);
        }
    }

    private function loadFile(string $file): void
    {
        /** @var array{entity: string, resources: array<string, array{class: string, fields: array<string, mixed>}>} $data */
        $data = Yaml::parseFile($file);

        $entity = $data['entity'];

        foreach ($data['resources'] as $resourceConfig) {
            $class = $resourceConfig['class'];
            $this->configs[$class] = [
                'entity' => $entity,
                'fields' => $resourceConfig['fields'],
            ];
        }
    }
}
