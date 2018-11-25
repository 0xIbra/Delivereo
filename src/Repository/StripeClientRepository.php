<?php

namespace App\Repository;

use App\Entity\StripeClient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StripeClient|null find($id, $lockMode = null, $lockVersion = null)
 * @method StripeClient|null findOneBy(array $criteria, array $orderBy = null)
 * @method StripeClient[]    findAll()
 * @method StripeClient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StripeClientRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StripeClient::class);
    }

//    /**
//     * @return StripeClient[] Returns an array of StripeClient objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?StripeClient
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
