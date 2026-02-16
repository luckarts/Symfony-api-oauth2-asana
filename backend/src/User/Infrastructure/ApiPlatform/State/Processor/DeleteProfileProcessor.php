<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Security\SecurityUser;
use App\User\Application\Service\UserProfileService;
use App\User\Infrastructure\ApiPlatform\Resource\UserProfile;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProcessorInterface<UserProfile, void>
 */
class DeleteProfileProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserProfileService $profileService,
        private readonly Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        /** @var SecurityUser $securityUser */
        $securityUser = $this->security->getUser();

        $this->profileService->delete($securityUser->getUser());
    }
}
