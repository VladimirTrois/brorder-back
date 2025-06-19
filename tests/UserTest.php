<?php

namespace App\Tests;

use App\Entity\User;
use App\Factory\UserFactory;

class UserTest extends AbstractTest
{
    public const URL_USER = self::URL_BASE . "/users";

    public function testAdminLogin()
    {
        $response = $this->createClientWithCredentials(['roles' => ['ROLE_ADMIN']])->request('GET', self::URL_USER);
        $this->assertResponseIsSuccessful();
    }

    public function testGetCollection(): void
    {
        $response = $this->createClientWithCredentials(['roles' => ['ROLE_ADMIN']])->request('GET', self::URL_USER);
        $this->assertResponseIsSuccessful();
        $this->assertMatchesResourceCollectionJsonSchema(User::class);
    }

    public function testGET(): void
    {
        $user = UserFactory::createOne();
        $response = $this->createClientWithCredentials(['roles' => ['ROLE_ADMIN']])->request('GET', self::URL_USER . "/" . $user->getId());
        $this->assertResponseIsSuccessful();
        $this->assertMatchesResourceItemJsonSchema(User::class);
    }

    public function testPOST(): void
    {
        $response = $this->createClientWithCredentials(['roles' => ['ROLE_ADMIN']])->request('POST', self::URL_USER, [
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
        $response = $this->createClientWithCredentials(['roles' => ['ROLE_ADMIN']])->request('PATCH', self::URL_USER . "/" . $user->getId(), [
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
        $response = $this->createClientWithCredentials(['roles' => ['ROLE_ADMIN']])->request('DELETE', self::URL_USER . "/" . $user->getId());

        $this->assertResponseIsSuccessful();
    }
}
