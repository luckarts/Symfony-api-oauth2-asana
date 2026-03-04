<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
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
            uriTemplate: '/tasks',
            processor: CreateTaskProcessor::class,
            security: "is_granted('ROLE_USER')",
        ),
        new GetCollection(
            uriTemplate: '/tasks',
            provider: TaskCollectionProvider::class,
            security: "is_granted('ROLE_USER')",
        ),
        new Get(
            uriTemplate: '/tasks/{id}',
            uriVariables: ['id' => new Link()],
            provider: TaskItemProvider::class,
            security: "is_granted('ROLE_USER')",
        ),
        new Put(
            uriTemplate: '/tasks/{id}',
            uriVariables: ['id' => new Link()],
            read: false,
            processor: UpdateTaskProcessor::class,
            security: "is_granted('ROLE_USER')",
        ),
        new Delete(
            uriTemplate: '/tasks/{id}',
            uriVariables: ['id' => new Link()],
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

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $title = '';

    public string $status = 'todo';

    public string $createdAt = '';
}
