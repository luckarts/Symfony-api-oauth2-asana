<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Shared\Infrastructure\Mapper\EntityDtoMapper;
use App\User\Application\Service\UserRegistrationService;
use App\User\Infrastructure\ApiPlatform\Resource\RegisterUserRequest;
use App\User\Infrastructure\ApiPlatform\Resource\UserProfile;

/**
 * @implements ProcessorInterface<RegisterUserRequest, UserProfile>
 */
class RegisterProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserRegistrationService $registrationService,
        private readonly EntityDtoMapper $mapper,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserProfile
    {
        assert($data instanceof RegisterUserRequest);

        $user = $this->registrationService->register(
            $data->email,
            $data->password,
            $data->firstName,
            $data->lastName,
        );

        return $this->mapper->toDto($user, UserProfile::class);
    }
}
