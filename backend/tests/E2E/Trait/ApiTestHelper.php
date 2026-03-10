<?php

declare(strict_types=1);

namespace App\Tests\E2E\Trait;

use App\Auth\Infrastructure\OAuth2\TestClientManagerSetup;
use App\User\Application\Service\UserRegistrationService;
use App\User\Domain\Entity\User;
use App\User\Infrastructure\ApiPlatform\Resource\RegisterUserRequest;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

trait ApiTestHelper
{
    private KernelBrowser $client;

    protected function createUser(
        string $email = 'test@example.com',
        string $password = 'password123',
        string $firstName = 'John',
        string $lastName = 'Doe',
    ): User {
        /** @var UserRegistrationService $registrationService */
        $registrationService = static::getContainer()->get(UserRegistrationService::class);

        $request = new RegisterUserRequest();
        $request->email = $email;
        $request->password = $password;
        $request->firstName = $firstName;
        $request->lastName = $lastName;

        return $registrationService->register($request);
    }

    protected function getOAuth2Token(string $email, string $password): string
    {
        $this->client->request('POST', '/api/auth/token', [
            'grant_type' => 'password',
            'client_id' => TestClientManagerSetup::CLIENT_ID,
            'client_secret' => TestClientManagerSetup::CLIENT_SECRET,
            'username' => $email,
            'password' => $password,
        ]);

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        return $data['access_token'];
    }

    protected function apiRequest(
        string $method,
        string $uri,
        ?string $token = null,
        array $data = [],
    ): Response {
        $headers = ['CONTENT_TYPE' => 'application/ld+json'];

        if ($token !== null) {
            $headers['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        }

        $body = $data !== [] ? json_encode($data) : null;

        $this->client->request($method, $uri, [], [], $headers, $body);

        return $this->client->getResponse();
    }
}
