<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\Project\Infrastructure\ApiPlatform\State\Processor\CreateProjectProcessor;
use App\Project\Infrastructure\ApiPlatform\State\Provider\ProjectCollectionProvider;
use App\Project\Infrastructure\ApiPlatform\State\Provider\ProjectItemProvider;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Project',
    operations: [
        new Post(
            uriTemplate: '/projects',
            processor: CreateProjectProcessor::class,
            security: "is_granted('ROLE_USER')",
        ),
        new GetCollection(
            uriTemplate: '/projects',
            provider: ProjectCollectionProvider::class,
            security: "is_granted('ROLE_USER')",
        ),
        new Get(
            uriTemplate: '/projects/{id}',
            uriVariables: ['id' => new Link(fromClass: ProjectResource::class, identifiers: ['id'])],
            provider: ProjectItemProvider::class,
            security: "is_granted('ROLE_USER')",
        ),
    ],
    routePrefix: '/api',
)]
class ProjectResource
{
    public string $id = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    public string $name = '';

    public string $status = 'active';

    public ?string $description = null;

    public string $createdAt = '';

    public string $updatedAt = '';
}
