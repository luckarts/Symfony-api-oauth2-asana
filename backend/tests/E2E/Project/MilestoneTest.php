<?php

declare(strict_types=1);

namespace App\Tests\E2E\Project;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class MilestoneTest extends AbstractApiTestCase
{
    /** @return array<string, mixed> */
    private function createProject(string $token, string $name = 'MS project'): array
    {
        return json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => $name])->getContent(),
            true,
        );
    }

    // ─── POST ─────────────────────────────────────────────────────────────────

    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('milestone')]
    public function createMilestoneReturns201WithBody(): void
    {
        $this->createUser('ms-create@example.com', 'password123');
        $token = $this->getOAuth2Token('ms-create@example.com', 'password123');

        $project = $this->createProject($token, 'Create MS project');

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/milestones', $token, [
            'title' => 'v1.0 Release',
            'status' => 'in_progress',
            'dueDate' => '2026-06-30T00:00:00+00:00',
        ]);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertSame('v1.0 Release', $data['title']);
        $this->assertSame('in_progress', $data['status']);
        $this->assertNotEmpty($data['id']);
        $this->assertSame($project['id'], $data['projectId']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('milestone')]
    public function createMilestoneWithEmptyTitleReturns422(): void
    {
        $this->createUser('ms-create-422@example.com', 'password123');
        $token = $this->getOAuth2Token('ms-create-422@example.com', 'password123');

        $project = $this->createProject($token, 'Create MS 422');

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/milestones', $token, [
            'title' => '',
        ]);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('milestone')]
    public function createMilestoneProjectNotFoundReturns404(): void
    {
        $this->createUser('ms-create-pnf@example.com', 'password123');
        $token = $this->getOAuth2Token('ms-create-pnf@example.com', 'password123');

        $response = $this->apiRequest(
            'POST',
            '/api/projects/00000000-0000-0000-0000-000000000000/milestones',
            $token,
            ['title' => 'Ghost milestone'],
        );

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('milestone')]
    public function createMilestoneOtherOrgReturns403(): void
    {
        $this->createUser('ms-create-owner@example.com', 'password123');
        $ownerToken = $this->getOAuth2Token('ms-create-owner@example.com', 'password123');

        $project = $this->createProject($ownerToken, 'Owner MS project');

        $this->createUser('ms-create-other@example.com', 'password123');
        $otherToken = $this->getOAuth2Token('ms-create-other@example.com', 'password123');

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/milestones', $otherToken, [
            'title' => 'Unauthorized milestone',
        ]);

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('milestone')]
    public function createMilestoneWithInvalidDueDateReturns422(): void
    {
        $this->createUser('ms-create-date@example.com', 'password123');
        $token = $this->getOAuth2Token('ms-create-date@example.com', 'password123');

        $project = $this->createProject($token, 'Date MS project');

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/milestones', $token, [
            'title' => 'Bad date milestone',
            'dueDate' => 'not-a-date',
        ]);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

}
