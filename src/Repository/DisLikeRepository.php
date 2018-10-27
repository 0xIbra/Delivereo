<?php

namespace App\Repository;

use App\Entity\DisLike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DisLike|null find($id, $lockMode = null, $lockVersion = null)
 * @method DisLike|null findOneBy(array $criteria, array $orderBy = null)
 * @method DisLike[]    findAll()
 * @method DisLike[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DisLikeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DisLike::class);
    }

//    /**
//     * @return DisLike[] Returns an array of DisLike objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DisLike
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
