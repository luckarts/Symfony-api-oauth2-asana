<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Task\Infrastructure\ApiPlatform\State\Processor\CreateTaskProcessor;
use App\Task\Infrastructure\ApiPlatform\State\Processor\DeleteTaskProcessor;
use App\Task\Infrastructure\ApiPlatform\State\Processor\UpdateTaskProcessor;
use App\Task\Infrastructure\ApiPlatform\State\Provider\TaskCollectionProvider;
use App\Task\Infrastructure\ApiPlatform\State\Provider\TaskItemProvider;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Task',
    operations: [
        new Post(
            uriTemplate: '/projects/{projectId}/tasks',
            uriVariables: ['projectId' => new Link(fromClass: TaskResource::class, identifiers: ['projectId'])],
            processor: CreateTaskProcessor::class,
            security: "is_granted('ROLE_USER')",
        ),
        new GetCollection(
            uriTemplate: '/projects/{projectId}/tasks',
            uriVariables: ['projectId' => new Link(fromClass: TaskResource::class, identifiers: ['projectId'])],
            provider: TaskCollectionProvider::class,
            security: "is_granted('ROLE_USER')",
        ),
        new Get(
            uriTemplate: '/projects/{projectId}/tasks/{id}',
            uriVariables: [
                'projectId' => new Link(fromClass: TaskResource::class, identifiers: ['projectId']),
                'id' => new Link(fromClass: TaskResource::class, identifiers: ['id']),
            ],
            provider: TaskItemProvider::class,
            security: "is_granted('ROLE_USER')",
        ),
        new Patch(
            uriTemplate: '/projects/{projectId}/tasks/{id}',
            uriVariables: [
                'projectId' => new Link(fromClass: TaskResource::class, identifiers: ['projectId']),
                'id' => new Link(fromClass: TaskResource::class, identifiers: ['id']),
            ],
            provider: TaskItemProvider::class,
            processor: UpdateTaskProcessor::class,
            security: "is_granted('ROLE_USER')",
        ),
        new Delete(
            uriTemplate: '/projects/{projectId}/tasks/{id}',
            uriVariables: [
                'projectId' => new Link(fromClass: TaskResource::class, identifiers: ['projectId']),
                'id' => new Link(fromClass: TaskResource::class, identifiers: ['id']),
            ],
            read: false,
            processor: DeleteTaskProcessor::class,
            security: "is_granted('ROLE_USER')",
        ),
    ],
    routePrefix: '/api',
)]
class TaskResource
{
    public string $id = '';

    public string $projectId = '';

    public ?string $columnId = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $title = '';

    public string $status = 'todo';

    public bool $isCompleted = false;

    public ?string $dueDate = null;

    public int $orderIndex = 0;

    public string $createdAt = '';

    public string $updatedAt = '';
}
