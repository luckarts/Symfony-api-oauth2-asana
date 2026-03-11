<?php

declare(strict_types=1);

namespace App\User\Domain\Exception;

class UserAlreadyExistsException extends \DomainException
{
    public static function withEmail(string $email): self
    {
        return new self(sprintf('Un compte avec l\'email "%s" existe déjà.', $email));
    }
}
