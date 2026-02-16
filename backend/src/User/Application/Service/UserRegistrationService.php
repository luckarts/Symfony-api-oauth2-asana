<?php

declare(strict_types=1);

namespace App\User\Application\Service;

use App\User\Domain\Contract\PasswordHasherInterface;
use App\User\Domain\Contract\UserRepositoryInterface;
use App\User\Domain\Entity\User;
use App\User\Domain\Enum\Role;
use App\User\Domain\Event\UserRegisteredEvent;
use App\User\Domain\Exception\UserAlreadyExistsException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserRegistrationService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherInterface $passwordHasher,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function register(string $email, string $plainPassword, string $firstName, string $lastName): User
    {
        if ($this->userRepository->existsByEmail($email)) {
            throw UserAlreadyExistsException::withEmail($email);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setRoles([Role::ROLE_USER]);
        $user->setPassword($this->passwordHasher->hash($plainPassword));

        $this->userRepository->save($user);

        $this->eventDispatcher->dispatch(new UserRegisteredEvent(
            (string) $user->getId(),
            $user->getEmail(),
        ));

        return $user;
    }
}
