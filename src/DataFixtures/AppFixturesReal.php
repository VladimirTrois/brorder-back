<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\OrderItems;
use App\Entity\Product;
use App\Entity\User;
use App\Factory\ProductFactory;
use App\Factory\OrderFactory;
use App\Factory\OrderItemsFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface; 

class AppFixturesReal extends Fixture implements FixtureGroupInterface
{
    //Creates real fixtures
    public const REALPRODUCTS = [
        ["Baguette",120,280,"/img/products/baguette.jpg"],
        ["Tradition",140,280,"/img/products/tradition.png"],
        ["Croissant",120,70,"/img/products/croissant.png"],
        ["Pain au chocolat",120,90,"/img/products/painauchocolat.png"],

    ];

    public function load(ObjectManager $manager): void
    {
        UserFactory::createOne(
            [
                'username' => "admin",
                'password' => 'copain',
                'roles' => ["ROLE_ADMIN"],
                
            ]
        );

        ProductFactory::createMany(
            count(SELF::REALPRODUCTS),
            static function(int $i) {
                return[
                    'name' => SELF::REALPRODUCTS[$i-1][0],
                    'price' => SELF::REALPRODUCTS[$i-1][1],
                    'weight' => SELF::REALPRODUCTS[$i-1][2],
                    'image' => SELF::REALPRODUCTS[$i-1][3],
                ];
            }
        );


        // foreach(SELF::REALPRODUCTS as $realProduct) {
        //     $product = new Product();
        //     $product->setName($realProduct[0])
        //     ->setPrice($realProduct[1])
        //     ->setWeight($realProduct[2])
        //     ->setImage($realProduct[3]);
        //     $manager->persist($product);
        // }
           
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['real'];
    }
}