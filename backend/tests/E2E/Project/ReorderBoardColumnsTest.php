<?php

declare(strict_types=1);

namespace App\Tests\E2E\Project;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class ReorderBoardColumnsTest extends AbstractApiTestCase
{
    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('project')]
    public function reorder_board_columns_success(): void
    {
        $this->createUser('col-reorder@example.com', 'password123');
        $token = $this->getOAuth2Token('col-reorder@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'Reorder project'])->getContent(),
            true,
        );

        $col1 = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$project['id'].'/columns', $token, ['title' => 'Col A'])->getContent(),
            true,
        );

        $col2 = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$project['id'].'/columns', $token, ['title' => 'Col B'])->getContent(),
            true,
        );

        $col3 = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$project['id'].'/columns', $token, ['title' => 'Col C'])->getContent(),
            true,
        );

        $newOrder = [$col3['id'], $col1['id'], $col2['id']];

        $response = $this->apiRequest(
            'POST',
            '/api/projects/'.$project['id'].'/columns/reorder',
            $token,
            ['order' => $newOrder],
        );

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode(), $response->getContent());

        $data = json_decode($response->getContent(), true);

        $this->assertIsArray($data);
        $members = $data['member'];
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
    public function reorder_with_foreign_column_id_returns_422(): void
    {
        $this->createUser('col-reorder-foreign@example.com', 'password123');
        $token = $this->getOAuth2Token('col-reorder-foreign@example.com', 'password123');

        $project1 = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'Project 1'])->getContent(),
            true,
        );

        $project2 = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'Project 2'])->getContent(),
            true,
        );

        $colP1 = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$project1['id'].'/columns', $token, ['title' => 'P1 Col'])->getContent(),
            true,
        );

        $colP2 = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$project2['id'].'/columns', $token, ['title' => 'P2 Col'])->getContent(),
            true,
        );

        // Try to reorder project1 using project2's column ID
        $response = $this->apiRequest(
            'POST',
            '/api/projects/'.$project1['id'].'/columns/reorder',
            $token,
            ['order' => [$colP2['id']]],
        );

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function reorder_with_incomplete_list_returns_422(): void
    {
        $this->createUser('col-reorder-incomplete@example.com', 'password123');
        $token = $this->getOAuth2Token('col-reorder-incomplete@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'Incomplete reorder'])->getContent(),
            true,
        );

        $col1 = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$project['id'].'/columns', $token, ['title' => 'Col A'])->getContent(),
            true,
        );

        $col2 = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$project['id'].'/columns', $token, ['title' => 'Col B'])->getContent(),
            true,
        );

        $this->apiRequest('POST', '/api/projects/'.$project['id'].'/columns', $token, ['title' => 'Col C']);

        // Send only 2 of the 3 column IDs
        $response = $this->apiRequest(
            'POST',
            '/api/projects/'.$project['id'].'/columns/reorder',
            $token,
            ['order' => [$col1['id'], $col2['id']]],
        );

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function reorder_board_columns_forbidden_for_non_owner(): void
    {
        $this->createUser('col-reorder-owner@example.com', 'password123');
        $ownerToken = $this->getOAuth2Token('col-reorder-owner@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $ownerToken, ['name' => 'Owner project'])->getContent(),
            true,
        );

        $col1 = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$project['id'].'/columns', $ownerToken, ['title' => 'Col A'])->getContent(),
            true,
        );

        $col2 = json_decode(
            $this->apiRequest('POST', '/api/projects/'.$project['id'].'/columns', $ownerToken, ['title' => 'Col B'])->getContent(),
            true,
        );

        $this->createUser('col-reorder-other@example.com', 'password123');
        $otherToken = $this->getOAuth2Token('col-reorder-other@example.com', 'password123');

        $response = $this->apiRequest(
            'POST',
            '/api/projects/'.$project['id'].'/columns/reorder',
            $otherToken,
            ['order' => [$col2['id'], $col1['id']]],
        );

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }
}
