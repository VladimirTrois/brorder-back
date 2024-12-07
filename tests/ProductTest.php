<?php

namespace App\Tests;

use App\Entity\Product;
use App\Factory\ProductFactory;

const NUMBERSOFPRODUCTS = 30;

class ProductTest extends AbstractTest
{
    public const URL_PRODUCT = self::URL_BASE . "/api/products";

    public function testGetCollection(): void
    {
        ProductFactory::createMany(NUMBERSOFPRODUCTS);

        $response = static::createClient()->request('GET', self::URL_PRODUCT);

        $this->assertResponseIsSuccessful();
        $this->assertMatchesResourceCollectionJsonSchema(Product::class);
        $this->assertJsonContains(["totalItems" => NUMBERSOFPRODUCTS]);
    }

    public function testGET(): void
    {
        $product = ProductFactory::createOne();
        $response = static::createClientWithCredentials()->request('GET', self::URL_PRODUCT . "/" . $product->getId());
        $this->assertResponseIsSuccessful();
        $this->assertMatchesResourceItemJsonSchema(Product::class);
    }

    public function testPOST(): void
    {
        $response = static::createClientWithCredentials()->request('POST', self::URL_PRODUCT, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'name' => 'productTest',
                'price' => 3452,
                'weight' => 234,
                'image' => '/url/test',
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
        ]);
        $this->assertMatchesRegularExpression('~^/api/products/\d+$~', $response->toArray()['@id']);
        $this->assertMatchesResourceItemJsonSchema(Product::class);
    }

    public function testPATCH(): void
    {
        $product = ProductFactory::createOne();
        $response = static::createClientWithCredentials()->request('PATCH', self::URL_PRODUCT . "/" . $product->getId(), [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'username' => 'changeg',
            ],
        ]);
        $this->assertResponseStatusCodeSame(200);
    }

    public function testDELETE(): void
    {
        $product = ProductFactory::createOne();
        $response = static::createClientWithCredentials()->request('DELETE', self::URL_PRODUCT . "/" . $product->getId());

        $this->assertResponseIsSuccessful();

        $response = static::createClientWithCredentials()->request('GET', self::URL_PRODUCT . "/" . $product->getId());
        $this->assertResponseStatusCodeSame(301);
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

        $response = static::createClient()->request("DELETE", self::URL_PRODUCT . "/" . $product->getId());
        $this->assertResponseStatusCodeSame(401);
    }
}
