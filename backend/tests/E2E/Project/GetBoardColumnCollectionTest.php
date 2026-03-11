<?php

declare(strict_types=1);

namespace App\Tests\E2E\Project;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class GetBoardColumnCollectionTest extends AbstractApiTestCase
{
    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('project')]
    public function get_board_columns_returns_empty_collection(): void
    {
        $this->createUser('col-get@example.com', 'password123');
        $token = $this->getOAuth2Token('col-get@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'My project'])->getContent(),
            true,
        );

        $response = $this->apiRequest('GET', '/api/projects/'.$project['id'].'/columns', $token);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data['member']);
        $this->assertCount(0, $data['member']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function get_board_columns_project_not_found(): void
    {
        $this->createUser('col-get-404@example.com', 'password123');
        $token = $this->getOAuth2Token('col-get-404@example.com', 'password123');

        $response = $this->apiRequest('GET', '/api/projects/00000000-0000-0000-0000-000000000000/columns', $token);

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function get_board_columns_forbidden_for_non_owner(): void
    {
        $this->createUser('col-owner@example.com', 'password123');
        $ownerToken = $this->getOAuth2Token('col-owner@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $ownerToken, ['name' => 'Owner project'])->getContent(),
            true,
        );

        $this->createUser('col-other@example.com', 'password123');
        $otherToken = $this->getOAuth2Token('col-other@example.com', 'password123');

        $response = $this->apiRequest('GET', '/api/projects/'.$project['id'].'/columns', $otherToken);

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function get_board_columns_requires_authentication(): void
    {
        $this->createUser('col-get-unauth@example.com', 'password123');
        $token = $this->getOAuth2Token('col-get-unauth@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'Auth project'])->getContent(),
            true,
        );

        $response = $this->apiRequest('GET', '/api/projects/'.$project['id'].'/columns', null);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
