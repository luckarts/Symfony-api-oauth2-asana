<?php

declare(strict_types=1);

namespace App\Tests\E2E\Project;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class CreateBoardColumnTest extends AbstractApiTestCase
{
    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('project')]
    public function create_board_column_success(): void
    {
        $this->createUser('col-create@example.com', 'password123');
        $token = $this->getOAuth2Token('col-create@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'My project'])->getContent(),
            true,
        );

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/columns', $token, [
            'title' => 'To Do',
        ]);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertSame('To Do', $data['title']);
        $this->assertSame(0, $data['position']);
        $this->assertFalse($data['isDefault']);
        $this->assertNotEmpty($data['id']);
        $this->assertSame($project['id'], $data['projectId']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function create_board_column_auto_increments_position(): void
    {
        $this->createUser('col-position@example.com', 'password123');
        $token = $this->getOAuth2Token('col-position@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'Position project'])->getContent(),
            true,
        );

        $this->apiRequest('POST', '/api/projects/'.$project['id'].'/columns', $token, ['title' => 'Col A']);
        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/columns', $token, ['title' => 'Col B']);

        $data = json_decode($response->getContent(), true);
        $this->assertSame(1, $data['position']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function create_board_column_fails_with_empty_title(): void
    {
        $this->createUser('col-empty@example.com', 'password123');
        $token = $this->getOAuth2Token('col-empty@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'My project'])->getContent(),
            true,
        );

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/columns', $token, [
            'title' => '',
        ]);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function create_board_column_forbidden_for_non_owner(): void
    {
        $this->createUser('col-create-owner@example.com', 'password123');
        $ownerToken = $this->getOAuth2Token('col-create-owner@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $ownerToken, ['name' => 'Owner project'])->getContent(),
            true,
        );

        $this->createUser('col-create-other@example.com', 'password123');
        $otherToken = $this->getOAuth2Token('col-create-other@example.com', 'password123');

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/columns', $otherToken, [
            'title' => 'Intruder',
        ]);

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function create_board_column_requires_authentication(): void
    {
        $this->createUser('col-create-unauth@example.com', 'password123');
        $token = $this->getOAuth2Token('col-create-unauth@example.com', 'password123');

        $project = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'My project'])->getContent(),
            true,
        );

        $response = $this->apiRequest('POST', '/api/projects/'.$project['id'].'/columns', null, [
            'title' => 'Unauthenticated',
        ]);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
