<?php

declare(strict_types=1);

namespace App\Tests\E2E\Task;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class TaskMilestoneReadTest extends AbstractApiTestCase
{
    // -------------------------------------------------------------------------
    // GET /api/projects/{projectId}/tasks/{id}
    // milestoneId field (read-only, always null for now)
    // -------------------------------------------------------------------------

    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('task')]
    public function task_item_exposes_milestone_id_as_null_by_default(): void
    {
        $this->createUser('task-ms-read@example.com', 'password123');
        $token = $this->getOAuth2Token('task-ms-read@example.com', 'password123');
        $project = $this->createProject($token, 'Milestone read project');

        $task = $this->createTask($token, $project['id'], 'Task without milestone');

        $response = $this->apiRequest('GET', '/api/projects/'.$project['id'].'/tasks/'.$task['id'], $token);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        // null fields are omitted by API Platform — absent key == null milestone
        $this->assertNull($data['milestoneId'] ?? null);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function task_collection_exposes_milestone_id_field(): void
    {
        $this->createUser('task-ms-col@example.com', 'password123');
        $token = $this->getOAuth2Token('task-ms-col@example.com', 'password123');
        $project = $this->createProject($token, 'Milestone col project');

        $this->createTask($token, $project['id'], 'Task col A');
        $this->createTask($token, $project['id'], 'Task col B');

        $response = $this->apiRequest('GET', '/api/projects/'.$project['id'].'/tasks', $token);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertCount(2, $data['member']);
        foreach ($data['member'] as $item) {
            // null fields are omitted by API Platform — absent key == null milestone
            $this->assertNull($item['milestoneId'] ?? null);
        }
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
