<?php

declare(strict_types=1);

namespace App\Tests\E2E\Task;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class UpdateSubtaskTest extends AbstractApiTestCase
{
    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('task')]
    public function patch_task_assigns_parent(): void
    {
        $token = $this->auth('subtask-patch-assign@example.com');
        $project = $this->createProject($token, 'Patch subtask project');
        $parent = $this->createTask($token, $project['id'], 'Parent task');
        $child = $this->createTask($token, $project['id'], 'Standalone task');

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$project['id'].'/tasks/'.$child['id'],
            $token,
            ['parentTaskId' => $parent['id']],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame($parent['id'], $data['parentTaskId']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function patch_task_removes_parent(): void
    {
        $token = $this->auth('subtask-patch-remove@example.com');
        $project = $this->createProject($token, 'Patch remove parent project');
        $parent = $this->createTask($token, $project['id'], 'Parent task');
        $child = $this->createTask($token, $project['id'], 'Child task', ['parentTaskId' => $parent['id']]);

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$project['id'].'/tasks/'.$child['id'],
            $token,
            ['parentTaskId' => null],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertNull(json_decode($response->getContent(), true)['parentTaskId']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function patch_task_parent_not_found_returns_404(): void
    {
        $token = $this->auth('subtask-patch-parent404@example.com');
        $project = $this->createProject($token, 'Patch parent 404 project');
        $task = $this->createTask($token, $project['id'], 'Task');

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$project['id'].'/tasks/'.$task['id'],
            $token,
            ['parentTaskId' => '00000000-0000-0000-0000-000000000000'],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function patch_task_parent_cross_project_returns_404(): void
    {
        $token = $this->auth('subtask-patch-crossproj@example.com');
        $projectA = $this->createProject($token, 'Patch cross A');
        $projectB = $this->createProject($token, 'Patch cross B');
        $parentInB = $this->createTask($token, $projectB['id'], 'Parent in B');
        $taskInA = $this->createTask($token, $projectA['id'], 'Task in A');

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$projectA['id'].'/tasks/'.$taskInA['id'],
            $token,
            ['parentTaskId' => $parentInB['id']],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function patch_task_parent_is_subtask_returns_422(): void
    {
        $token = $this->auth('subtask-patch-depth@example.com');
        $project = $this->createProject($token, 'Patch depth project');
        $root = $this->createTask($token, $project['id'], 'Root task');
        $child = $this->createTask($token, $project['id'], 'Child task', ['parentTaskId' => $root['id']]);
        $other = $this->createTask($token, $project['id'], 'Other task');

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$project['id'].'/tasks/'.$other['id'],
            $token,
            ['parentTaskId' => $child['id']],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
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

    /**
     * @param array<string, mixed> $extra
     *
     * @return array<string, mixed>
     */
    private function createTask(string $token, string $projectId, string $title, array $extra = []): array
    {
        return json_decode(
            $this->apiRequest('POST', '/api/projects/'.$projectId.'/tasks', $token, array_merge(['title' => $title], $extra))->getContent(),
            true,
        );
    }
}
