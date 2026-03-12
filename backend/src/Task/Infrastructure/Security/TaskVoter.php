<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Security;

use App\Security\SecurityUser;
use App\Task\Domain\Entity\Task;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Task>
 */
final class TaskVoter extends Voter
{
    public const string TASK_VIEW = 'TASK_VIEW';
    public const string TASK_EDIT = 'TASK_EDIT';
    public const string TASK_DELETE = 'TASK_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::TASK_VIEW, self::TASK_EDIT, self::TASK_DELETE], true)
            && $subject instanceof Task;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof SecurityUser) {
            return false;
        }

        /** @var Task $subject */
        return $subject->getProject()->getUserId() === (string) $user->getUser()->getId();
    }
}
