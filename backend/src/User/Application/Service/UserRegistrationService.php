<?php

declare(strict_types=1);

namespace App\User\Application\Service;

use App\User\Domain\Contract\PasswordHasherInterface;
use App\User\Domain\Contract\UserRepositoryInterface;
use App\User\Domain\Entity\User;
use App\User\Domain\Event\UserRegisteredEvent;
use App\User\Domain\Exception\UserAlreadyExistsException;
use App\User\Infrastructure\ApiPlatform\Resource\RegisterUserRequest;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserRegistrationService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $passwordHasher,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function register(RegisterUserRequest $request): User
    {
        if ($this->userRepository->existsByEmail($request->email)) {
            throw UserAlreadyExistsException::withEmail($request->email);
        }

        $user = User::register(
            email: $request->email,
            hashedPassword: $this->passwordHasher->hash($request->password),
            firstName: $request->firstName,
            lastName: $request->lastName,
        );

        $this->userRepository->save($user);

        $this->eventDispatcher->dispatch(new UserRegisteredEvent(
            (string) $user->getId(),
            $user->getEmail(),
        ));

        return $user;
    }
}
