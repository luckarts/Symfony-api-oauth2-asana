<?php

declare(strict_types=1);

namespace App\Tests\E2E\Project;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class BoardColumnWorkflowTest extends AbstractApiTestCase
{
    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('project')]
    public function testCrudHappyPath(): void
    {
        $this->createUser('wf-crud@example.com', 'password123');
        $token = $this->getOAuth2Token('wf-crud@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'Workflow project'])->getContent(),
            true,
        );
        $projectId = $project['id'];

        // Create
        $createResponse = $this->apiRequest('POST', "/api/projects/{$projectId}/columns", $token, [
            'title' => 'Todo',
        ]);
        $this->assertSame(Response::HTTP_CREATED, $createResponse->getStatusCode());
        $column = json_decode($createResponse->getContent(), true);
        $colId = $column['id'];
        $this->assertSame('Todo', $column['title']);
        $this->assertSame(0, $column['position']);
        $this->assertFalse($column['isDefault']);

        // Read (GET collection)
        $listResponse = $this->apiRequest('GET', "/api/projects/{$projectId}/columns", $token);
        $this->assertSame(Response::HTTP_OK, $listResponse->getStatusCode());
        $list = json_decode($listResponse->getContent(), true);
        $this->assertArrayHasKey('member', $list);
        $this->assertContains($colId, array_column($list['member'], 'id'));

        // Update (PATCH)
        $patchResponse = $this->apiRequest(
            'PATCH',
            "/api/projects/{$projectId}/columns/{$colId}",
            $token,
            ['title' => 'In Progress'],
            'application/merge-patch+json',
        );
        $this->assertSame(Response::HTTP_OK, $patchResponse->getStatusCode());
        $updated = json_decode($patchResponse->getContent(), true);
        $this->assertSame('In Progress', $updated['title']);

        // Add a second column so we can delete the first
        $this->apiRequest('POST', "/api/projects/{$projectId}/columns", $token, ['title' => 'Done']);

        // Delete
        $deleteResponse = $this->apiRequest('DELETE', "/api/projects/{$projectId}/columns/{$colId}", $token);
        $this->assertSame(Response::HTTP_NO_CONTENT, $deleteResponse->getStatusCode());

        // Verify deleted
        $afterList = json_decode(
            $this->apiRequest('GET', "/api/projects/{$projectId}/columns", $token)->getContent(),
            true,
        );
        $this->assertNotContains($colId, array_column($afterList['member'], 'id'));
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function testReorder(): void
    {
        $this->createUser('wf-reorder@example.com', 'password123');
        $token = $this->getOAuth2Token('wf-reorder@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'Reorder workflow'])->getContent(),
            true,
        );
        $projectId = $project['id'];

        $col1 = json_decode(
            $this->apiRequest('POST', "/api/projects/{$projectId}/columns", $token, ['title' => 'A'])->getContent(),
            true,
        );
        $col2 = json_decode(
            $this->apiRequest('POST', "/api/projects/{$projectId}/columns", $token, ['title' => 'B'])->getContent(),
            true,
        );
        $col3 = json_decode(
            $this->apiRequest('POST', "/api/projects/{$projectId}/columns", $token, ['title' => 'C'])->getContent(),
            true,
        );

        // Reorder: C(0), A(1), B(2)
        $response = $this->apiRequest(
            'POST',
            "/api/projects/{$projectId}/columns/reorder",
            $token,
            ['order' => [$col3['id'], $col1['id'], $col2['id']]],
        );
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $members = json_decode($response->getContent(), true)['member'];
        $this->assertCount(3, $members);
        $this->assertSame($col3['id'], $members[0]['id']);
        $this->assertSame(0, $members[0]['position']);
        $this->assertSame($col1['id'], $members[1]['id']);
        $this->assertSame(1, $members[1]['position']);
        $this->assertSame($col2['id'], $members[2]['id']);
        $this->assertSame(2, $members[2]['position']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function testIsDefaultUniqueness(): void
    {
        $this->createUser('wf-default@example.com', 'password123');
        $token = $this->getOAuth2Token('wf-default@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'Default workflow'])->getContent(),
            true,
        );
        $projectId = $project['id'];

        $col1 = json_decode(
            $this->apiRequest('POST', "/api/projects/{$projectId}/columns", $token, [
                'title' => 'Col A',
                'isDefault' => true,
            ])->getContent(),
            true,
        );
        $col2 = json_decode(
            $this->apiRequest('POST', "/api/projects/{$projectId}/columns", $token, ['title' => 'Col B'])->getContent(),
            true,
        );

        // Set col2 as default — col1 should become false
        $this->apiRequest(
            'PATCH',
            "/api/projects/{$projectId}/columns/{$col2['id']}",
            $token,
            ['isDefault' => true],
            'application/merge-patch+json',
        );

        $members = json_decode(
            $this->apiRequest('GET', "/api/projects/{$projectId}/columns", $token)->getContent(),
            true,
        )['member'];

        $defaults = array_filter($members, static fn (array $c) => true === $c['isDefault']);
        $this->assertCount(1, $defaults, 'Only one column should be default');

        $defaultCol = array_values($defaults)[0];
        $this->assertSame($col2['id'], $defaultCol['id']);

        $col1Data = array_values(array_filter($members, static fn (array $c) => $c['id'] === $col1['id']))[0];
        $this->assertFalse($col1Data['isDefault']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function testCrossUserForbidden(): void
    {
        $this->createUser('wf-cross-a@example.com', 'password123');
        $tokenA = $this->getOAuth2Token('wf-cross-a@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $tokenA, ['name' => 'User A project'])->getContent(),
            true,
        );
        $projectId = $project['id'];

        $col = json_decode(
            $this->apiRequest('POST', "/api/projects/{$projectId}/columns", $tokenA, ['title' => 'Col A'])->getContent(),
            true,
        );

        $this->createUser('wf-cross-b@example.com', 'password123');
        $tokenB = $this->getOAuth2Token('wf-cross-b@example.com', 'password123');

        // User B cannot list columns
        $response = $this->apiRequest('GET', "/api/projects/{$projectId}/columns", $tokenB);
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());

        // User B cannot create a column
        $response = $this->apiRequest('POST', "/api/projects/{$projectId}/columns", $tokenB, ['title' => 'Intruder']);
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());

        // User B cannot update a column
        $response = $this->apiRequest(
            'PATCH',
            "/api/projects/{$projectId}/columns/{$col['id']}",
            $tokenB,
            ['title' => 'Hijack'],
            'application/merge-patch+json',
        );
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function testDeleteLastColumnFails(): void
    {
        $this->createUser('wf-dellast@example.com', 'password123');
        $token = $this->getOAuth2Token('wf-dellast@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'Last column project'])->getContent(),
            true,
        );
        $projectId = $project['id'];

        $col = json_decode(
            $this->apiRequest('POST', "/api/projects/{$projectId}/columns", $token, ['title' => 'Only Column'])->getContent(),
            true,
        );

        $response = $this->apiRequest('DELETE', "/api/projects/{$projectId}/columns/{$col['id']}", $token);
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        // Verify column still exists
        $list = json_decode(
            $this->apiRequest('GET', "/api/projects/{$projectId}/columns", $token)->getContent(),
            true,
        );
        $this->assertContains($col['id'], array_column($list['member'], 'id'));
    }
}
