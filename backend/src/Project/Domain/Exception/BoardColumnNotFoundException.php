<?php

declare(strict_types=1);

namespace App\Project\Domain\Exception;

final class BoardColumnNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct(sprintf('Board column "%s" not found.', $id));
    }
}
