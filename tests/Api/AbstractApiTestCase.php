<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Tests\Api\Factory\UserFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class AbstractApiTestCase extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

    protected static ?bool $alwaysBootKernel = false;

    protected function createJsonClient(): Client
    {
        return static::createClient([], [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    protected function createAuthenticatedClient(
        string $role = 'ROLE_ADMIN',
        string $email = 'admin@example.com',
    ): Client
    {
        $client = $this->createJsonClient();

        $user = UserFactory::createOne([
            'email' => $email,
            'roles' => [$role],
        ]);

        $client->loginUser($user);

        return $client;
    }
}
