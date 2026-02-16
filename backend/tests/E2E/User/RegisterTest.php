<?php

declare(strict_types=1);

namespace App\Tests\E2E\User;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class RegisterTest extends AbstractApiTestCase
{
    #[Test]
    public function register_success(): void
    {
        $response = $this->apiRequest('POST', '/api/register', data: [
            'email' => 'new@example.com',
            'password' => 'password123',
            'firstName' => 'John',
            'lastName' => 'Doe',
        ]);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('new@example.com', $data['email']);
        $this->assertSame('John', $data['firstName']);
        $this->assertSame('Doe', $data['lastName']);
        $this->assertContains('ROLE_USER', $data['roles']);
        $this->assertArrayHasKey('createdAt', $data);
    }

    #[Test]
    public function register_duplicate_email(): void
    {
        $this->createUser('duplicate@example.com', 'password123', 'First', 'User');

        $response = $this->apiRequest('POST', '/api/register', data: [
            'email' => 'duplicate@example.com',
            'password' => 'password456',
            'firstName' => 'Second',
            'lastName' => 'User',
        ]);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    #[Test]
    public function register_invalid_email(): void
    {
        $response = $this->apiRequest('POST', '/api/register', data: [
            'email' => 'not-an-email',
            'password' => 'password123',
            'firstName' => 'John',
            'lastName' => 'Doe',
        ]);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    #[Test]
    public function register_short_password(): void
    {
        $response = $this->apiRequest('POST', '/api/register', data: [
            'email' => 'short@example.com',
            'password' => 'short',
            'firstName' => 'John',
            'lastName' => 'Doe',
        ]);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    #[Test]
    public function register_missing_fields(): void
    {
        $response = $this->apiRequest('POST', '/api/register', data: []);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }
}
