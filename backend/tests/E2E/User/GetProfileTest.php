<?php

declare(strict_types=1);

namespace App\Tests\E2E\User;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class GetProfileTest extends AbstractApiTestCase
{
    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('user')]
    public function get_profile_authenticated(): void
    {
        $this->createUser('profile@example.com', 'password123', 'Jane', 'Smith');
        $token = $this->getOAuth2Token('profile@example.com', 'password123');

        $response = $this->apiRequest('GET', '/api/user/profile', $token);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertSame('profile@example.com', $data['email']);
        $this->assertSame('Jane', $data['firstName']);
        $this->assertSame('Smith', $data['lastName']);
        $this->assertContains('ROLE_USER', $data['roles']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('user')]
    public function get_profile_unauthenticated(): void
    {
        $response = $this->apiRequest('GET', '/api/user/profile');

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
