<?php

namespace App\Factory;

use App\Entity\Order;
use App\Entity\OrderItems;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Order>
 */
final class OrderFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct() {}

    public static function class(): string
    {
        return Order::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->text(30),
            'pitch' => self::faker()->randomLetter() . self::faker()->numberBetween(0, 1) . self::faker()->numberBetween(0, 9),
            'pickUpDate' => self::faker()->dateTimeThisYear(),
            'isDeleted' => self::faker()->boolean(70),
            'isTaken' => self::faker()->boolean(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Order $order): void {})
        ;
    }

    final public static function createOrderWithItems(array $products, $numberOfOrders, $nbOfItemsPerOrderMax): array
    {
        $orders = self::createMany($numberOfOrders);

        foreach ($orders as $order) {
            $nbOfItems = rand(1, $nbOfItemsPerOrderMax);
            $selectedKeys = array_rand($products, $nbOfItems);
            if ($nbOfItems == 1) {
                OrderItemsFactory::createOne(
                    [
                        'order' => $order,
                        'product' => $products[$selectedKeys],
                        'quantity' => rand(1, 10),

                    ]
                );
            } else {
                foreach ($selectedKeys as $key) {
                    OrderItemsFactory::createOne(
                        [
                            'order' => $order,
                            'product' => $products[$key],
                            'quantity' => rand(1, 10),

                        ]
                    );
                }
            }
        };
        return $orders;
    }
}
