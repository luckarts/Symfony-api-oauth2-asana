<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ApiPlatform\Transformer;

use App\User\Domain\Entity\User;
use App\User\Infrastructure\ApiPlatform\Resource\UserProfile;

final class UserProfileTransformer
{
    public function toResource(User $user): UserProfile
    {
        $resource = new UserProfile();
        $resource->id = (string) $user->getId();
        $resource->email = $user->getEmail();
        $resource->firstName = $user->getFirstName();
        $resource->lastName = $user->getLastName();
        $resource->roles = $user->getRoles();
        $resource->createdAt = $user->getCreatedAt()->format(\DateTimeInterface::ATOM);

        return $resource;
    }

    public function fromResource(UserProfile $resource, User $user): void
    {
        $user->setFirstName($resource->firstName);
        $user->setLastName($resource->lastName);
    }
}
