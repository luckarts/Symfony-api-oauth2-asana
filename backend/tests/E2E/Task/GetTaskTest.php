<?php

declare(strict_types=1);

namespace App\Tests\E2E\Task;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class GetTaskTest extends AbstractApiTestCase
{
    // -------------------------------------------------------------------------
    // GET /api/projects/{projectId}/tasks
    // -------------------------------------------------------------------------

    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('task')]
    public function get_task_collection_returns_tasks_for_project(): void
    {
        $this->createUser('task-col@example.com', 'password123');
        $token = $this->getOAuth2Token('task-col@example.com', 'password123');
        $project = $this->createProject($token, 'Collection project');

        $this->apiRequest('POST', '/api/projects/'.$project['id'].'/tasks', $token, ['title' => 'Task A']);
        $this->apiRequest('POST', '/api/projects/'.$project['id'].'/tasks', $token, ['title' => 'Task B']);

        $response = $this->apiRequest('GET', '/api/projects/'.$project['id'].'/tasks', $token);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertCount(2, $data['member']);
        $titles = array_column($data['member'], 'title');
        $this->assertContains('Task A', $titles);
        $this->assertContains('Task B', $titles);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function get_task_collection_is_isolated_per_project(): void
    {
        $this->createUser('task-iso@example.com', 'password123');
        $token = $this->getOAuth2Token('task-iso@example.com', 'password123');

        $projectA = $this->createProject($token, 'Iso project A');
        $projectB = $this->createProject($token, 'Iso project B');

        $this->apiRequest('POST', '/api/projects/'.$projectA['id'].'/tasks', $token, ['title' => 'Task in A']);

        $response = $this->apiRequest('GET', '/api/projects/'.$projectB['id'].'/tasks', $token);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertCount(0, $data['member']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function get_task_collection_project_not_found(): void
    {
        $this->createUser('task-col-404@example.com', 'password123');
        $token = $this->getOAuth2Token('task-col-404@example.com', 'password123');

        $response = $this->apiRequest('GET', '/api/projects/00000000-0000-0000-0000-000000000000/tasks', $token);

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function get_task_collection_forbidden_for_non_owner(): void
    {
        $this->createUser('task-col-owner@example.com', 'password123');
        $ownerToken = $this->getOAuth2Token('task-col-owner@example.com', 'password123');
        $project = $this->createProject($ownerToken, 'Private collection project');

        $this->createUser('task-col-other@example.com', 'password123');
        $otherToken = $this->getOAuth2Token('task-col-other@example.com', 'password123');

        $response = $this->apiRequest('GET', '/api/projects/'.$project['id'].'/tasks', $otherToken);

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function get_task_collection_requires_authentication(): void
    {
        $this->createUser('task-col-unauth@example.com', 'password123');
        $token = $this->getOAuth2Token('task-col-unauth@example.com', 'password123');
        $project = $this->createProject($token, 'Auth collection project');

        $response = $this->apiRequest('GET', '/api/projects/'.$project['id'].'/tasks', null);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // GET /api/projects/{projectId}/tasks/{id}
    // -------------------------------------------------------------------------

    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('task')]
    public function get_task_item_returns_all_fields(): void
    {
        $this->createUser('task-item@example.com', 'password123');
        $token = $this->getOAuth2Token('task-item@example.com', 'password123');
        $project = $this->createProject($token, 'Item project');

        $created = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$project['id'].'/tasks', $token, [
                'title' => 'My task',
                'orderIndex' => 2,
            ])->getContent(),
            true,
        );

        $response = $this->apiRequest('GET', '/api/projects/'.$project['id'].'/tasks/'.$created['id'], $token);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame($created['id'], $data['id']);
        $this->assertSame('My task', $data['title']);
        $this->assertSame($project['id'], $data['projectId']);
        $this->assertSame('todo', $data['status']);
        $this->assertFalse($data['isCompleted']);
        $this->assertSame(2, $data['orderIndex']);
        $this->assertNull($data['columnId']);
        $this->assertNull($data['dueDate']);
        $this->assertNotEmpty($data['createdAt']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function get_task_item_from_wrong_project_returns_404(): void
    {
        $this->createUser('task-cross@example.com', 'password123');
        $token = $this->getOAuth2Token('task-cross@example.com', 'password123');

        $projectA = $this->createProject($token, 'Cross project A');
        $projectB = $this->createProject($token, 'Cross project B');

        $task = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$projectA['id'].'/tasks', $token, ['title' => 'Task in A'])->getContent(),
            true,
        );

        $response = $this->apiRequest('GET', '/api/projects/'.$projectB['id'].'/tasks/'.$task['id'], $token);

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function get_task_item_not_found(): void
    {
        $this->createUser('task-item-404@example.com', 'password123');
        $token = $this->getOAuth2Token('task-item-404@example.com', 'password123');
        $project = $this->createProject($token, 'Item 404 project');

        $response = $this->apiRequest(
            'GET',
            '/api/projects/'.$project['id'].'/tasks/00000000-0000-0000-0000-000000000000',
            $token,
        );

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function get_task_item_forbidden_for_non_owner(): void
    {
        $this->createUser('task-item-owner@example.com', 'password123');
        $ownerToken = $this->getOAuth2Token('task-item-owner@example.com', 'password123');
        $project = $this->createProject($ownerToken, 'Owner item project');

        $task = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$project['id'].'/tasks', $ownerToken, ['title' => 'Private task'])->getContent(),
            true,
        );

        $this->createUser('task-item-other@example.com', 'password123');
        $otherToken = $this->getOAuth2Token('task-item-other@example.com', 'password123');

        $response = $this->apiRequest('GET', '/api/projects/'.$project['id'].'/tasks/'.$task['id'], $otherToken);

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function get_task_item_requires_authentication(): void
    {
        $this->createUser('task-item-unauth@example.com', 'password123');
        $token = $this->getOAuth2Token('task-item-unauth@example.com', 'password123');
        $project = $this->createProject($token, 'Unauth item project');

        $task = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$project['id'].'/tasks', $token, ['title' => 'Unauth task'])->getContent(),
            true,
        );

        $response = $this->apiRequest('GET', '/api/projects/'.$project['id'].'/tasks/'.$task['id'], null);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** @return array<string, mixed> */
    private function createProject(string $token, string $name): array
    {
        return json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => $name])->getContent(),
            true,
        );
    }
}
