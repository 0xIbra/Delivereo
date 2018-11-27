<?php

namespace App\Repository;

use App\Entity\OrderMenu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method OrderMenu|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderMenu|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderMenu[]    findAll()
 * @method OrderMenu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderMenuRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OrderMenu::class);
    }

//    /**
//     * @return OrderMenu[] Returns an array of OrderMenu objects
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
    public function findOneBySomeField($value): ?OrderMenu
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
