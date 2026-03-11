<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\Project\Domain\Entity\Project;
use App\Project\Infrastructure\ApiPlatform\State\Provider\BoardColumnCollectionProvider;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'BoardColumn',
    operations: [
        new GetCollection(
            uriTemplate: '/projects/{id}/columns',
            uriVariables: [
                'id' => new Link(fromClass: Project::class, identifiers: ['id']),
            ],
            provider: BoardColumnCollectionProvider::class,
            security: "is_granted('ROLE_USER')",
        ),
    ],
    routePrefix: '/api',
)]
class BoardColumnResource
{
    public string $id = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $title = '';

    public int $position = 0;

    public ?int $wipLimit = null;

    public bool $isDefault = false;

    public string $projectId = '';

    public string $createdAt = '';

    public string $updatedAt = '';
}
