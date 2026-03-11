<?php

declare(strict_types=1);

namespace App\Tests\E2E\Project;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class DeleteProjectTest extends AbstractApiTestCase
{

    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('project')]
    public function delete_project_success(): void
    {
        $this->createUser('project-delete@example.com', 'password123');
        $token = $this->getOAuth2Token('project-delete@example.com', 'password123');

        $created = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'Project to delete'])->getContent(),
            true,
        );

        $response = $this->apiRequest('DELETE', '/api/projects/' . $created['id'], $token);
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $response = $this->apiRequest('GET', '/api/projects/' . $created['id'], $token);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function delete_project_not_found(): void
    {
        $this->createUser('project-delete-404@example.com', 'password123');
        $token = $this->getOAuth2Token('project-delete-404@example.com', 'password123');

        $response = $this->apiRequest('DELETE', '/api/projects/00000000-0000-0000-0000-000000000000', $token);

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function delete_project_requires_authentication(): void
    {
        $response = $this->apiRequest('DELETE', '/api/projects/00000000-0000-0000-0000-000000000000', null);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
