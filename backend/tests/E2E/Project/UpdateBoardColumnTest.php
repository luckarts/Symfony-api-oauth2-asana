<?php

declare(strict_types=1);

namespace App\Tests\E2E\Project;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class UpdateBoardColumnTest extends AbstractApiTestCase
{
    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('project')]
    public function patch_board_column_updates_title(): void
    {
        $this->createUser('col-patch@example.com', 'password123');
        $token = $this->getOAuth2Token('col-patch@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'My project'])->getContent(),
            true,
        );

        $column = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$project['id'].'/columns', $token, ['title' => 'Todo'])->getContent(),
            true,
        );

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$project['id'].'/columns/'.$column['id'],
            $token,
            ['title' => 'In Progress'],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertSame('In Progress', $data['title']);
        $this->assertSame($column['id'], $data['id']);
        $this->assertSame($project['id'], $data['projectId']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function patch_board_column_sets_default_and_unsets_others(): void
    {
        $this->createUser('col-patch-default@example.com', 'password123');
        $token = $this->getOAuth2Token('col-patch-default@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'Default project'])->getContent(),
            true,
        );

        $col1 = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$project['id'].'/columns', $token, [
                'title' => 'Col A',
                'isDefault' => true,
            ])->getContent(),
            true,
        );

        $col2 = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$project['id'].'/columns', $token, ['title' => 'Col B'])->getContent(),
            true,
        );

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$project['id'].'/columns/'.$col2['id'],
            $token,
            ['isDefault' => true],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['isDefault']);

        $col1Updated = json_decode(
            $this->apiRequest('GET', '/api/projects/'.$project['id'].'/columns', $token)->getContent(),
            true,
        );

        $members = $col1Updated['member'];
        $col1Data = array_values(array_filter($members, static fn (array $c) => $c['id'] === $col1['id']))[0];
        $this->assertFalse($col1Data['isDefault']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function patch_board_column_not_found_returns_404(): void
    {
        $this->createUser('col-patch-404@example.com', 'password123');
        $token = $this->getOAuth2Token('col-patch-404@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'My project'])->getContent(),
            true,
        );

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$project['id'].'/columns/00000000-0000-0000-0000-000000000000',
            $token,
            ['title' => 'Ghost'],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function patch_board_column_cross_project_returns_404(): void
    {
        $this->createUser('col-patch-cross@example.com', 'password123');
        $token = $this->getOAuth2Token('col-patch-cross@example.com', 'password123');

        $project1 = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'Project 1'])->getContent(),
            true,
        );

        $project2 = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'Project 2'])->getContent(),
            true,
        );

        $col = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$project1['id'].'/columns', $token, ['title' => 'Col'])->getContent(),
            true,
        );

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$project2['id'].'/columns/'.$col['id'],
            $token,
            ['title' => 'Hijack'],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function patch_board_column_forbidden_for_non_owner(): void
    {
        $this->createUser('col-patch-owner@example.com', 'password123');
        $ownerToken = $this->getOAuth2Token('col-patch-owner@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $ownerToken, ['name' => 'Owner project'])->getContent(),
            true,
        );

        $column = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$project['id'].'/columns', $ownerToken, ['title' => 'Col'])->getContent(),
            true,
        );

        $this->createUser('col-patch-other@example.com', 'password123');
        $otherToken = $this->getOAuth2Token('col-patch-other@example.com', 'password123');

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$project['id'].'/columns/'.$column['id'],
            $otherToken,
            ['title' => 'Hijack'],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function patch_board_column_requires_authentication(): void
    {
        $this->createUser('col-patch-unauth@example.com', 'password123');
        $token = $this->getOAuth2Token('col-patch-unauth@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'My project'])->getContent(),
            true,
        );

        $column = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$project['id'].'/columns', $token, ['title' => 'Col'])->getContent(),
            true,
        );

        $response = $this->apiRequest(
            'PATCH',
            '/api/projects/'.$project['id'].'/columns/'.$column['id'],
            null,
            ['title' => 'Unauthenticated'],
            'application/merge-patch+json',
        );

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
