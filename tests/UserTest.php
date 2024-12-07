<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;
use App\Factory\UserFactory;

class UserTest extends AbstractTest
{
    public const URL_USER = self::URL_BASE . "/api/users";

    public function testAdminLogin()
    {
        $response = static::createClientWithCredentials()->request('GET', self::URL_USER);
        $this->assertResponseIsSuccessful();
    }

    public function testGetCollection(): void
    {
        $response = static::createClientWithCredentials()->request('GET', self::URL_USER);
        $this->assertResponseIsSuccessful();
        $this->assertMatchesResourceCollectionJsonSchema(User::class);
    }

    public function testGET(): void
    {
        $user = UserFactory::createOne();
        $response = static::createClientWithCredentials()->request('GET', self::URL_USER . "/" . $user->getId());
        $this->assertResponseIsSuccessful();
        $this->assertMatchesResourceItemJsonSchema(User::class);
    }

    public function testPOST(): void
    {
        $response = static::createClientWithCredentials()->request('POST', self::URL_USER, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'username' => 'testUser',
                'roles' => ['ROLE_USER'],
                'password' => "testpassword",
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/User',
            '@type' => 'User',
            'username' => 'testUser',
        ]);
        $this->assertMatchesRegularExpression('~^/api/users/\d+$~', $response->toArray()['@id']);
        $this->assertMatchesResourceItemJsonSchema(User::class);
    }

    public function testPATCH(): void
    {
        $user = UserFactory::createOne();
        $response = static::createClientWithCredentials()->request('PATCH', self::URL_USER . "/" . $user->getId(), [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'username' => 'changeg',
            ],
        ]);
        $this->assertResponseStatusCodeSame(200);
    }

    public function testDELETE(): void
    {
        $user = UserFactory::createOne();
        $response = static::createClientWithCredentials()->request('DELETE', self::URL_USER . "/" . $user->getId());

        $this->assertResponseIsSuccessful();

        $response = static::createClientWithCredentials()->request('GET', self::URL_USER . "/" . $user->getId());
        $this->assertResponseStatusCodeSame(301);
    }
}
