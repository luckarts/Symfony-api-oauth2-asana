<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Project\Infrastructure\ApiPlatform\State\Processor\CreateProjectProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Project',
    operations: [
        new Post(
            uriTemplate: '/projects',
            processor: CreateProjectProcessor::class,
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
