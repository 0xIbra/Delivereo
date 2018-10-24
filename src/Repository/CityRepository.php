<?php

namespace App\Repository;

use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method City|null find($id, $lockMode = null, $lockVersion = null)
 * @method City|null findOneBy(array $criteria, array $orderBy = null)
 * @method City[]    findAll()
 * @method City[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, City::class);
    }

    public function findByName($search)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->where('c.name LIKE :name')
            ->setParameter('name', $search.'%');

        return $qb->getQuery()->getResult(Query::HYDRATE_OBJECT);
    }

    public function findByZipCode($zipCode)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->where('c.zipCode = :zip')
            ->setParameter('zip', $zipCode);

        return $qb->getQuery()->getResult(Query::HYDRATE_OBJECT);
    }

//    /**
//     * @return City[] Returns an array of City objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?City
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
