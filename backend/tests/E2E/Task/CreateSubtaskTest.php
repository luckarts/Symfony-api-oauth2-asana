<?php

declare(strict_types=1);

namespace App\Tests\E2E\Task;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class CreateSubtaskTest extends AbstractApiTestCase
{
    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('task')]
    public function create_subtask_success(): void
    {
        $token = $this->auth('subtask-create@example.com');
        $project = $this->createProject($token, 'Subtask project');
        $parent = $this->createTask($token, $project['id'], 'Parent task');

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/tasks', $token, [
            'title' => 'Child task',
            'parentTaskId' => $parent['id'],
        ]);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertSame('Child task', $data['title']);
        $this->assertSame($parent['id'], $data['parentTaskId']);
        $this->assertSame([], $data['subtasks']);

        // Parent now has 1 subtask embedded
        $get = $this->apiRequest('GET', '/api/projects/'.$project['id'].'/tasks/'.$parent['id'], $token);
        $parentData = json_decode($get->getContent(), true);
        $this->assertCount(1, $parentData['subtasks']);
        $this->assertSame($data['id'], $parentData['subtasks'][0]['id']);
        $this->assertSame('Child task', $parentData['subtasks'][0]['title']);
        $this->assertSame('todo', $parentData['subtasks'][0]['status']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function create_subtask_parent_not_found_returns_404(): void
    {
        $token = $this->auth('subtask-parent404@example.com');
        $project = $this->createProject($token, 'Subtask 404 project');

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/tasks', $token, [
            'title' => 'Orphan',
            'parentTaskId' => '00000000-0000-0000-0000-000000000000',
        ]);

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function create_subtask_parent_cross_project_returns_404(): void
    {
        $token = $this->auth('subtask-crossproj@example.com');
        $projectA = $this->createProject($token, 'Subtask proj A');
        $projectB = $this->createProject($token, 'Subtask proj B');
        $parentInB = $this->createTask($token, $projectB['id'], 'Parent in B');

        $response = $this->apiRequest('POST', '/api/projects/'.$projectA['id'].'/tasks', $token, [
            'title' => 'Child in A with parent from B',
            'parentTaskId' => $parentInB['id'],
        ]);

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function create_subtask_of_subtask_returns_422(): void
    {
        $token = $this->auth('subtask-depth@example.com');
        $project = $this->createProject($token, 'Depth project');
        $parent = $this->createTask($token, $project['id'], 'Root task');
        $child = $this->createTask($token, $project['id'], 'Child task', ['parentTaskId' => $parent['id']]);

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/tasks', $token, [
            'title' => 'Grandchild task',
            'parentTaskId' => $child['id'],
        ]);

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
