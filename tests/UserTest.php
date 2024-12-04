<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use UserFactory;

class UserTest extends ApiTestCase
{
    const url = '/users';

    public function testGetCollection(): void
    {
        UserFactory::createMany(30);

        $response = static::createClient()->request('GET', '/users');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['@id' => '/']);
    }
}
