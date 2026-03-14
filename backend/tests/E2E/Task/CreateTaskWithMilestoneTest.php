<?php

declare(strict_types=1);

namespace App\Tests\E2E\Task;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class CreateTaskWithMilestoneTest extends AbstractApiTestCase
{
    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('task')]
    public function createTaskWithValidMilestoneIdReturns201(): void
    {
        $this->createUser('task-ms-create@example.com', 'password123');
        $token = $this->getOAuth2Token('task-ms-create@example.com', 'password123');
        $project = $this->createProject($token, 'Milestone assign project');
        $milestone = $this->createMilestone($token, $project['id'], 'Sprint 1');

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/tasks', $token, [
            'title' => 'Task in milestone',
            'milestoneId' => $milestone['id'],
        ]);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame($milestone['id'], $data['milestoneId']);
        $this->assertSame('Task in milestone', $data['title']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function createTaskWithMilestoneFromOtherProjectReturns422(): void
    {
        $this->createUser('task-ms-xproj@example.com', 'password123');
        $token = $this->getOAuth2Token('task-ms-xproj@example.com', 'password123');
        $projectA = $this->createProject($token, 'Project A');
        $projectB = $this->createProject($token, 'Project B');
        $milestoneB = $this->createMilestone($token, $projectB['id'], 'Sprint B');

        $response = $this->apiRequest('POST', '/api/projects/'.$projectA['id'].'/tasks', $token, [
            'title' => 'Task in A with milestone from B',
            'milestoneId' => $milestoneB['id'],
        ]);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function createTaskWithNonExistentMilestoneIdReturns422(): void
    {
        $this->createUser('task-ms-404@example.com', 'password123');
        $token = $this->getOAuth2Token('task-ms-404@example.com', 'password123');
        $project = $this->createProject($token, 'Milestone 404 project');

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/tasks', $token, [
            'title' => 'Task with ghost milestone',
            'milestoneId' => '00000000-0000-0000-0000-000000000000',
        ]);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
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
    private function createMilestone(string $token, string $projectId, string $title): array
    {
        return json_decode(
            $this->apiRequest('POST', '/api/projects/'.$projectId.'/milestones', $token, ['title' => $title])->getContent(),
            true,
        );
    }
}
