<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\Security;

use App\Project\Domain\Entity\Project;
use App\Security\SecurityUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Project>
 */
final class PersonalProjectVoter extends Voter
{
    public const string PROJECT_VIEW = 'PROJECT_VIEW';
    public const string PROJECT_EDIT = 'PROJECT_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::PROJECT_VIEW, self::PROJECT_EDIT], true)
            && $subject instanceof Project;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof SecurityUser) {
            return false;
        }

        /** @var Project $subject */
        return $subject->getUserId() === (string) $user->getUser()->getId();
    }
}
