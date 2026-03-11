<?php

declare(strict_types=1);

namespace App\Tests\E2E\Project;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class CreateProjectTest extends AbstractApiTestCase
{
    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('project')]
    public function create_project_success(): void
    {
        $this->createUser('project-create@example.com', 'password123');
        $token = $this->getOAuth2Token('project-create@example.com', 'password123');

        $response = $this->apiRequest('POST', '/api/projects', $token, [
            'name' => 'My first project',
        ]);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertSame('My first project', $data['name']);
        $this->assertSame('active', $data['status']);
        $this->assertNull($data['description']);
        $this->assertNotEmpty($data['id']);
        $this->assertNotEmpty($data['createdAt']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function create_project_with_description(): void
    {
        $this->createUser('project-desc@example.com', 'password123');
        $token = $this->getOAuth2Token('project-desc@example.com', 'password123');

        $response = $this->apiRequest('POST', '/api/projects', $token, [
            'name' => 'Project with desc',
            'description' => 'A longer description',
            'status' => 'on_hold',
        ]);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertSame('A longer description', $data['description']);
        $this->assertSame('on_hold', $data['status']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function create_project_fails_with_empty_name(): void
    {
        $this->createUser('project-empty@example.com', 'password123');
        $token = $this->getOAuth2Token('project-empty@example.com', 'password123');

        $response = $this->apiRequest('POST', '/api/projects', $token, [
            'name' => '',
        ]);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function create_project_requires_authentication(): void
    {
        $response = $this->apiRequest('POST', '/api/projects', null, [
            'name' => 'Unauthenticated project',
        ]);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
