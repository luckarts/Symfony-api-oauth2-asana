<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Shared\Infrastructure\Mapper\EntityDtoMapper;
use App\User\Application\Service\UserRegistrationService;
use App\User\Domain\Exception\UserAlreadyExistsException;
use App\User\Infrastructure\ApiPlatform\Resource\RegisterUserRequest;
use App\User\Infrastructure\ApiPlatform\Resource\UserProfile;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

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

        try {
            $user = $this->registrationService->register(
                $data->email,
                $data->password,
                $data->firstName,
                $data->lastName,
            );
        } catch (UserAlreadyExistsException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        return $this->mapper->toDto($user, UserProfile::class);
    }
}
