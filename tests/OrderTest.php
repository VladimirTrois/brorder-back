<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Product;
use App\Factory\OrderFactory;
use App\Factory\ProductFactory;
use App\Repository\ProductRepository;

class OrderTest extends AbstractTest
{
    const NUMBEROFORDERS = 15;
    const NUMBEROFPRODUCTS = 10;
    const NUMBEROFITEMPERORDERMAX = 3;
    public const URL_ORDER = self::URL_BASE . "/orders";
    public const URL_PRODUCT = self::URL_BASE . "/products";

    private Product $product1;
    private Product $product2;
    private Client $clientWithCredentials;

    public function setUp(): void
    {
        parent::setUp();
        $this->clientWithCredentials = $this->createClientWithCredentials(['roles' => ['ROLE_ADMIN']]);
        $this->product1 = ProductFactory::createOne(['stock' => 10]);
        $this->product2 = ProductFactory::createOne(['stock' => 10]);
    }

    public function testGetCollection(): void
    {
        OrderFactory::createMany(self::NUMBEROFORDERS);
        $response = $this->clientWithCredentials->request('GET', self::URL_ORDER);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(["totalItems" => self::NUMBEROFORDERS]);
    }

    public function testGET(): void
    {
        $order = OrderFactory::createOne();

        $response = $this->clientWithCredentials->request('GET', self::URL_ORDER . "/" . $order->getId());
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'id' => $order->getId(),
            'name' => $order->getName()
        ]);
    }

    public function testPOST(): void
    {
        $client = $this->createClient();

        $response = $client->request('POST', self::URL_ORDER, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'name' => 'testOrder',
                'pitch' => "A23",
                'pickUpDate' => (new \DateTime('+1 week'))->format('Y-m-d'),
                'items' => [
                    0 => [
                        "product" => '/api/products/' . $this->product1->getId(),
                        "quantity" => 2,
                    ],
                    1 => [
                        "product" => '/api/products/' . $this->product2->getId(),
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
            'pickUpDate' => (new \DateTime('+1 week'))->format('Y-m-d'),
            'items' => [
                0 => [
                    '@type' => 'OrderItems',
                    "product" => [
                        '@id' => '/api/products/' . $this->product1->getId(),
                        "name" => $this->product1->getName(),
                    ],
                    "quantity" => 2,
                ],
                1 => [
                    '@type' => 'OrderItems',
                    "product" => [
                        '@id' => '/api/products/' . $this->product2->getId(),
                        "name" => $this->product2->getName(),
                    ],
                    "quantity" => 4,
                ],
            ],
        ]);
        $this->assertMatchesRegularExpression('~^/api/orders/\d+$~', $response->toArray()['@id']);
    }

    public function testPATCH(): void
    {
        $client = $this->createClient();

        $responseOrder = $client->request('POST', self::URL_ORDER, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'name' => 'testOrder',
                'pitch' => "A23",
                'pickUpDate' => (new \DateTime('+1 week'))->format('Y-m-d'),
                'items' => [
                    0 => [
                        "product" => '/api/products/' . $this->product1->getId(),
                        "quantity" => 2,
                    ]
                ],
            ],
        ]);

        $response = $client->request('PATCH', self::URL_ORDER . "/" . $responseOrder->toArray()['id'], [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'isTaken' => true,
            ],
        ]);
        $this->assertResponseStatusCodeSame(200);
    }

    public function testPOSTonOrder(): void
    {
        $client = $this->createClient();

        $responseOrder = $client->request('POST', self::URL_ORDER, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'name' => 'testOrder',
                'pitch' => "A23",
                'pickUpDate' => (new \DateTime('+1 week'))->format('Y-m-d'),
                'items' => [
                    0 => [
                        "product" => '/api/products/' . $this->product1->getId(),
                        "quantity" => 4,
                    ],
                    1 => [
                        "product" => '/api/products/' . $this->product2->getId(),
                        "quantity" => 2,
                    ]
                ],
            ],
        ]);
        $this->assertResponseStatusCodeSame(201);

        $responseOrder = $client->request('POST', self::URL_ORDER, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'name' => 'testOrder',
                'pitch' => "A23",
                'pickUpDate' => (new \DateTime('+1 week'))->format('Y-m-d'),
                'items' => [
                    0 => [
                        "product" => '/api/products/' . $this->product1->getId(),
                        "quantity" => 1,
                    ],
                    1 => [
                        "product" => '/api/products/' . $this->product2->getId(),
                        "quantity" => 5,
                    ]
                ],
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'cause' => [
                '@context' => '/api/contexts/Order',
                '@type' => 'Order',
                'name' => 'testOrder',
                'pitch' => "A23",
                'pickUpDate' => (new \DateTime('+1 week'))->format('Y-m-d'),
                'items' => [
                    0 => [
                        '@type' => 'OrderItems',
                        "product" => [
                            "name" => $this->product1->getName(),
                        ],
                        "quantity" => 4,
                    ],
                    1 => [
                        '@type' => 'OrderItems',
                        "product" => [
                            "name" => $this->product2->getName(),
                        ],
                        "quantity" => 2,
                    ],
                ],
            ]
        ]);
    }

    public function testPOSTonDeletedOrder(): void
    {
        $product1 = ProductFactory::createOne(['stock' => 10]);
        $product2 = ProductFactory::createOne(['stock' => -1]);
        $client = $this->createClient();

        $responseOrder = $client->request('POST', self::URL_ORDER, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'name' => 'testOrder',
                'pitch' => "A23",
                'pickUpDate' => (new \DateTime('+1 week'))->format('Y-m-d'),
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
        $this->assertResponseStatusCodeSame(201);
        $this->assertProductStockEqual($product1, 6);
        $this->assertProductStockEqual($product2, -1);

        $client->request('PATCH', self::URL_ORDER . "/" . $responseOrder->toArray()['id'], [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'isDeleted' => true,
            ],
        ]);
        $this->assertResponseStatusCodeSame(200);
        $this->assertProductStockEqual($product1, 10);
        $this->assertProductStockEqual($product2, -1);

        $responseOrder = $this->createClient()->request('POST', self::URL_ORDER, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'name' => 'testOrder',
                'pitch' => "A23",
                'pickUpDate' => (new \DateTime('+1 week'))->format('Y-m-d'),
                'items' => [
                    0 => [
                        "product" => '/api/products/' . $product1->getId(),
                        "quantity" => 1,
                    ],
                    1 => [
                        "product" => '/api/products/' . $product2->getId(),
                        "quantity" => 5,
                    ]
                ],
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/api/contexts/Order',
            '@type' => 'Order',
            'name' => 'testOrder',
            'pitch' => "A23",
            'pickUpDate' => (new \DateTime('+1 week'))->format('Y-m-d'),
            'items' => [
                0 => [
                    '@type' => 'OrderItems',
                    "product" => [
                        '@id' => '/api/products/' . $product1->getId(),
                        "name" => $product1->getName(),
                    ],
                    "quantity" => 1,
                ],
                1 => [
                    '@type' => 'OrderItems',
                    "product" => [
                        '@id' => '/api/products/' . $product2->getId(),
                        "name" => $product2->getName(),
                    ],
                    "quantity" => 5,
                ],
            ],
        ]);

        $this->assertProductStockEqual($product1, 9);
        $this->assertProductStockEqual($product2, -1);
    }

    public function testStockOnPOST(): void
    {
        $product = ProductFactory::createOne(['stock' => 10]);

        $responseOrder = $this->clientWithCredentials->request('POST', self::URL_ORDER, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'name' => 'testOrder',
                'pitch' => "A23",
                'pickUpDate' => (new \DateTime('+1 week'))->format('Y-m-d'),
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

        $responseOrder = $this->clientWithCredentials->request('POST', self::URL_ORDER, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'name' => 'testOrder',
                'pitch' => "A23",
                'pickUpDate' => (new \DateTime('+1 week'))->format('Y-m-d'),
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


        $this->clientWithCredentials->request('PATCH', self::URL_ORDER . "/" . $responseOrder->toArray()['id'], [
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
        $responseOrder = $this->clientWithCredentials->request('POST', self::URL_ORDER, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'name' => 'testOrder',
                'pitch' => "A23",
                'pickUpDate' => (new \DateTime('+1 week'))->format('Y-m-d'),
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


        $this->clientWithCredentials->request('PATCH', self::URL_ORDER . "/" . $responseOrder->toArray()['id'], [
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
        $responseOrder = $this->clientWithCredentials->request('POST', self::URL_ORDER, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'name' => 'testOrder',
                'pitch' => "A23",
                'pickUpDate' => (new \DateTime('+1 week'))->format('Y-m-d'),
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

        $this->clientWithCredentials->request('PATCH', self::URL_ORDER . "/" . $responseOrder->toArray()['id'], [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'isDeleted' => true,
            ],
        ]);

        $this->assertProductStockEqual($product1, 10);
        $this->assertProductStockEqual($product2, 20);



        $this->clientWithCredentials->request('PATCH', self::URL_ORDER . "/" . $responseOrder->toArray()['id'], [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'isDeleted' => true,
            ],
        ]);

        $this->assertProductStockEqual($product1, 10);
        $this->assertProductStockEqual($product2, 20);

        $this->clientWithCredentials->request('PATCH', self::URL_ORDER . "/" . $responseOrder->toArray()['id'], [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'isDeleted' => false,
            ],
        ]);

        $this->assertProductStockEqual($product1, 6);
        $this->assertProductStockEqual($product2, 18);
    }

    public function testStockInfinity(): void
    {
        //Set product to stock infinity
        $product1 = ProductFactory::createOne(['stock' => -1]);

        //Test by adding product to an order
        $responseOrder = $this->clientWithCredentials->request('POST', self::URL_ORDER, [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'name' => 'testOrder',
                'pitch' => "A23",
                'pickUpDate' => (new \DateTime('+1 week'))->format('Y-m-d'),
                'items' => [
                    0 => [
                        "product" => '/api/products/' . $product1->getId(),
                        "quantity" => 4,
                    ]
                ],
            ],
        ]);
        $this->assertProductStockEqual($product1, -1);

        //Test by deleting order
        $this->clientWithCredentials->request('PATCH', self::URL_ORDER . "/" . $responseOrder->toArray()['id'], [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'isDeleted' => true,
            ],
        ]);
        $this->assertProductStockEqual($product1, -1);

        //Test by undeleting order
        $this->clientWithCredentials->request('PATCH', self::URL_ORDER . "/" . $responseOrder->toArray()['id'], [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => [
                'isDeleted' => false,
            ],
        ]);
        $this->assertProductStockEqual($product1, -1);
    }

    function assertProductStockEqual($product, $expectedStock)
    {
        $repository = static::getContainer()->get(ProductRepository::class);
        $refreshedProduct = $repository->find($product->getId());
        $this->assertNotNull($refreshedProduct, "Product not found in database");
        $this->assertSame($expectedStock, $refreshedProduct->getStock(), "Stock mismatch for product ID {$product->getId()}");
    }
}
