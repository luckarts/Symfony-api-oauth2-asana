<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use App\User\Infrastructure\ApiPlatform\State\Processor\DeleteProfileProcessor;
use App\User\Infrastructure\ApiPlatform\State\Processor\UpdateProfileProcessor;
use App\User\Infrastructure\ApiPlatform\State\Provider\ProfileProvider;

#[ApiResource(
    shortName: 'UserProfile',
    operations: [
        new Get(
            uriTemplate: '/user/profile',
            provider: ProfileProvider::class,
        ),
        new Put(
            uriTemplate: '/user/profile',
            provider: ProfileProvider::class,
            processor: UpdateProfileProcessor::class,
        ),
        new Delete(
            uriTemplate: '/user/profile',
            provider: ProfileProvider::class,
            processor: DeleteProfileProcessor::class,
        ),
    ],
    routePrefix: '/api',
)]
class UserProfile
{
    public string $id = '';
    public string $email = '';
    public string $firstName = '';
    public string $lastName = '';

    /** @var list<string> */
    public array $roles = [];

    public string $createdAt = '';
}
