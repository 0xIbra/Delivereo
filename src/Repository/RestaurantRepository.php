<?php

namespace App\Repository;

use App\Entity\Restaurant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Restaurant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Restaurant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Restaurant[]    findAll()
 * @method Restaurant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RestaurantRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Restaurant::class);
    }

    public function countRestaurants()
    {
        $qb = $this->createQueryBuilder('r');
        $qb->select('COUNT(r)')
            ->where('r.published = true')
            ->andWhere('r.enabled = true');
        return $qb->getQuery()->getSingleResult();
    }

    public function getApplications($limit = null)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->where('r.enabled = false')
            ->andWhere('r.published = false')
            ->orderBy('r.createdAt', 'DESC');
        if ($limit !== null && is_int(intval($limit)))
        {
            $qb->setMaxResults($limit);
        }
        return $qb->getQuery()->getResult();
    }


    public function findByDateRange(\DateTime $dateTime)
    {
        $now = new \DateTime();
        $qb = $this->createQueryBuilder('r');
        $qb->where('r.createdAt BETWEEN :date AND :now')
            ->setParameter('date', $dateTime)
            ->setParameter('now', $now)
            ->orderBy('r.createdAt', 'DESC');
        return $qb->getQuery()->getResult();
    }


    public function countByDate(\DateTime $dateTime)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->select('COUNT(r)')
            ->where('r.createdAt LIKE :date')
            ->setParameter('date', '%'. $dateTime->format('Y-m-d') .'%');
        return $qb->getQuery()->getOneOrNullResult();
    }


//    /**
//     * @return Restaurant[] Returns an array of Restaurant objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Restaurant
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
