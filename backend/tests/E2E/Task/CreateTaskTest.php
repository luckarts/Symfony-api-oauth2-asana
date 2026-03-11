<?php

declare(strict_types=1);

namespace App\Tests\E2E\Task;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class CreateTaskTest extends AbstractApiTestCase
{
    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('task')]
    public function create_task_success(): void
    {
        $this->createUser('task-create@example.com', 'password123');
        $token = $this->getOAuth2Token('task-create@example.com', 'password123');

        $response = $this->apiRequest('POST', '/api/tasks', $token, [
            'title' => 'My first task',
        ]);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertSame('My first task', $data['title']);
        $this->assertSame('todo', $data['status']);
        $this->assertNotEmpty($data['id']);
        $this->assertNotEmpty($data['createdAt']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function create_task_fails_with_empty_title(): void
    {
        $this->createUser('task-empty@example.com', 'password123');
        $token = $this->getOAuth2Token('task-empty@example.com', 'password123');

        $response = $this->apiRequest('POST', '/api/tasks', $token, [
            'title' => '',
        ]);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function create_task_requires_authentication(): void
    {
        $response = $this->apiRequest('POST', '/api/tasks', null, [
            'title' => 'Unauthenticated task',
        ]);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
