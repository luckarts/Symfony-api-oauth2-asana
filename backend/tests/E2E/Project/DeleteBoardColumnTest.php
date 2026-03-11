<?php

declare(strict_types=1);

namespace App\Tests\E2E\Project;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class DeleteBoardColumnTest extends AbstractApiTestCase
{
    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('project')]
    public function delete_board_column_success(): void
    {
        $this->createUser('col-delete@example.com', 'password123');
        $token = $this->getOAuth2Token('col-delete@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'My project'])->getContent(),
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

        $response = $this->apiRequest('DELETE', '/api/projects/'.$project['id'].'/columns/'.$col2['id'], $token);

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $list = json_decode(
            $this->apiRequest('GET', '/api/projects/'.$project['id'].'/columns', $token)->getContent(),
            true,
        );

        $ids = array_column($list['member'], 'id');
        $this->assertContains($col1['id'], $ids);
        $this->assertNotContains($col2['id'], $ids);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function delete_last_column_returns_422(): void
    {
        $this->createUser('col-delete-last@example.com', 'password123');
        $token = $this->getOAuth2Token('col-delete-last@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'Single col project'])->getContent(),
            true,
        );

        $list = json_decode(
            $this->apiRequest('GET', '/api/projects/'.$project['id'].'/columns', $token)->getContent(),
            true,
        );

        $onlyCol = $list['member'][0];

        $response = $this->apiRequest('DELETE', '/api/projects/'.$project['id'].'/columns/'.$onlyCol['id'], $token);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function delete_board_column_not_found_returns_404(): void
    {
        $this->createUser('col-delete-404@example.com', 'password123');
        $token = $this->getOAuth2Token('col-delete-404@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'My project'])->getContent(),
            true,
        );

        $response = $this->apiRequest(
            'DELETE',
            '/api/projects/'.$project['id'].'/columns/00000000-0000-0000-0000-000000000000',
            $token,
        );

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function delete_board_column_cross_project_returns_404(): void
    {
        $this->createUser('col-delete-cross@example.com', 'password123');
        $token = $this->getOAuth2Token('col-delete-cross@example.com', 'password123');

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
            'DELETE',
            '/api/projects/'.$project2['id'].'/columns/'.$col['id'],
            $token,
        );

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function delete_board_column_forbidden_for_non_owner(): void
    {
        $this->createUser('col-delete-owner@example.com', 'password123');
        $ownerToken = $this->getOAuth2Token('col-delete-owner@example.com', 'password123');

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

        $this->createUser('col-delete-other@example.com', 'password123');
        $otherToken = $this->getOAuth2Token('col-delete-other@example.com', 'password123');

        $response = $this->apiRequest(
            'DELETE',
            '/api/projects/'.$project['id'].'/columns/'.$col2['id'],
            $otherToken,
        );

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function delete_board_column_requires_authentication(): void
    {
        $this->createUser('col-delete-unauth@example.com', 'password123');
        $token = $this->getOAuth2Token('col-delete-unauth@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'My project'])->getContent(),
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

        $response = $this->apiRequest('DELETE', '/api/projects/'.$project['id'].'/columns/'.$col2['id'], null);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
