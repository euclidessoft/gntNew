<?php

namespace App\Repository;

use App\Entity\Avantage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Avantage>
 */
class AvantageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avantage::class);
    }

    public function periode()
    {
        // $debut = (new \DateTime($p1))->setTime(0, 0, 0);
        // $fin   = (new \DateTime($p2))->setTime(23, 59, 59);

        return $this->createQueryBuilder('c')
            // ->select("DATE_FORMAT(c.date, '%Y-%m') as mois, SUM(c.Montant) as totalAchats")
            // ->where('c.date BETWEEN :debut AND :fin')
            // ->setParameter('debut', $debut)
            // ->setParameter('fin', $fin)
            ->groupBy('c.debut','c.fin')
            // ->orderBy('mois', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    public function show($date)
    {
        $date = (new \DateTime($date));
        // $fin   = (new \DateTime($p2))->setTime(23, 59, 59);

        return $this->createQueryBuilder('c')
            // ->select("DATE_FORMAT(c.date, '%Y-%m') as mois, SUM(c.Montant) as totalAchats")
            ->where('c.date = :date')
            ->setParameter('date', $date)
            // ->setParameter('fin', $fin)
            // ->groupBy('c.debut','c.fin')
            // ->orderBy('mois', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Avantage[] Returns an array of Avantage objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Avantage
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
