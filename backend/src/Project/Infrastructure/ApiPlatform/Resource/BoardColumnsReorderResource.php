<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\Project\Domain\Entity\Project;
use App\Project\Infrastructure\ApiPlatform\State\Processor\ReorderBoardColumnsProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'BoardColumnsReorder',
    operations: [
        new Post(
            uriTemplate: '/projects/{projectId}/columns/reorder',
            uriVariables: [
                'projectId' => new Link(fromClass: Project::class, identifiers: ['id']),
            ],
            processor: ReorderBoardColumnsProcessor::class,
            security: "is_granted('ROLE_USER')",
        ),
    ],
    routePrefix: '/api',
)]
class BoardColumnsReorderResource
{
    /**
     * @var list<string>
     */
    #[Assert\Count(min: 1)]
    public array $order = [];
}
