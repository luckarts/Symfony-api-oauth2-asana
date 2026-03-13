<?php

declare(strict_types=1);

namespace App\Tests\E2E\Task;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class CreateTaskTest extends AbstractApiTestCase
{
    // -------------------------------------------------------------------------
    // Task (root)
    // -------------------------------------------------------------------------

    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('task')]
    public function createTaskSuccess(): void
    {
        $token = $this->auth('task-create@example.com');
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
    public function createTaskWithOrderIndex(): void
    {
        $token = $this->auth('task-order@example.com');
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
    public function createTaskFailsWithEmptyTitle(): void
    {
        $token = $this->auth('task-empty@example.com');
        $project = $this->createProject($token, 'Validation project');

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/tasks', $token, [
            'title' => '',
        ]);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function createTaskProjectNotFound(): void
    {
        $token = $this->auth('task-404@example.com');

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
    public function createTaskForbiddenForNonOwner(): void
    {
        $ownerToken = $this->auth('task-owner@example.com');
        $project = $this->createProject($ownerToken, 'Owner project');

        $otherToken = $this->auth('task-other@example.com');

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/tasks', $otherToken, [
            'title' => 'Stolen task',
        ]);

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function createTaskRequiresAuthentication(): void
    {
        $token = $this->auth('task-unauth@example.com');
        $project = $this->createProject($token, 'Auth project');

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/tasks', null, [
            'title' => 'Unauthenticated task',
        ]);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Subtask (task with parentTaskId)
    // -------------------------------------------------------------------------

    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('task')]
    public function createSubtaskSuccess(): void
    {
        $token = $this->auth('subtask-create@example.com');
        $project = $this->createProject($token, 'Subtask project');
        $parent = $this->createTask($token, $project['id'], 'Parent task');

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/tasks', $token, [
            'title' => 'Child task',
            'parentTaskId' => $parent['id'],
        ]);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $child = json_decode($response->getContent(), true);
        $this->assertSame('Child task', $child['title']);
        $this->assertSame($parent['id'], $child['parentTaskId']);
        $this->assertSame([], $child['subtasks']);

        // Parent now has 1 subtask embedded
        $parentGet = $this->apiRequest('GET', '/api/projects/'.$project['id'].'/tasks/'.$parent['id'], $token);
        $parentData = json_decode($parentGet->getContent(), true);
        $this->assertCount(1, $parentData['subtasks']);
        $this->assertSame($child['id'], $parentData['subtasks'][0]['id']);
        $this->assertSame('Child task', $parentData['subtasks'][0]['title']);
        $this->assertSame('todo', $parentData['subtasks'][0]['status']);

        // Verify child persistence via GET item
        $childGet = $this->apiRequest('GET', '/api/projects/'.$project['id'].'/tasks/'.$child['id'], $token);
        $this->assertSame(Response::HTTP_OK, $childGet->getStatusCode());
        $childData = json_decode($childGet->getContent(), true);
        $this->assertSame($child['id'], $childData['id']);
        $this->assertSame('Child task', $childData['title']);
        $this->assertSame([], $childData['subtasks']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function createSubtaskParentNotFoundReturns404(): void
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
    public function createSubtaskParentCrossProjectReturns404(): void
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
    public function createSubtaskOfSubtaskReturns422(): void
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
