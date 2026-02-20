<?php

declare(strict_types=1);

namespace App\Tests\E2E\User;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class UpdateProfileTest extends AbstractApiTestCase
{
    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('user')]
    public function update_profile_success(): void
    {
        $this->createUser('update@example.com', 'password123', 'John', 'Doe');
        $token = $this->getOAuth2Token('update@example.com', 'password123');

        $response = $this->apiRequest('PUT', '/api/user/profile', $token, [
            'firstName' => 'Updated',
            'lastName' => 'Name',
        ]);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertSame('Updated', $data['firstName']);
        $this->assertSame('Name', $data['lastName']);
        $this->assertSame('update@example.com', $data['email']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('user')]
    public function update_profile_unauthenticated(): void
    {
        $response = $this->apiRequest('PUT', '/api/user/profile', data: [
            'firstName' => 'Updated',
            'lastName' => 'Name',
        ]);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('user')]
    public function update_profile_blank_fields(): void
    {
        $this->createUser('blank@example.com', 'password123', 'John', 'Doe');
        $token = $this->getOAuth2Token('blank@example.com', 'password123');

        $response = $this->apiRequest('PUT', '/api/user/profile', $token, [
            'firstName' => '',
            'lastName' => '',
        ]);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }
}
