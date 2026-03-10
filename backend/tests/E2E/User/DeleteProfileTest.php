<?php

declare(strict_types=1);

namespace App\Tests\E2E\User;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class DeleteProfileTest extends AbstractApiTestCase
{
    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('user')]
    public function delete_profile_success(): void
    {
        $this->createUser('delete@example.com', 'password123', 'John', 'Doe');
        $token = $this->getOAuth2Token('delete@example.com', 'password123');

        $response = $this->apiRequest('DELETE', '/api/user/profile', $token);

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $response = $this->apiRequest('GET', '/api/user/profile', $token);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('user')]
    public function delete_profile_unauthenticated(): void
    {
        $response = $this->apiRequest('DELETE', '/api/user/profile');

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
