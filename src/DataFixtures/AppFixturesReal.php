<?php

namespace App\DataFixtures;

use App\Factory\AllergyFactory;
use App\Factory\ProductFactory;
use App\Factory\OrderFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class AppFixturesReal extends Fixture implements FixtureGroupInterface
{

    const NUMBEROFORDERS = 300; //How many Orders to create
    const NUMBEROFITEMPERORDERMAX = 3; //How many Items per Order MAX to create 

    //Creates real fixtures
    public const REALPRODUCTS = [
        ["Baguette", 120, 280, "baguette.jpg", 1],
        ["Tradition", 140, 280, "tradition.png", 2],
        ["Pavé Céréales", 120, 90, "pavecereales.png", 3],
        ["Pain au chocolat", 120, 90, "painauchocolat.png", 4],
        ["Croissant", 120, 70, "croissant.png", 5],

    ];

    public const REALALLERGIES = [
        "Gluten",
        "Oeufs/Eggs",
        "Lait/Milk",
        "Soja",
        "Graines de sésame"
    ];

    public function load(ObjectManager $manager): void
    {
        UserFactory::findOrCreate([
            'username' => 'user', 
        ])->setPassword('password') 
        ->setRoles(['ROLE_ADMIN']);

        $allergies = AllergyFactory::createMany(
            count(SELF::REALALLERGIES),
            static function (int $i) {
                return [
                    'name' => SELF::REALALLERGIES[$i - 1]
                ];
            }
        );

        $products = ProductFactory::createMany(
            count(SELF::REALPRODUCTS),
            static function (int $i) {
                return [
                    'name' => SELF::REALPRODUCTS[$i - 1][0],
                    'price' => SELF::REALPRODUCTS[$i - 1][1],
                    'weight' => SELF::REALPRODUCTS[$i - 1][2],
                    'image' => SELF::REALPRODUCTS[$i - 1][3],
                    'rank' => self::REALPRODUCTS[$i - 1][4],
                    'stock' => 10,
                    'isAvailable' => true,
                ];
            }
        );

        //Create orders with items
        $orders = OrderFactory::createOrderWithItemsForToday($products, self::NUMBEROFORDERS, self::NUMBEROFITEMPERORDERMAX);
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['real'];
    }
}
