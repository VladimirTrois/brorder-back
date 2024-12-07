<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Order;
use App\Factory\OrderFactory;
use App\Factory\OrderItemsFactory;
use App\Factory\ProductFactory;
use stdClass;

const NUMBEROFORDERS = 15;
const NUMBEROFPRODUCTS = 10;
const NUMBEROFITEMPERORDERMAX = 3;

class OrderTest extends AbstractTest
{
    public const URL_ORDER = self::URL_BASE . "/api/orders";

    public function testGetCollection(): void
    {
        OrderFactory::createMany(NUMBEROFORDERS);

        $response = static::createClientWithCredentials()->request('GET', self::URL_ORDER);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(["totalItems" => NUMBEROFORDERS]);
    }

    public function testGET(): void
    {
        $order = OrderFactory::createOne();
        $response = static::createClientWithCredentials()->request('GET', self::URL_ORDER . "/" . $order->getId());
        $this->assertResponseIsSuccessful();
    }

    // public function testPOST(): void
    // {
    //     $product1 = ProductFactory::createOne();
    //     $response1 = static::createClientWithCredentials()->request('GET', self::URL_BASE . "/api/products/" . $product1->getId());
    //     $json = new stdClass();
    //     $product2 = ProductFactory::createOne();
    //     $response2 = static::createClientWithCredentials()->request('GET', self::URL_BASE . "/api/products/" . $product2->getId());
    //     $json = new stdClass();

    //     $response = static::createClientWithCredentials()->request('POST', self::URL_ORDER, [
    //         'headers' => ['Content-Type' => 'application/ld+json'],
    //         'json' => [
    //             'name' => 'testOrder',
    //             'pitch' => "A23",
    //             'pickUpDate' => "2024-11-23",
    //             'items' => [[
    //                 "product" => 23,
    //                 "quantity" => 2,
    //             ]],
    //         ],
    //     ]);

    //     $this->assertResponseStatusCodeSame(201);
    //     $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
    //     $this->assertJsonContains([
    //         '@context' => '/api/contexts/Product',
    //         '@type' => 'Product',
    //         'name' => 'productTest',
    //         'price' => 3452,
    //         'weight' => 234,
    //         'image' => '/url/test',
    //     ]);
    //     $this->assertMatchesRegularExpression('~^/api/products/\d+$~', $response->toArray()['@id']);
    //     $this->assertMatchesResourceItemJsonSchema(Order::class);
    // }

    // public function testPATCH(): void
    // {
    //     $order = OrderFactory::createOne();
    //     $response = static::createClientWithCredentials()->request('PATCH', self::URL_ORDER . "/" . $order->getId(), [
    //         'headers' => ['Content-Type' => 'application/merge-patch+json'],
    //         'json' => [
    //             'isTaken' => true,
    //         ],
    //     ]);
    //     $this->assertResponseStatusCodeSame(200);
    // }

    // public function testDELETE(): void
    // {
    //     $order = OrderFactory::createOne();
    //     $response = static::createClientWithCredentials()->request('DELETE', self::URL_ORDER . "/" . $order->getId());

    //     $this->assertResponseIsSuccessful();

    //     $response = static::createClientWithCredentials()->request('GET', self::URL_ORDER . "/" . $order->getId());
    //     $this->assertResponseStatusCodeSame(301);
    // }

    // public function testNoAdmin(): void
    // {
    //     $order = OrderFactory::createOne();

    //     $response = static::createClient()->request('GET', self::URL_ORDER  . "/" . $order->getId());
    //     $this->assertResponseStatusCodeSame(401);

    //     $response = static::createClient()->request('POST', self::URL_ORDER);
    //     $this->assertResponseStatusCodeSame(401);

    //     $response = static::createClient()->request('PATCH', self::URL_ORDER  . "/" . $order->getId());
    //     $this->assertResponseStatusCodeSame(401);

    //     $response = static::createClient()->request("DELETE", self::URL_ORDER  . "/" . $order->getId());
    //     $this->assertResponseStatusCodeSame(401);
    // }
}
