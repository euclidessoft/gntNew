<?php

namespace App\Repository;

use App\Entity\Versement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Versement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Versement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Versement[]    findAll()
 * @method Versement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VersementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Versement::class);
    }

     public function balance($client, $p1, $p2){
        $debut = new \Datetime($p1);
        // $debut = (clone $date)->setTim;

        $fin = new \Datetime($p2);
        // $fin = (clone $date)->setTime(23, 59, 59);
        $qb = $this->createQueryBuilder('c')
        // ->Addselect("u.id, SUM(c.montant) as paie") 
        ->andWhere('c.date BETWEEN :debut AND :fin')
        ->andWhere('c.Client = :user')
            ->setParameter('user', $client)
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin);
            // ->setParameter('espece', false);
        // ->join('c.achats', 'a')
        // ->groupBy('u.id');
        // ->orderBy('mois', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function balancecompte($client){
       
        $qb = $this->createQueryBuilder('c')
        ->andWhere('c.Client = :user')
            ->setParameter('user', $client);
            // ->setParameter('espece', false);
        // ->join('c.achats', 'a')
        // ->groupBy('u.id');
        // ->orderBy('mois', 'DESC');

        return $qb->getQuery()->getResult();
    }

    // /**
    //  * @return Versement[] Returns an array of Versement objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Versement
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
