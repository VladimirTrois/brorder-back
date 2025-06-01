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
    protected JWTTokenManagerInterface $jwtManager;

    public function setUp(): void
    {
        self::bootKernel();

        $this->jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);

        $user = UserFactory::createOne([
            'username' => self::USERNAME,
            'password' => self::PASSWORD,
            'roles' => ['ROLE_ADMIN'],
        ]);

        $this->token = $this->jwtManager->create($user);
    }

    protected function createClientWithCredentials($token = null): Client
    {
        return static::createClient([], [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);
    }
}
