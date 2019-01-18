<?php
/**
 * Created by PhpStorm.
 * User: ibrahim
 * Date: 27/11/18
 * Time: 21:17
 */

namespace App\Repository;


use App\Entity\Restaurant;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class UserRepository extends EntityRepository
{

    public function countConsumers()
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select('COUNT(u)')
            ->where("u.roles LIKE '%ROLE_CONSUMER%'");
        return $qb->getQuery()->getSingleResult();
    }


    public function getConsumersAndOwners($limit = null)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->where("u.roles LIKE '%ROLE_CONSUMER%'")
            ->andWhere("u.roles LIKE '%ROLE_OWNER%'");
        if ($limit !== null)
        {
            $qb->setMaxResults($limit);
        }
        return $qb->getQuery()->getResult();
    }


    public function findByDateRange(\DateTime $dateTime)
    {
        $now = new \DateTime();
        $qb = $this->createQueryBuilder('u');
        $qb->where("u.roles LIKE '%ROLE_CONSUMER%' OR '%ROLE_OWNER%'")
            ->andWhere('u.createdAt BETWEEN :date AND :now')
            ->setParameter('date', $dateTime)
            ->setParameter('now', $now)
            ->orderBy('u.createdAt', 'DESC');
        return $qb->getQuery()->getResult();
    }


    public function countByDate(\DateTime $dateTime)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select('COUNT(u)')
            ->where("u.roles LIKE '%ROLE_CONSUMER%' OR u.roles LIKE '%ROLE_OWNER%'")
            ->andWhere('u.createdAt LIKE :date')
            ->setParameter('date', '%'. $dateTime->format('Y-m-d') .'%');
        return $qb->getQuery()->getOneOrNullResult();
    }


    public function countRestaurantClients(Restaurant $restaurant)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select('DISTINCT COUNT(u.id)')
            ->join('u.orders', 'o')
            ->join('o.restaurants', 'r')
            ->where('r.id = :restaurant')
            ->setParameter('restaurant', $restaurant->getId());
        return $qb->getQuery()->getOneOrNullResult();
    }


    public function recentUsers($limit = null)
    {
        $recent = new \DateTime('12 hours ago');
        $qb = $this->createQueryBuilder('u');
        $qb->select('u')
            ->where("u.roles LIKE '%ROLE_CONSUMER%'")
            ->andWhere('u.createdAt > :time')
            ->setParameter('time', $recent->format('Y-m-d H:i'))
            ->orderBy('u.createdAt', 'DESC');
        if ($limit !== null)
        {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult(Query::HYDRATE_OBJECT);
    }

}