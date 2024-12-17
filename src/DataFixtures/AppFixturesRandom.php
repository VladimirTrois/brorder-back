<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Factory\OrderFactory;
use App\Factory\OrderItemsFactory;
use App\Factory\ProductFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class AppFixturesRandom extends Fixture implements FixtureGroupInterface
{
    const NUMBEROFPRODUCTS = 20; //How many Products to create
    const NUMBEROFORDERS = 5; //How many Orders to create
    const NUMBEROFITEMPERORDERMAX = 5; //How many Items per Order MAX to create 
    const NUMBERSOFUSERS = 10;

    public function load(ObjectManager $manager): void
    {
        //Create admin for tests
        UserFactory::createOne([
            'username' => "admin",
            'password' => 'copain',
            'roles' => ["ROLE_ADMIN"],
        ]);

        //Create Users
        UserFactory::createMany(self::NUMBERSOFUSERS);
        //Create products
        $products = ProductFactory::createMany(self::NUMBEROFPRODUCTS);
        //Create orders with items
        $orders = OrderFactory::createOrderWithItems($products, self::NUMBEROFORDERS, self::NUMBEROFITEMPERORDERMAX);
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['random'];
    }
}
