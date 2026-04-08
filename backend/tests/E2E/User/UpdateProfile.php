<?php

declare(strict_types=1);

namespace App\Tests\E2E\User;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use App\Tests\E2E\AbstractApiTestCase;

class LoginUserTest extends AbstractApiTestCase
{
 
 public function update_profile_success(): void
    {
        $token = $this->authenticate('update@example.com', 'T3st!P@ss#Api42', 'John', 'Doe');
        

        $response = $this->apiRequest('PUT', '/api/user/profile', $token, [
            'firstName' => 'Updated',
            'lastName' => 'Name',
        ]);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertSame('Updated', $data['firstName']);
        $this->assertSame('Name', $data['lastName']);
        $this->assertSame('update@example.com', $data['email']);
    }

    
}