<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Project\Domain\Entity\BoardColumn;
use App\Project\Domain\Entity\Project;
use App\Project\Infrastructure\ApiPlatform\State\Processor\CreateBoardColumnProcessor;
use App\Project\Infrastructure\ApiPlatform\State\Processor\DeleteBoardColumnProcessor;
use App\Project\Infrastructure\ApiPlatform\State\Processor\UpdateBoardColumnProcessor;
use App\Project\Infrastructure\ApiPlatform\State\Provider\BoardColumnCollectionProvider;
use App\Project\Infrastructure\ApiPlatform\State\Provider\BoardColumnItemProvider;
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
        new Post(
            uriTemplate: '/projects/{id}/columns',
            uriVariables: [
                'id' => new Link(fromClass: Project::class, identifiers: ['id']),
            ],
            processor: CreateBoardColumnProcessor::class,
            security: "is_granted('ROLE_USER')",
        ),
        new Patch(
            uriTemplate: '/projects/{id}/columns/{colId}',
            uriVariables: [
                'id' => new Link(fromClass: Project::class, identifiers: ['id']),
                'colId' => new Link(fromClass: BoardColumn::class, identifiers: ['id']),
            ],
            provider: BoardColumnItemProvider::class,
            processor: UpdateBoardColumnProcessor::class,
            security: "is_granted('ROLE_USER')",
        ),
        new Delete(
            uriTemplate: '/projects/{id}/columns/{colId}',
            uriVariables: [
                'id' => new Link(fromClass: Project::class, identifiers: ['id']),
                'colId' => new Link(fromClass: BoardColumn::class, identifiers: ['id']),
            ],
            read: false,
            processor: DeleteBoardColumnProcessor::class,
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
