<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use App\State\Processor\DeleteProfileProcessor;
use App\State\Processor\UpdateProfileProcessor;
use App\State\Provider\ProfileProvider;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'UserProfile',
    operations: [
        new Get(
            uriTemplate: '/user/profile',
            provider: ProfileProvider::class,
        ),
        new Put(
            uriTemplate: '/user/profile',
            provider: ProfileProvider::class,
            processor: UpdateProfileProcessor::class,
        ),
        new Delete(
            uriTemplate: '/user/profile',
            provider: ProfileProvider::class,
            processor: DeleteProfileProcessor::class,
        ),
    ],
    routePrefix: '/api',
)]
class UserProfile
{
    public string $id = '';

    public string $email = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $firstName = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $lastName = '';

    /** @var list<string> */
    public array $roles = [];

    public string $createdAt = '';

    public static function fromUser(\App\Entity\User $user): self
    {
        $dto = new self();
        $dto->id = (string) $user->getId();
        $dto->email = $user->getEmail();
        $dto->firstName = $user->getFirstName();
        $dto->lastName = $user->getLastName();
        $dto->roles = $user->getRoles();
        $dto->createdAt = $user->getCreatedAt()->format(\DateTimeInterface::ATOM);

        return $dto;
    }
}
