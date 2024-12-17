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
            'name' => self::faker()->lastName(),
            'pitch' => self::faker()->randomLetter() . self::faker()->numberBetween(0, 1) . self::faker()->numberBetween(0, 9),
            'pickUpDate' => self::faker()->dateTimeBetween('-0 days', '+0 days'),
            'isDeleted' => self::faker()->boolean(10),
            'isTaken' => self::faker()->boolean(10),
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

    final public static function createOrderWithItemsForToday(array $products, $numberOfOrders, $nbOfItemsPerOrderMax): array
    {
        $orders = self::createMany(
            $numberOfOrders,
            static function (int $i) {
                return ['pickUpDate' => self::faker()->dateTimeBetween('-1 days', '+1 days')];
            }
        );

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
