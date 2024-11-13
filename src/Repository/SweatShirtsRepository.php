<?php

namespace App\Repository;

use App\Entity\SweatShirts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SweatShirts>
 */
class SweatShirtsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SweatShirts::class);
    }

     // Method to fetch all products
     public function findAllProducts()
     {
         return $this->findBy([], ['name' => 'ASC']);
     }

    public function findFeaturedSweatshirts()
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.featured = true')
            ->getQuery()
            ->getResult();
    }


    //    /**
    //     * @return SweatShirts[] Returns an array of SweatShirts objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?SweatShirts
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
