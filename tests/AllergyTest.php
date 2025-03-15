<?php

namespace App\Tests;

use App\Entity\Allergy;
use App\Factory\AllergyFactory;

class AllergyTest extends AbstractTest
{
    const NUMBEROFALLERGIES = 15;
    public const URL_ALLERGY = self::URL_BASE . "/allergies";

    public function testGetCollection(): void
    {
        AllergyFactory::createMany(self::NUMBEROFALLERGIES);

        $response = static::createClient()->request('GET', self::URL_ALLERGY);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(["totalItems" => self::NUMBEROFALLERGIES]);
    }

    public function testPOST(): void
    {
        $response = static::createClientWithCredentials()->request('POST', self::URL_ALLERGY, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'name' => 'Gluten',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/Allergy',
            '@type' => 'Allergy',
            'name' => 'Gluten'
        ]);
        $this->assertMatchesRegularExpression('~^/api/allergies/\d+$~', $response->toArray()['@id']);
    }

    public function testPATCH(): void
    {
        $allergy = AllergyFactory::createOne();

        $response = static::createClientWithCredentials()->request('PATCH', self::URL_ALLERGY . "/" . $allergy->getId(), [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'name' => "NewAllergyName",
            ],
        ]);
        $this->assertResponseStatusCodeSame(200);
    }
}
