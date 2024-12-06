<?php
// api/tests/AbstractTest.php
namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class AbstractTest extends ApiTestCase
{
    private ?string $token = null;

    const USERNAME = "usernameExample";
    const PASSWORD = "passwordExample";
    const URL_BASE = "https://localhost:4443";
    const URL_LOGIN = self::URL_BASE . "/login";

    use ResetDatabase, Factories;

    public function setUp(): void
    {
        UserFactory::createOne(
            [
                'username' => SELF::USERNAME,
                'password' => SELF::PASSWORD,
                'roles' => ["ROLE_ADMIN"],
            ]
        );

        self::bootKernel();
    }

    protected function createClientWithCredentials($token = null): Client
    {
        $token = $token ?: $this->getToken();

        return static::createClient([], ['headers' => ['authorization' => 'Bearer ' . $token]]);
    }

    /**
     * Use other credentials if needed.
     */
    protected function getToken($body = []): string
    {
        if ($this->token) {
            return $this->token;
        }

        $response = static::createClient()->request(
            'POST',
            self::URL_LOGIN,
            [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => $body ?: [
                    'username' => self::USERNAME,
                    'password' => self::PASSWORD,
                ],
            ]
        );

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data);
        $this->token = $data['token'];

        return $data['token'];
    }
}
