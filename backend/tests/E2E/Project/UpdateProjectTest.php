<?php

declare(strict_types=1);

namespace App\Tests\E2E\Project;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class UpdateProjectTest extends AbstractApiTestCase
{
    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('project')]
    public function update_project_success(): void
    {
        $this->createUser('project-update@example.com', 'password123');
        $token = $this->getOAuth2Token('project-update@example.com', 'password123');

        $created = json_decode(
            $this->apiRequest('POST', '/api/projects', $token, ['name' => 'Original name'])->getContent(),
            true,
        );

        $response = $this->apiRequest('PATCH', '/api/projects/' . $created['id'], $token, [
            'name' => 'Updated name',
            'status' => 'completed',
            'description' => 'Added description',
        ], 'application/merge-patch+json');

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertSame('Updated name', $data['name']);
        $this->assertSame('completed', $data['status']);
        $this->assertSame('Added description', $data['description']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function update_project_not_found(): void
    {
        $this->createUser('project-update-404@example.com', 'password123');
        $token = $this->getOAuth2Token('project-update-404@example.com', 'password123');

        $response = $this->apiRequest('PATCH', '/api/projects/00000000-0000-0000-0000-000000000000', $token, [
            'name' => 'Ghost project',
        ], 'application/merge-patch+json');

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[Group('e2e')]
    #[Group('project')]
    public function update_project_requires_authentication(): void
    {
        $response = $this->apiRequest('PATCH', '/api/projects/00000000-0000-0000-0000-000000000000', null, [
            'name' => 'Unauthenticated update',
        ], 'application/merge-patch+json');

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

}
