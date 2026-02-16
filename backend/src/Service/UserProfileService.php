<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserProfileService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function update(User $user, string $firstName, string $lastName): User
    {
        $user->setFirstName($firstName);
        $user->setLastName($lastName);

        $this->em->flush();

        return $user;
    }

    public function delete(User $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }
}
