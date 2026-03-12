<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Security\SecurityUser;
use App\User\Application\Service\UserProfileService;
use App\User\Infrastructure\ApiPlatform\Resource\UserProfile;
use App\User\Infrastructure\ApiPlatform\Transformer\UserProfileTransformer;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProcessorInterface<UserProfile, UserProfile>
 */
class UpdateProfileProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserProfileService $profileService,
        private readonly Security $security,
        private readonly UserProfileTransformer $transformer,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserProfile
    {
        assert($data instanceof UserProfile);

        /** @var SecurityUser $securityUser */
        $securityUser = $this->security->getUser();

        $user = $securityUser->getUser();
        $this->transformer->fromResource($data, $user);

        $user = $this->profileService->update($user);

        return $this->transformer->toResource($user);
    }
}
