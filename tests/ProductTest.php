<?php

namespace App\Tests;

use App\Entity\Product;
use App\Factory\ProductFactory;

const URL = 'https://localhost:4443/api/products';
const NUMBERSOFPRODUCTS = 30;

class ProductTest extends AbstractTest
{
    public function testGetProductCollection(): void
    {
        ProductFactory::createMany(NUMBERSOFPRODUCTS);

        $response = static::createClient()->request('GET', URL);

        $this->assertResponseIsSuccessful();

        // For collections
        $this->assertMatchesResourceCollectionJsonSchema(Product::class);
        $this->assertJsonContains(["totalItems" => NUMBERSOFPRODUCTS]);
    }

    public function testProductNoAdmin(): void
    {
        $product = ProductFactory::createOne();

        $response = static::createClient()->request('GET', URL . "/" . $product->getId());
        $this->assertResponseStatusCodeSame(401);

        $response = static::createClient()->request('POST', URL);
        $this->assertResponseStatusCodeSame(401);

        $response = static::createClient()->request('PATCH', URL . "/" . $product->getId());
        $this->assertResponseStatusCodeSame(401);

        $response = static::createClient()->request("DELETE", URL . "/" . $product->getId());
        $this->assertResponseStatusCodeSame(401);
    }
}
