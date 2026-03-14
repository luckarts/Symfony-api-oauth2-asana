<?php

declare(strict_types=1);

namespace App\Tests\E2E\Task;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class UpdateTaskTest extends AbstractApiTestCase
{
    // -------------------------------------------------------------------------
    // PATCH /api/projects/{projectId}/tasks/{id}
    // -------------------------------------------------------------------------

    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('task')]
    public function patchTaskUpdatesTitle(): void
    {
        $token = $this->auth('task-patch-title@example.com');
        $project = $this->createProject($token, 'Patch title project');
        $task = $this->createTask($token, $project['id'], 'Original title');

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$project['id'].'/tasks/'.$task['id'],
            $token,
            ['title' => 'Updated title'],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('Updated title', $data['title']);
        $this->assertSame($task['id'], $data['id']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function patchTaskAssignsColumn(): void
    {
        $token = $this->auth('task-patch-col@example.com');
        $project = $this->createProject($token, 'Patch column project');
        $col = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$project['id'].'/columns', $token, ['title' => 'Todo'])->getContent(),
            true,
        );
        $task = $this->createTask($token, $project['id'], 'Task to assign');

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$project['id'].'/tasks/'.$task['id'],
            $token,
            ['columnId' => $col['id']],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($col['id'], json_decode($response->getContent(), true)['columnId']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function patchTaskRemovesColumn(): void
    {
        $token = $this->auth('task-patch-rmcol@example.com');
        $project = $this->createProject($token, 'Patch remove col project');
        $col = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$project['id'].'/columns', $token, ['title' => 'Todo'])->getContent(),
            true,
        );
        $task = $this->createTask($token, $project['id'], 'Task with column', ['columnId' => $col['id']]);

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$project['id'].'/tasks/'.$task['id'],
            $token,
            ['columnId' => null],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertNull(json_decode($response->getContent(), true)['columnId']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function patchTaskMarksAsCompleted(): void
    {
        $token = $this->auth('task-patch-done@example.com');
        $project = $this->createProject($token, 'Patch done project');
        $task = $this->createTask($token, $project['id'], 'Task to complete');

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$project['id'].'/tasks/'.$task['id'],
            $token,
            ['isCompleted' => true],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertTrue(json_decode($response->getContent(), true)['isCompleted']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function patchTaskSetsDueDate(): void
    {
        $token = $this->auth('task-patch-due@example.com');
        $project = $this->createProject($token, 'Patch due project');
        $task = $this->createTask($token, $project['id'], 'Task with due date');

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$project['id'].'/tasks/'.$task['id'],
            $token,
            ['dueDate' => '2026-12-31T23:59:59+00:00'],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertNotNull(json_decode($response->getContent(), true)['dueDate']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function patchTaskInvalidDueDateReturns422(): void
    {
        $token = $this->auth('task-patch-baddue@example.com');
        $project = $this->createProject($token, 'Patch bad due project');
        $task = $this->createTask($token, $project['id'], 'Task');

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$project['id'].'/tasks/'.$task['id'],
            $token,
            ['dueDate' => 'not-a-date'],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function patchTaskCrossProjectColumnReturns422(): void
    {
        $token = $this->auth('task-patch-crosscol@example.com');
        $projectA = $this->createProject($token, 'Cross col project A');
        $projectB = $this->createProject($token, 'Cross col project B');
        $colB = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$projectB['id'].'/columns', $token, ['title' => 'Col B'])->getContent(),
            true,
        );
        $task = $this->createTask($token, $projectA['id'], 'Task in A');

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$projectA['id'].'/tasks/'.$task['id'],
            $token,
            ['columnId' => $colB['id']],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function patchTaskNotFoundReturns404(): void
    {
        $token = $this->auth('task-patch-404@example.com');
        $project = $this->createProject($token, 'Patch 404 project');

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$project['id'].'/tasks/00000000-0000-0000-0000-000000000000',
            $token,
            ['title' => 'Ghost'],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function patchTaskWrongProjectReturns404(): void
    {
        $token = $this->auth('task-patch-wrongproj@example.com');
        $projectA = $this->createProject($token, 'Wrong proj A');
        $projectB = $this->createProject($token, 'Wrong proj B');
        $task = $this->createTask($token, $projectA['id'], 'Task in A');

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$projectB['id'].'/tasks/'.$task['id'],
            $token,
            ['title' => 'Hijack'],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function patchTaskForbiddenForNonOwner(): void
    {
        $ownerToken = $this->auth('task-patch-owner@example.com');
        $project = $this->createProject($ownerToken, 'Patch owner project');
        $task = $this->createTask($ownerToken, $project['id'], 'Owner task');

        $otherToken = $this->auth('task-patch-other@example.com');

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$project['id'].'/tasks/'.$task['id'],
            $otherToken,
            ['title' => 'Hijacked'],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function patchTaskRequiresAuthentication(): void
    {
        $token = $this->auth('task-patch-unauth@example.com');
        $project = $this->createProject($token, 'Patch unauth project');
        $task = $this->createTask($token, $project['id'], 'Task');

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$project['id'].'/tasks/'.$task['id'],
            null,
            ['title' => 'Unauthenticated'],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Subtask (parentTaskId)
    // -------------------------------------------------------------------------

    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('task')]
    public function patchSubtaskAssignsParent(): void
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
    public function patchSubtaskRemovesParent(): void
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
    public function patchSubtaskParentNotFoundReturns404(): void
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
    public function patchSubtaskParentCrossProjectReturns404(): void
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
    public function patchSubtaskParentIsSubtaskReturns422(): void
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
    // Milestone assignment (milestoneId)
    // -------------------------------------------------------------------------

    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('task')]
    public function patchTaskAssignsMilestone(): void
    {
        $token = $this->auth('task-patch-ms-assign@example.com');
        $project = $this->createProject($token, 'Milestone assign patch project');
        $milestone = $this->createMilestone($token, $project['id'], 'Sprint 1');
        $task = $this->createTask($token, $project['id'], 'Task without milestone');

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$project['id'].'/tasks/'.$task['id'],
            $token,
            ['milestoneId' => $milestone['id']],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame($milestone['id'], $data['milestoneId']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function patchTaskUnsetsMilestone(): void
    {
        $token = $this->auth('task-patch-ms-unset@example.com');
        $project = $this->createProject($token, 'Milestone unset patch project');
        $milestone = $this->createMilestone($token, $project['id'], 'Sprint 1');
        $task = $this->createTask($token, $project['id'], 'Task with milestone', ['milestoneId' => $milestone['id']]);

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$project['id'].'/tasks/'.$task['id'],
            $token,
            ['milestoneId' => null],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertNull(json_decode($response->getContent(), true)['milestoneId'] ?? null);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function patchTaskCrossProjectMilestoneReturns422(): void
    {
        $token = $this->auth('task-patch-ms-xproj@example.com');
        $projectA = $this->createProject($token, 'Milestone cross A');
        $projectB = $this->createProject($token, 'Milestone cross B');
        $milestoneB = $this->createMilestone($token, $projectB['id'], 'Sprint B');
        $task = $this->createTask($token, $projectA['id'], 'Task in A');

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$projectA['id'].'/tasks/'.$task['id'],
            $token,
            ['milestoneId' => $milestoneB['id']],
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

    /** @return array<string, mixed> */
    private function createMilestone(string $token, string $projectId, string $title): array
    {
        return json_decode(
            $this->apiRequest('POST', '/api/projects/'.$projectId.'/milestones', $token, ['title' => $title])->getContent(),
            true,
        );
    }
}
