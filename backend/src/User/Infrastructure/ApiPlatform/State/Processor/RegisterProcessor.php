<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\User\Application\Service\UserRegistrationService;
use App\User\Domain\Exception\UserAlreadyExistsException;
use App\User\Infrastructure\ApiPlatform\Resource\RegisterUserRequest;
use App\User\Infrastructure\ApiPlatform\Resource\UserProfile;
use App\User\Infrastructure\ApiPlatform\Transformer\UserProfileTransformer;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @implements ProcessorInterface<RegisterUserRequest, UserProfile>
 */
class RegisterProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserRegistrationService $registrationService,
        private readonly UserProfileTransformer $transformer,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserProfile
    {
        assert($data instanceof RegisterUserRequest);

        try {
            $user = $this->registrationService->register($data);
        } catch (UserAlreadyExistsException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        return $this->transformer->toResource($user);
    }
}
