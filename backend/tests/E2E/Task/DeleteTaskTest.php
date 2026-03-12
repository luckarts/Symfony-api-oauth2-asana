<?php

declare(strict_types=1);

namespace App\Tests\E2E\Task;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class DeleteTaskTest extends AbstractApiTestCase
{
    // -------------------------------------------------------------------------
    // DELETE /api/projects/{projectId}/tasks/{id}
    // -------------------------------------------------------------------------

    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('task')]
    public function delete_task_success(): void
    {
        $token = $this->auth('task-delete@example.com');
        $project = $this->createProject($token, 'Delete project');
        $task = $this->createTask($token, $project['id'], 'Task to delete');

        $response = $this->apiRequest('DELETE', '/api/projects/'.$project['id'].'/tasks/'.$task['id'], $token);

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        // Verify task is gone
        $get = $this->apiRequest('GET', '/api/projects/'.$project['id'].'/tasks/'.$task['id'], $token);
        $this->assertSame(Response::HTTP_NOT_FOUND, $get->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function delete_task_not_found_returns_404(): void
    {
        $token = $this->auth('task-delete-404@example.com');
        $project = $this->createProject($token, 'Delete 404 project');

        $response = $this->apiRequest(
            'DELETE',
            '/api/projects/'.$project['id'].'/tasks/00000000-0000-0000-0000-000000000000',
            $token,
        );

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function delete_task_wrong_project_returns_404(): void
    {
        $token = $this->auth('task-delete-wrongproj@example.com');
        $projectA = $this->createProject($token, 'Delete wrong proj A');
        $projectB = $this->createProject($token, 'Delete wrong proj B');
        $task = $this->createTask($token, $projectA['id'], 'Task in A');

        $response = $this->apiRequest('DELETE', '/api/projects/'.$projectB['id'].'/tasks/'.$task['id'], $token);

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function delete_task_forbidden_for_non_owner(): void
    {
        $ownerToken = $this->auth('task-delete-owner@example.com');
        $project = $this->createProject($ownerToken, 'Delete owner project');
        $task = $this->createTask($ownerToken, $project['id'], 'Owner task');

        $otherToken = $this->auth('task-delete-other@example.com');

        $response = $this->apiRequest('DELETE', '/api/projects/'.$project['id'].'/tasks/'.$task['id'], $otherToken);

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function delete_task_requires_authentication(): void
    {
        $token = $this->auth('task-delete-unauth@example.com');
        $project = $this->createProject($token, 'Delete unauth project');
        $task = $this->createTask($token, $project['id'], 'Task');

        $response = $this->apiRequest('DELETE', '/api/projects/'.$project['id'].'/tasks/'.$task['id'], null);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function auth(string $email): string
    {
        $this->createUser($email, 'password123');

        return $this->getOAuth2Token($email, 'password123');
    }

    /** @return array<string, mixed> */
    private function createProject(string $token, string $name): array
    {
        return json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => $name])->getContent(),
            true,
        );
    }

    /** @return array<string, mixed> */
    private function createTask(string $token, string $projectId, string $title): array
    {
        return json_decode(
            $this->apiRequest('POST', '/api/projects/'.$projectId.'/tasks', $token, ['title' => $title])->getContent(),
            true,
        );
    }
}
