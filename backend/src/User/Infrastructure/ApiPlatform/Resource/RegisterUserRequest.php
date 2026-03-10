<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\User\Infrastructure\ApiPlatform\State\Processor\RegisterProcessor;

#[ApiResource(
    shortName: 'Register',
    operations: [
        new Post(
            uriTemplate: '/register',
            processor: RegisterProcessor::class,
            output: UserProfile::class,
        ),
    ],
    routePrefix: '/api',
)]
class RegisterUserRequest
{
    public string $email = '';
    public string $password = '';
    public string $firstName = '';
    public string $lastName = '';
}
