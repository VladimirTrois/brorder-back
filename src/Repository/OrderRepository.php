<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    //    /**
    //     * @return Order[] Returns an array of Order objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Order
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function getDailyTotalOrders(\DateTimeInterface $date): array
    {
        $qb = $this->createQueryBuilder('o')
            ->select('COUNT(o.id) AS totalOrders')
            ->where('o.pickUpDate = :date AND o.isDeleted=false')
            ->setParameter('date', $date->format('Y-m-d'));

        return $qb->getQuery()->getResult();
    }

    public function getDailyTotalProducts(\DateTimeInterface $date): array
    {

        $qb = $this->createQueryBuilder('o')
            ->select('p.id As productId, p.name AS productName, SUM(orderItems.quantity) AS productCount')
            ->join('o.items', 'orderItems')
            ->join('orderItems.product', 'p')
            ->where('o.pickUpDate = :date AND o.isDeleted=false')
            ->setParameter('date', $date->format('Y-m-d'))
            ->groupBy('p.id');

        return $qb->getQuery()->getResult();
    }

    public function getDailyTotalProductsNotSold(\DateTimeInterface $date): array
    {

        $qb = $this->createQueryBuilder('o')
            ->select('p.id As productId, p.name AS productName, SUM(orderItems.quantity) AS productCount')
            ->join('o.items', 'orderItems')
            ->join('orderItems.product', 'p')
            ->where('o.pickUpDate = :date AND o.isDeleted=false AND o.isTaken=false')
            ->setParameter('date', $date->format('Y-m-d'))
            ->groupBy('p.id');

        return $qb->getQuery()->getResult();
    }
}
