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
        $project = $this->createProject($token, 'Task project');

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/tasks', $token, [
            'title' => 'My first task',
        ]);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertNotEmpty($data['id']);
        $this->assertSame('My first task', $data['title']);
        $this->assertSame('todo', $data['status']);
        $this->assertFalse($data['isCompleted']);
        $this->assertSame(0, $data['orderIndex']);
        $this->assertNull($data['columnId']);
        $this->assertNull($data['dueDate']);
        $this->assertSame($project['id'], $data['projectId']);
        $this->assertNotEmpty($data['createdAt']);

        // Verify persistence via GET item
        $get = $this->apiRequest('GET', '/api/projects/'.$project['id'].'/tasks/'.$data['id'], $token);
        $this->assertSame(Response::HTTP_OK, $get->getStatusCode());
        $fetched = json_decode($get->getContent(), true);
        $this->assertSame($data['id'], $fetched['id']);
        $this->assertSame('My first task', $fetched['title']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function create_task_with_order_index(): void
    {
        $this->createUser('task-order@example.com', 'password123');
        $token = $this->getOAuth2Token('task-order@example.com', 'password123');
        $project = $this->createProject($token, 'Order project');

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/tasks', $token, [
            'title' => 'Ordered task',
            'orderIndex' => 3,
        ]);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame(3, $data['orderIndex']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function create_task_fails_with_empty_title(): void
    {
        $this->createUser('task-empty@example.com', 'password123');
        $token = $this->getOAuth2Token('task-empty@example.com', 'password123');
        $project = $this->createProject($token, 'Validation project');

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/tasks', $token, [
            'title' => '',
        ]);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function create_task_project_not_found(): void
    {
        $this->createUser('task-404@example.com', 'password123');
        $token = $this->getOAuth2Token('task-404@example.com', 'password123');

        $response = $this->apiRequest(
            'POST',
            '/api/projects/00000000-0000-0000-0000-000000000000/tasks',
            $token,
            ['title' => 'Ghost task'],
        );

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function create_task_forbidden_for_non_owner(): void
    {
        $this->createUser('task-owner@example.com', 'password123');
        $ownerToken = $this->getOAuth2Token('task-owner@example.com', 'password123');
        $project = $this->createProject($ownerToken, 'Owner project');

        $this->createUser('task-other@example.com', 'password123');
        $otherToken = $this->getOAuth2Token('task-other@example.com', 'password123');

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/tasks', $otherToken, [
            'title' => 'Stolen task',
        ]);

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function create_task_requires_authentication(): void
    {
        $this->createUser('task-unauth@example.com', 'password123');
        $token = $this->getOAuth2Token('task-unauth@example.com', 'password123');
        $project = $this->createProject($token, 'Auth project');

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/tasks', null, [
            'title' => 'Unauthenticated task',
        ]);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /** @return array<string, mixed> */
    private function createProject(string $token, string $name): array
    {
        return json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => $name])->getContent(),
            true,
        );
    }
}
