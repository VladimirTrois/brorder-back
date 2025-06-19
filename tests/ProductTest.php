<?php

namespace App\Tests;

use App\Entity\Product;
use App\Factory\AllergyFactory;
use App\Factory\ProductFactory;

class ProductTest extends AbstractTest
{
    const NUMBERSOFPRODUCTS = 30;
    public const URL_PRODUCT = self::URL_BASE . "/products";

    public function testGetCollection(): void
    {
        ProductFactory::createMany(self::NUMBERSOFPRODUCTS);

        $response = $this->createClient()->request('GET', self::URL_PRODUCT);

        $this->assertResponseIsSuccessful();
        $this->assertMatchesResourceCollectionJsonSchema(Product::class);
        $this->assertJsonContains(["totalItems" => self::NUMBERSOFPRODUCTS]);
    }

    public function testGetCollectionWithAllergies(): void
    {
        $allergy1 = AllergyFactory::createOne(['name' => 'B']);
        $allergy2 = AllergyFactory::createOne(['name' => 'A']);

        $response = $this->createClientWithCredentials(['roles' => ['ROLE_ADMIN']])->request('POST', self::URL_PRODUCT, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'name' => 'AAAAAAAAA',
                'price' => 3452,
                'weight' => 234,
                'image' => '/url/test',
                'stock' => 3,
                'allergies' => [
                    0 => [
                        "allergy" => '/api/allergies/' . $allergy1->getId(),
                        "level" => 'No',
                    ],
                    1 => [
                        "allergy" => '/api/allergies/' . $allergy2->getId(),
                        "level" => 'May contain',
                    ]
                ]
            ],
        ]);
        $this->assertResponseIsSuccessful();

        $response = $this->createClient()->request('GET', self::URL_PRODUCT . '/allergies');

        $this->assertResponseIsSuccessful();
        $this->assertMatchesResourceCollectionJsonSchema(Product::class);
        $this->assertJsonContains([
            'member' => [
                0 => [
                    'name' => 'AAAAAAAAA',
                    'allergies' => [
                        0 => [
                            "@type" => 'ProductAllergy',
                            "allergy" => [
                                "name" => $allergy2->getName(),
                            ],
                            "level" => 'May contain',
                        ],
                        1 => [
                            "@type" => 'ProductAllergy',
                            "allergy" => [
                                "name" => $allergy1->getName(),
                            ],
                            "level" => 'No',
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function testGET(): void
    {
        $product = ProductFactory::createOne();
        $response = $this->createClientWithCredentials(['roles' => ['ROLE_ADMIN']])->request('GET', self::URL_PRODUCT . "/" . $product->getId());
        $this->assertResponseIsSuccessful();
        $this->assertMatchesResourceItemJsonSchema(Product::class);
    }

    public function testPOST(): void
    {
        $allergy1 = AllergyFactory::createOne();
        $allergy2 = AllergyFactory::createOne();

        $response = $this->createClientWithCredentials(['roles' => ['ROLE_ADMIN']])->request('POST', self::URL_PRODUCT, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'name' => 'productTest',
                'price' => 3452,
                'weight' => 234,
                'image' => '/url/test',
                'stock' => 3,
                'allergies' => [
                    0 => [
                        "allergy" => '/api/allergies/' . $allergy1->getId(),
                        "level" => 'No',
                    ],
                    1 => [
                        "allergy" => '/api/allergies/' . $allergy2->getId(),
                        "level" => 'May contain',
                    ]
                ]
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/Product',
            '@type' => 'Product',
            'name' => 'productTest',
            'price' => 3452,
            'weight' => 234,
            'image' => '/url/test',
            'stock' => 3,
        ]);
        $this->assertMatchesRegularExpression('~^/api/products/\d+$~', $response->toArray()['@id']);
        $this->assertMatchesResourceItemJsonSchema(Product::class);
    }

    public function testPATCH(): void
    {
        $product = ProductFactory::createOne();
        $response = $this->createClientWithCredentials(['roles' => ['ROLE_ADMIN']])->request('PATCH', self::URL_PRODUCT . "/" . $product->getId(), [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'username' => 'change',
            ],
        ]);
        $this->assertResponseStatusCodeSame(200);
    }

    public function testNoAdmin(): void
    {
        $product = ProductFactory::createOne();

        $response = static::createClient()->request('GET', self::URL_PRODUCT . "/" . $product->getId());
        $this->assertResponseStatusCodeSame(401);

        $response = static::createClient()->request('POST', self::URL_PRODUCT);
        $this->assertResponseStatusCodeSame(401);

        $response = static::createClient()->request('PATCH', self::URL_PRODUCT . "/" . $product->getId());
        $this->assertResponseStatusCodeSame(401);
    }

    // public function testDELETE(): void
    // {
    //     $product = ProductFactory::createOne();
    //     $response = $this->createClientWithCredentials(['roles' => ['ROLE_ADMIN']])->request('DELETE', self::URL_PRODUCT . "/" . $product->getId());

    //     $this->assertResponseIsSuccessful();

    //     $response = $this->createClientWithCredentials(['roles' => ['ROLE_ADMIN']])->request('GET', self::URL_PRODUCT . "/" . $product->getId());
    //     $this->assertResponseStatusCodeSame(404);
    // }
}
