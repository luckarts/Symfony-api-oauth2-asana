<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use App\Tests\E2E\Trait\ApiTestHelper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractApiTestCase extends WebTestCase
{
    use ApiTestHelper;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
    }
}
