<?php

declare(strict_types=1);

namespace App\Tests\E2E\Task;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class UpdateDeleteTaskTest extends AbstractApiTestCase
{
    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('task')]
    public function update_task_success(): void
    {
        $this->createUser('task-update@example.com', 'password123');
        $token = $this->getOAuth2Token('task-update@example.com', 'password123');

        $created = json_decode(
            $this->apiRequest('POST', '/api/tasks', $token, ['title' => 'Original title'])->getContent(),
            true,
        );

        $response = $this->apiRequest('PUT', '/api/tasks/' . $created['id'], $token, [
            'title' => 'Updated title',
            'status' => 'in_progress',
        ]);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertSame('Updated title', $data['title']);
        $this->assertSame('in_progress', $data['status']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function update_task_fails_with_empty_title(): void
    {
        $this->createUser('task-update-empty@example.com', 'password123');
        $token = $this->getOAuth2Token('task-update-empty@example.com', 'password123');

        $created = json_decode(
            $this->apiRequest('POST', '/api/tasks', $token, ['title' => 'Task for empty update'])->getContent(),
            true,
        );

        $response = $this->apiRequest('PUT', '/api/tasks/' . $created['id'], $token, [
            'title' => '',
        ]);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('task')]
    public function delete_task_success(): void
    {
        $this->createUser('task-delete@example.com', 'password123');
        $token = $this->getOAuth2Token('task-delete@example.com', 'password123');

        $created = json_decode(
            $this->apiRequest('POST', '/api/tasks', $token, ['title' => 'Task to delete'])->getContent(),
            true,
        );

        $response = $this->apiRequest('DELETE', '/api/tasks/' . $created['id'], $token);
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $response = $this->apiRequest('GET', '/api/tasks/' . $created['id'], $token);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }
}
