<?php

namespace App\Repository;

use App\Entity\Avoir;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Avoir|null find($id, $lockMode = null, $lockVersion = null)
 * @method Avoir|null findOneBy(array $criteria, array $orderBy = null)
 * @method Avoir[]    findAll()
 * @method Avoir[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AvoirRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avoir::class);
    }

     public function Annuellepassee(int $year): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.rebourser = :rembourser')
            ->andWhere('c.date <= :end')
            ->setParameter('rembourser', false)
            ->setParameter('end', new \DateTimeImmutable(($year - 1) . "-12-31"))
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Avoir[] Returns an array of Avoir objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Avoir
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
