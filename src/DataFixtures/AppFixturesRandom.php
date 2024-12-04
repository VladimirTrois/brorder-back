<?php

namespace App\DataFixtures;

use App\Factory\OrderFactory;
use App\Factory\OrderItemsFactory;
use App\Factory\ProductFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;   

class AppFixturesRandom extends Fixture implements FixtureGroupInterface
{
    public const NUMBEROFPRODUCTS = 20; //How many Products to create
    public const NUMBEROFORDERS = 5; //How many Orders to create
    public const NUMBERSOFITEMSPERORDERSMAX = 2; //How many Items per Order to create
    public const NUMBERSOFUSERS = 10;

    public function load(ObjectManager $manager): void
    {
        UserFactory::createMany(SELF::NUMBERSOFUSERS);
        $products = ProductFactory::createMany(SELF::NUMBEROFPRODUCTS);
        $orders = OrderFactory::createMany(SELF::NUMBEROFORDERS);

        for($i=0; $i<SELF::NUMBERSOFITEMSPERORDERSMAX*SELF::NUMBEROFORDERS; $i++){
            OrderItemsFactory::createOne(
                [
                    'order' => $orders[rand(0,SELF::NUMBEROFORDERS-1)],
                    'product' =>$products[rand(0,SELF::NUMBEROFPRODUCTS-1)],
                    'quantity' => rand(1,10),

                ]
            );
        };

        $manager->flush();

    }

    public static function getGroups(): array
    {
        return ['random'];
    }
}
