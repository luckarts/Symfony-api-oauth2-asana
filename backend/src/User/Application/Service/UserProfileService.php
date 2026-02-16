<?php

declare(strict_types=1);

namespace App\User\Application\Service;

use App\User\Domain\Contract\UserRepositoryInterface;
use App\User\Domain\Entity\User;
use App\User\Domain\Event\UserDeletedEvent;
use App\User\Domain\Event\UserProfileUpdatedEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserProfileService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function update(User $user, string $firstName, string $lastName): User
    {
        $user->setFirstName($firstName);
        $user->setLastName($lastName);

        $this->userRepository->save($user);

        $this->eventDispatcher->dispatch(new UserProfileUpdatedEvent(
            (string) $user->getId(),
        ));

        return $user;
    }

    public function delete(User $user): void
    {
        $userId = (string) $user->getId();
        $email = $user->getEmail();

        $this->userRepository->remove($user);

        $this->eventDispatcher->dispatch(new UserDeletedEvent($userId, $email));
    }
}
