<?php
// api/tests/AbstractTest.php
namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Factory\UserFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class AbstractTest extends ApiTestCase
{
    const USERNAME = "usernameExample";
    const PASSWORD = "passwordExample";
    const URL_BASE = "http://localhost:8000/api";
    const URL_LOGIN = self::URL_BASE . "/login";

    use ResetDatabase, Factories;

    private ?string $token = null;

    protected function createClientWithCredentials(array $userData = []): Client
    {
        $user = UserFactory::createOne(array_merge([
            'username' => 'test_user',
            'password' => 'test_pass',
            'roles' => ['ROLE_USER'],
        ], $userData));

        $jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        return static::createClient([], [
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);
    }
}
