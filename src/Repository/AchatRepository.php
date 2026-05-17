<?php

namespace App\Repository;

use App\Entity\Achat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Achat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Achat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Achat[]    findAll()
 * @method Achat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AchatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Achat::class);
    }

    public function balance($fournisseur, $p1, $p2){
        $debut = new \Datetime($p1);
        // $debut = (clone $date)->setTim;

        $fin = new \Datetime($p2);
        // $fin = (clone $date)->setTime(23, 59, 59);
        $qb = $this->createQueryBuilder('c')
        // ->Addselect("u.id, SUM(c.montant) as paie") 
        ->andWhere('c.date BETWEEN :debut AND :fin')
        ->andWhere('c.fournisseur = :user')
            ->setParameter('user', $fournisseur)
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin);
            // ->setParameter('espece', false);
        // ->join('c.achats', 'a')
        // ->groupBy('u.id');
        // ->orderBy('mois', 'DESC');

        return $qb->getQuery()->getResult();
    }

    // /**
    //  * @return Achat[] Returns an array of Achat objects
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
    public function findOneBySomeField($value): ?Achat
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
