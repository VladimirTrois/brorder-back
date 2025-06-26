<?php

namespace App\Factory;

use App\Entity\Order;
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
        $faker = self::faker();

        return [
            'name' => strtolower($faker->lastName()),
            'pitch' => $faker->randomLetter() . $faker->numberBetween(0, 1) . $faker->numberBetween(0, 9),
            'pickUpDate' => $faker->dateTimeBetween('-0 days', '+0 days'),
            'isDeleted' => $faker->boolean(10),
            'isTaken' => $faker->boolean(10),
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

        self::addItemsToOrders($orders,$products,$nbOfItemsPerOrderMax);

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

        self::addItemsToOrders($orders,$products,$nbOfItemsPerOrderMax);

        return $orders;
    }

    final public static function createOrderWithItemsForTheNextXDay(array $products, $numberOfOrders, $nbOfItemsPerOrderMax, $numberOfDays): array
    {
        $orders = self::createMany(
            $numberOfOrders,
            static function (int $i) use ($numberOfDays) {
                return ['pickUpDate' => self::faker()->dateTimeBetween('-1 days', "+$numberOfDays days")];
            }
        );

        self::addItemsToOrders($orders,$products,$nbOfItemsPerOrderMax);

        return $orders;
    }

    private static function addItemsToOrders(array $orders,array $products,$nbOfItemsPerOrderMax){
        foreach ($orders as $order) {
            $total = 0;
            $nbOfItems = rand(1, $nbOfItemsPerOrderMax);
            $selectedKeys = (array) array_rand($products, $nbOfItems);
            foreach ($selectedKeys as $key) {
                $quantity = rand(1, 5);
                OrderItemsFactory::createOne(
                    [
                        'order' => $order,
                        'product' => $products[$key],
                        'quantity' => $quantity,

                    ]
                );
                $total += $products[$key]->getPrice() * $quantity;
            }
            $order->setTotal($total);
        };
    }
}
