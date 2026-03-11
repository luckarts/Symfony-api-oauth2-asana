<?php

declare(strict_types=1);

namespace App\Tests\E2E\Task;

use App\Tests\E2E\AbstractApiTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class GetTaskTest extends AbstractApiTestCase
{
    #[Test]
    #[Group('smoke')]
    #[Group('e2e')]
    #[Group('task')]
    public function get_task_success(): void
    {
        $this->createUser('task-get@example.com', 'password123');
        $token = $this->getOAuth2Token('task-get@example.com', 'password123');

        $created = json_decode(
            $this->apiRequest('POST', '/api/tasks', $token, ['title' => 'Task to get'])->getContent(),
            true,
        );

        $response = $this->apiRequest('GET', '/api/tasks/' . $created['id'], $token);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertSame($created['id'], $data['id']);
        $this->assertSame('Task to get', $data['title']);
        $this->assertSame('todo', $data['status']);
    }

    #[Test]
    #[Group('e2e')]
    #[Group('task')]
    public function get_task_not_found(): void
    {
        $this->createUser('task-get-404@example.com', 'password123');
        $token = $this->getOAuth2Token('task-get-404@example.com', 'password123');

        $response = $this->apiRequest('GET', '/api/tasks/00000000-0000-0000-0000-000000000000', $token);

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }
}
