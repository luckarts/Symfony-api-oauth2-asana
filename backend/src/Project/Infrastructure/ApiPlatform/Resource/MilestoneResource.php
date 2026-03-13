<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\Project\Infrastructure\ApiPlatform\State\Processor\CreateMilestoneProcessor;
use App\Project\Infrastructure\ApiPlatform\State\Provider\MilestoneCollectionProvider;
use App\Project\Infrastructure\ApiPlatform\State\Provider\MilestoneItemProvider;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Milestone',
    operations: [
        new GetCollection(
            uriTemplate: '/projects/{projectId}/milestones',
            uriVariables: [
                'projectId' => new Link(fromClass: self::class, identifiers: ['projectId']),
            ],
            provider: MilestoneCollectionProvider::class,
            security: "is_granted('ROLE_USER')",
        ),
        new Post(
            uriTemplate: '/projects/{projectId}/milestones',
            uriVariables: [
                'projectId' => new Link(fromClass: self::class, identifiers: ['projectId']),
            ],
            processor: CreateMilestoneProcessor::class,
            security: "is_granted('ROLE_USER')",
        ),
        new Get(
            uriTemplate: '/projects/{projectId}/milestones/{id}',
            uriVariables: [
                'projectId' => new Link(fromClass: self::class, identifiers: ['projectId']),
                'id' => new Link(fromClass: self::class, identifiers: ['id']),
            ],
            provider: MilestoneItemProvider::class,
            security: "is_granted('ROLE_USER')",
        ),
    ],
    routePrefix: '/api',
)]
class MilestoneResource
{
    public string $id = '';

    public string $projectId = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $title = '';

    public string $status = 'pending';

    public ?string $dueDate = null;

    public string $createdAt = '';

    public string $updatedAt = '';
}
