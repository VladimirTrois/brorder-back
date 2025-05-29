<?php

// src/DataProvider/StatisticsProvider.php
namespace App\DataProvider;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\State\ProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Product;

class ProductsOrderedAllergiesProvider implements ProviderInterface
{
    private EntityManagerInterface $entityManager;
    private iterable $collectionExtensions;

    public function __construct(EntityManagerInterface $entityManager, iterable $collectionExtensions)
    {
        $this->entityManager = $entityManager;
        $this->collectionExtensions = $collectionExtensions;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $queryBuilder = $this->entityManager->getRepository(Product::class)->createQueryBuilder('p')
            ->leftJoin('p.allergies', 'pa')
            ->leftJoin('pa.allergy', 'a')
            ->addSelect('a')
            ->addSelect('pa')
            ->orderBy('p.rank', 'ASC')
            ->addOrderBy('a.name', 'ASC');

        // Add a queryNameGenerator to allow multiple orderBy
        $queryNameGenerator = new QueryNameGenerator();
        foreach ($this->collectionExtensions as $extension) {
            if ($extension instanceof QueryCollectionExtensionInterface) {
                $extension->applyToCollection($queryBuilder, $queryNameGenerator, Product::class, $operation, $context);
            }
        }
        return $queryBuilder->getQuery()->getResult();
    }
}
