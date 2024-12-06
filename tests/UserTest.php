<?php

namespace App\Tests;

use App\Entity\User;
use App\Factory\UserFactory;

class UserTest extends AbstractTest
{
    public const URL_USER = self::URL_BASE . "/api/users";

    public function testAdminResource()
    {
        $response = $this->createClientWithCredentials()
            ->request('GET', self::URL_USER);
        $this->assertResponseIsSuccessful();
    }

    public function testLoginAsUser()
    {
        UserFactory::createOne(
            [
                'username' => 'user@example.com',
                'password' => '$3cr3t',
                'roles' => ["ROLE_USER"],
            ]
        );

        $token = $this->getToken(
            [
                'username' => 'user@example.com',
                'password' => '$3cr3t',
            ]
        );

        $response = $this->createClientWithCredentials($token)->request('GET', self::URL_USER);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testGetCollection(): void
    {
        $response = static::createClientWithCredentials()->request('GET', self::URL_USER);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'totalItems' => self::NUMBERSOFUSERS,
        ]);
        $this->assertMatchesResourceCollectionJsonSchema(User::class);
    }

    public function testCreateUser(): void
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
}
