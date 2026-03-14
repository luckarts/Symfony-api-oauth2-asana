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

     /** @return array<string, mixed> */
    private function createMilestone(string $token, string $projectId, string $title = 'Sprint 1', ?string $dueDate = '2026-07-01T00:00:00+00:00'): array
    {
        $body = ['title' => $title];
        if (null !== $dueDate) {
            $body['dueDate'] = $dueDate;
        }

        return json_decode(
            $this->apiRequest('POST', '/api/projects/'.$projectId.'/milestones', $token, $body)->getContent(),
            true,
        );
    }
    
     // ─── GET collection ───────────────────────────────────────────────────────

    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('milestone')]
    public function getMilestonesReturnsEmptyCollection(): void
    {
        $this->createUser('ms-get@example.com', 'password123');
        $token = $this->getOAuth2Token('ms-get@example.com', 'password123');

        $project = $this->createProject($token, 'Milestone project');

        $response = $this->apiRequest('GET', '/api/projects/'.$project['id'].'/milestones', $token);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data['member']);
        $this->assertCount(0, $data['member']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('milestone')]
    public function getMilestoneCollectionProjectNotFound(): void
    {
        $this->createUser('ms-get-pnf@example.com', 'password123');
        $token = $this->getOAuth2Token('ms-get-pnf@example.com', 'password123');

        $response = $this->apiRequest(
            'GET',
            '/api/projects/00000000-0000-0000-0000-000000000000/milestones',
            $token,
        );

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('milestone')]
    public function getMilestonesForbiddenForNonOwner(): void
    {
        $this->createUser('ms-owner@example.com', 'password123');
        $ownerToken = $this->getOAuth2Token('ms-owner@example.com', 'password123');

        $project = $this->createProject($ownerToken, 'Owner milestone project');

        $this->createUser('ms-other@example.com', 'password123');
        $otherToken = $this->getOAuth2Token('ms-other@example.com', 'password123');

        $response = $this->apiRequest('GET', '/api/projects/'.$project['id'].'/milestones', $otherToken);

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    // ─── GET item ─────────────────────────────────────────────────────────────

    #[Test]
    #[Group('e2e')]
    #[Group('milestone')]
    public function getMilestoneItemNotFound(): void
    {
        $this->createUser('ms-get-404@example.com', 'password123');
        $token = $this->getOAuth2Token('ms-get-404@example.com', 'password123');

        $project = $this->createProject($token, 'Milestone project 404');

        $response = $this->apiRequest(
            'GET',
            '/api/projects/'.$project['id'].'/milestones/00000000-0000-0000-0000-000000000000',
            $token,
        );

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
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

    // ─── DELETE ───────────────────────────────────────────────────────────────

    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('milestone')]
    public function deleteMilestoneReturns204(): void
    {
        $this->createUser('ms-del@example.com', 'password123');
        $token = $this->getOAuth2Token('ms-del@example.com', 'password123');

        $project = $this->createProject($token, 'Delete MS project');
        $milestone = $this->createMilestone($token, $project['id'], 'To delete', null);

        $response = $this->apiRequest(
            'DELETE',
            '/api/projects/'.$project['id'].'/milestones/'.$milestone['id'],
            $token,
        );

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $getResponse = $this->apiRequest(
            'GET',
            '/api/projects/'.$project['id'].'/milestones/'.$milestone['id'],
            $token,
        );
        $this->assertSame(Response::HTTP_NOT_FOUND, $getResponse->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('milestone')]
    public function deleteMilestoneNotFoundReturns404(): void
    {
        $this->createUser('ms-del-404@example.com', 'password123');
        $token = $this->getOAuth2Token('ms-del-404@example.com', 'password123');

        $project = $this->createProject($token, 'Del 404 MS project');

        $response = $this->apiRequest(
            'DELETE',
            '/api/projects/'.$project['id'].'/milestones/00000000-0000-0000-0000-000000000000',
            $token,
        );

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('milestone')]
    public function deleteMilestoneForbiddenForNonOwner(): void
    {
        $this->createUser('ms-del-owner@example.com', 'password123');
        $ownerToken = $this->getOAuth2Token('ms-del-owner@example.com', 'password123');

        $project = $this->createProject($ownerToken, 'Del forbidden MS');
        $milestone = $this->createMilestone($ownerToken, $project['id'], 'To delete', null);

        $this->createUser('ms-del-other@example.com', 'password123');
        $otherToken = $this->getOAuth2Token('ms-del-other@example.com', 'password123');

        $response = $this->apiRequest(
            'DELETE',
            '/api/projects/'.$project['id'].'/milestones/'.$milestone['id'],
            $otherToken,
        );

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }
}
