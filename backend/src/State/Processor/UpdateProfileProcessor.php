<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\UserProfile;
use App\Security\SecurityUser;
use App\Service\UserProfileService;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProcessorInterface<UserProfile, UserProfile>
 */
class UpdateProfileProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserProfileService $profileService,
        private readonly Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserProfile
    {
        assert($data instanceof UserProfile);

        /** @var SecurityUser $securityUser */
        $securityUser = $this->security->getUser();

        $user = $this->profileService->update(
            $securityUser->getUser(),
            $data->firstName,
            $data->lastName,
        );

        return UserProfile::fromUser($user);
    }
}
