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

const NUMBEROFPRODUCTS = 20; //How many Products to create
const NUMBEROFORDERS = 5; //How many Orders to create
const NUMBEROFITEMPERORDERMAX = 5; //How many Items per Order MAX to create 
const NUMBERSOFUSERS = 10;
class AppFixturesRandom extends Fixture implements FixtureGroupInterface
{

    public function load(ObjectManager $manager): void
    {
        //Create admin for tests
        UserFactory::createOne([
            'username' => "admin",
            'password' => 'test',
            'roles' => ["ROLE_ADMIN"],
        ]);

        //Create Users
        UserFactory::createMany(NUMBERSOFUSERS);
        //Create products
        $products = ProductFactory::createMany(NUMBEROFPRODUCTS);
        //Create orders with items
        $orders = OrderFactory::createOrderWithItems($products, NUMBEROFORDERS, NUMBEROFITEMPERORDERMAX);
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['random'];
    }
}
