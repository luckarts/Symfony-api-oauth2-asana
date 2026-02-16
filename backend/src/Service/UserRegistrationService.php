<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Enum\Role;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserRegistrationService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function register(string $email, string $plainPassword, string $firstName, string $lastName): User
    {
        if ($this->userRepository->existsByEmail($email)) {
            throw new \DomainException('Un compte avec cet email existe déjà.');
        }

        $user = new User();
        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setRoles([Role::ROLE_USER]);

        $hashedPassword = $this->passwordHasher->hashPassword(
            new class($plainPassword) implements PasswordAuthenticatedUserInterface {
                public function __construct(private readonly string $password) {}
                public function getPassword(): string { return $this->password; }
            },
            $plainPassword,
        );
        $user->setPassword($hashedPassword);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
