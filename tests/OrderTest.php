<?php

namespace App\Tests;

use App\Factory\OrderFactory;
use App\Factory\ProductFactory;
use stdClass;


class OrderTest extends AbstractTest
{
    const NUMBEROFORDERS = 15;
    const NUMBEROFPRODUCTS = 10;
    const NUMBEROFITEMPERORDERMAX = 3;
    public const URL_ORDER = self::URL_BASE . "/orders";
    public const URL_PRODUCT = self::URL_BASE . "/products";

    public function testGetCollection(): void
    {
        OrderFactory::createMany(self::NUMBEROFORDERS);

        $response = static::createClientWithCredentials()->request('GET', self::URL_ORDER);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(["totalItems" => self::NUMBEROFORDERS]);
    }

    public function testGET(): void
    {
        $order = OrderFactory::createOne();
        $response = static::createClientWithCredentials()->request('GET', self::URL_ORDER . "/" . $order->getId());
        $this->assertResponseIsSuccessful();
    }

    public function testPOST(): void
    {
        $product1 = ProductFactory::createOne();
        $response1 = static::createClientWithCredentials()->request('GET', self::URL_BASE . "/products/" . $product1->getId());
        $json = new stdClass();
        $product2 = ProductFactory::createOne();
        $response2 = static::createClientWithCredentials()->request('GET', self::URL_BASE . "/products/" . $product2->getId());
        $json = new stdClass();

        $response = static::createClientWithCredentials()->request('POST', self::URL_ORDER, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'name' => 'testOrder',
                'pitch' => "A23",
                'pickUpDate' => "2024-11-23",
                'items' => [
                    0 => [
                        "product" => $response1->toArray()['@id'],
                        "quantity" => 2,
                    ],
                    1 => [
                        "product" => $response2->toArray()['@id'],
                        "quantity" => 4,
                    ],
                ],
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/Order',
            '@type' => 'Order',
            'name' => 'testOrder',
            'pitch' => "A23",
            'pickUpDate' => "2024-11-23",
            'items' => [
                0 => [
                    '@type' => 'OrderItems',
                    "product" => [
                        '@id' => $response1->toArray()['@id'],
                        "name" => $response1->toArray()['name'],
                    ],
                    "quantity" => 2,
                ],
                1 => [
                    '@type' => 'OrderItems',
                    "product" => [
                        '@id' => $response2->toArray()['@id'],
                        "name" => $response2->toArray()['name'],
                    ],
                    "quantity" => 4,
                ],
            ],
        ]);
        $this->assertMatchesRegularExpression('~^/api/orders/\d+$~', $response->toArray()['@id']);
    }

    public function testPATCH(): void
    {
        $product = ProductFactory::createOne();
        $responseOrder = static::createClientWithCredentials()->request('POST', self::URL_ORDER, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'name' => 'testOrder',
                'pitch' => "A23",
                'pickUpDate' => "2024-11-23",
                'items' => [
                    0 => [
                        "product" => '/api/products/' . $product->getId(),
                        "quantity" => 2,
                    ]
                ],
            ],
        ]);

        $response = static::createClientWithCredentials()->request('PATCH', self::URL_ORDER . "/" . $responseOrder->toArray()['id'], [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'isTaken' => true,
            ],
        ]);
        $this->assertResponseStatusCodeSame(200);
    }

    public function testStockOnPOST(): void
    {
        $product = ProductFactory::createOne(['stock' => 10]);
        $responseOrder = static::createClientWithCredentials()->request('POST', self::URL_ORDER, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'name' => 'testOrder',
                'pitch' => "A23",
                'pickUpDate' => "2024-11-23",
                'items' => [
                    0 => [
                        "product" => '/api/products/' . $product->getId(),
                        "quantity" => 2,
                    ]
                ],
            ],
        ]);

        $this->assertProductStockEqual($product, 8);
    }

    public function testStockOnChangeItemQuantity(): void
    {
        $product1 = ProductFactory::createOne(['stock' => 10]);
        $product2 = ProductFactory::createOne(['stock' => 20]);
        $responseOrder = static::createClientWithCredentials()->request('POST', self::URL_ORDER, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'name' => 'testOrder',
                'pitch' => "A23",
                'pickUpDate' => "2024-11-23",
                'items' => [
                    0 => [
                        "product" => '/api/products/' . $product1->getId(),
                        "quantity" => 2,
                    ],
                    1 => [
                        "product" => '/api/products/' . $product2->getId(),
                        "quantity" => 10,
                    ]
                ],
            ],
        ]);

        $this->assertProductStockEqual($product1, 8);
        $this->assertProductStockEqual($product2, 10);


        $responseOrderPatch = static::createClientWithCredentials()->request('PATCH', self::URL_ORDER . "/" . $responseOrder->toArray()['id'], [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'items' => [
                    0 => [
                        "product" => '/api/products/' . $product1->getId(),
                        "quantity" => 5,
                    ],
                    1 => [
                        "product" => '/api/products/' . $product2->getId(),
                        "quantity" => 2,
                    ],
                ],
            ],
        ]);

        $this->assertProductStockEqual($product1, 5);
        $this->assertProductStockEqual($product2, 18);
    }

    public function testStockOnAddAndRemoveItem(): void
    {
        $product1 = ProductFactory::createOne(['stock' => 10]);
        $product2 = ProductFactory::createOne(['stock' => 10]);
        $product3 = ProductFactory::createOne(['stock' => 10]);
        $responseOrder = static::createClientWithCredentials()->request('POST', self::URL_ORDER, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'name' => 'testOrder',
                'pitch' => "A23",
                'pickUpDate' => "2024-11-23",
                'items' => [
                    0 => [
                        "product" => '/api/products/' . $product1->getId(),
                        "quantity" => 2,
                    ],
                    1 => [
                        "product" => '/api/products/' . $product2->getId(),
                        "quantity" => 5,
                    ]
                ],
            ],
        ]);

        $this->assertProductStockEqual($product1, 8);
        $this->assertProductStockEqual($product2, 5);
        $this->assertProductStockEqual($product3, 10);


        $responseOrderPatch = static::createClientWithCredentials()->request('PATCH', self::URL_ORDER . "/" . $responseOrder->toArray()['id'], [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'items' => [
                    0 => [
                        "product" => '/api/products/' . $product1->getId(),
                        "quantity" => 2,
                    ],
                    1 => [
                        "product" => '/api/products/' . $product3->getId(),
                        "quantity" => 5,
                    ],
                ],
            ],
        ]);

        $this->assertProductStockEqual($product1, 8);
        $this->assertProductStockEqual($product2, 10);
        $this->assertProductStockEqual($product3, 5);
    }

    public function testStockOnDELETE(): void
    {
        $product1 = ProductFactory::createOne(['stock' => 10]);
        $product2 = ProductFactory::createOne(['stock' => 20]);
        $responseOrder = static::createClientWithCredentials()->request('POST', self::URL_ORDER, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'name' => 'testOrder',
                'pitch' => "A23",
                'pickUpDate' => "2024-11-23",
                'items' => [
                    0 => [
                        "product" => '/api/products/' . $product1->getId(),
                        "quantity" => 4,
                    ],
                    1 => [
                        "product" => '/api/products/' . $product2->getId(),
                        "quantity" => 2,
                    ]
                ],
            ],
        ]);

        $this->assertProductStockEqual($product1, 6);
        $this->assertProductStockEqual($product2, 18);

        static::createClientWithCredentials()->request('PATCH', self::URL_ORDER . "/" . $responseOrder->toArray()['id'], [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'isDeleted' => true,
            ],
        ]);

        $this->assertProductStockEqual($product1, 10);
        $this->assertProductStockEqual($product2, 20);

        static::createClientWithCredentials()->request('PATCH', self::URL_ORDER . "/" . $responseOrder->toArray()['id'], [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'isDeleted' => false,
            ],
        ]);

        $this->assertProductStockEqual($product1, 6);
        $this->assertProductStockEqual($product2, 18);
    }

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

    function assertProductStockEqual($product, $stock)
    {
        $responseStock = static::createClientWithCredentials()->request('GET', self::URL_PRODUCT . "/" . $product->getId());
        $this->assertJsonContains([
            'name' => $product->getName(),
            'stock' => $stock
        ]);
    }
}
