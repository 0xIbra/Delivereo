<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\Restaurant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use http\Env\Request;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Order::class);
    }


    public function countOrders()
    {
        $qb = $this->createQueryBuilder('o');
        $qb->select('COUNT(o)');
        return $qb->getQuery()->getSingleResult();
    }


    public function findByDateRange(\DateTime $dateTime)
    {
        $now = new \DateTime();
        $qb = $this->createQueryBuilder('o');
        $qb->where('o.orderedAt BETWEEN :date AND :now')
            ->setParameter('date', $dateTime)
            ->setParameter('now', $now)
            ->orderBy('o.orderedAt', 'DESC');
        return $qb->getQuery()->getResult();
    }


    public function findRestaurantOrdersByDate(Restaurant $restaurant, \DateTime $dateTime)
    {
        $qb = $this->createQueryBuilder('o');
        $qb->select('o')
            ->join('o.restaurants', 'r')
            ->where('r.id = :id')
            ->setParameter('id', $restaurant->getId())
            ->andWhere('o.orderedAt LIKE :date')
            ->setParameter('date', '%'. $dateTime->format('Y-m-d') .'%');
        return $qb->getQuery()->getResult();
    }


    public function countByDate(\DateTime $date)
    {
        $qb = $this->createQueryBuilder('o');
        $qb->select('COUNT(o)')
            ->where('o.orderedAt LIKE :date')
            ->setParameter('date', '%'. $date->format('Y-m-d') .'%');
        return $qb->getQuery()->getOneOrNullResult();
    }


    public function recentRestaurantOrders(Restaurant $restaurant, $limit = null)
    {
        $qb = $this->createQueryBuilder('o');
        $qb->join('o.restaurants', 'r')
            ->where('r.id = :id')
            ->setParameter('id', $restaurant->getId())
            ->orderBy('o.orderedAt', 'DESC');
        if ($limit !== null)
        {
            $qb->setMaxResults($limit);
        }
        return $qb->getQuery()->getResult();
    }


//    /**
//     * @return Order[] Returns an array of Order objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Order
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
