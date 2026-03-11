<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Security\SecurityUser;
use App\Shared\Infrastructure\Mapper\EntityDtoMapper;
use App\User\Infrastructure\ApiPlatform\Resource\UserProfile;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProviderInterface<UserProfile>
 */
class ProfileProvider implements ProviderInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly EntityDtoMapper $mapper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): UserProfile
    {
        /** @var SecurityUser $securityUser */
        $securityUser = $this->security->getUser();

        return $this->mapper->toDto($securityUser->getUser(), UserProfile::class);
    }
}
