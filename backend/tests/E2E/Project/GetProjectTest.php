<?php

declare(strict_types=1);

namespace App\Tests\E2E\Project;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class GetProjectTest extends AbstractApiTestCase
{
    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('project')]
    public function get_project_item_success(): void
    {
        $this->createUser('project-get@example.com', 'password123');
        $token = $this->getOAuth2Token('project-get@example.com', 'password123');

        $created = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'Project to get'])->getContent(),
            true,
        );

        $response = $this->apiRequest('GET', '/api/projects/' . $created['id'], $token);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertSame($created['id'], $data['id']);
        $this->assertSame('Project to get', $data['name']);
        $this->assertSame('active', $data['status']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function get_project_not_found(): void
    {
        $this->createUser('project-get-404@example.com', 'password123');
        $token = $this->getOAuth2Token('project-get-404@example.com', 'password123');

        $response = $this->apiRequest('GET', '/api/projects/00000000-0000-0000-0000-000000000000', $token);

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('project')]
    public function get_project_collection_success(): void
    {
        $this->createUser('project-list@example.com', 'password123');
        $token = $this->getOAuth2Token('project-list@example.com', 'password123');

        $this->apiRequest('POST', '/api/projects', $token, ['name' => 'Project A']);
        $this->apiRequest('POST', '/api/projects', $token, ['name' => 'Project B']);

        $response = $this->apiRequest('GET', '/api/projects', $token);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data['member']);
        $this->assertGreaterThanOrEqual(2, count($data['member']));
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function get_project_requires_authentication(): void
    {
        $response = $this->apiRequest('GET', '/api/projects/00000000-0000-0000-0000-000000000000', null);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
