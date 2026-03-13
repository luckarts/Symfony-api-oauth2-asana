<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\Project\Infrastructure\ApiPlatform\Resource\ProjectResource;
use App\Project\Infrastructure\ApiPlatform\State\Processor\CreateMilestoneProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Milestone',
    operations: [
        new Post(
            uriTemplate: '/projects/{id}/milestones',
            uriVariables: [
                'id' => new Link(fromClass: ProjectResource::class, identifiers: ['id']),
            ],
            processor: CreateMilestoneProcessor::class,
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
