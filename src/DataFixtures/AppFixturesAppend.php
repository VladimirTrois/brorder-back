<?php

namespace App\DataFixtures;

use App\Factory\AllergyFactory;
use App\Factory\ProductFactory;
use App\Factory\OrderFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class AppFixturesAppend extends Fixture implements FixtureGroupInterface
{

    const NUMBEROFORDERS = 300; //How many Orders to create
    const NUMBEROFITEMPERORDERMAX = 3; //How many Items per Order MAX to create 
    const NUMBERSOFDAYS = 100;

    //Creates real fixtures
    public const REALPRODUCTS = [
        ["Baguette", 120, 280, "baguette.jpg", 1],
        ["Tradition", 140, 280, "tradition.png", 2],
        ["Pavé Céréales", 120, 90, "pavecereales.png", 3],
        ["Pain au chocolat", 120, 90, "painauchocolat.png", 4],
        ["Croissant", 120, 70, "croissant.png", 5],

    ];

    public function load(ObjectManager $manager): void
    {
        UserFactory::findOrCreate([
            'username' => 'user', 
        ])->setPassword('password') 
        ->setRoles(['ROLE_ADMIN']);

        $products = array();

        foreach(SELF::REALPRODUCTS as $product){
            $products[] = ProductFactory::findOrCreate(
                [
                    'name' => $product[0],
                    'price' => $product[1],
                    'weight' => $product[2],
                    'image' => $product[3],
                    'rank' => $product[4],
                    'stock' => 10,
                    'isAvailable' => true,
                ]
            );
        }

        //Create orders with items
        OrderFactory::createOrderWithItemsForTheNextXDay($products, self::NUMBEROFORDERS, self::NUMBEROFITEMPERORDERMAX,self::NUMBERSOFDAYS);
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['append'];
    }
}
