<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\RegisterUserRequest;
use App\ApiResource\UserProfile;
use App\Service\UserRegistrationService;

/**
 * @implements ProcessorInterface<RegisterUserRequest, UserProfile>
 */
class RegisterProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserRegistrationService $registrationService,
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

        return UserProfile::fromUser($user);
    }
}
