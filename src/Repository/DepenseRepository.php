<?php

namespace App\Repository;

use App\Entity\Depense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Depense|null find($id, $lockMode = null, $lockVersion = null)
 * @method Depense|null findOneBy(array $criteria, array $orderBy = null)
 * @method Depense[]    findAll()
 * @method Depense[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Depense::class);
    }

    // /**
    //  * @return Depense[] Returns an array of Depense objects
    //  */
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
    public function findOneBySomeField($value): ?Depense
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function compteResultat($annee)
    {
        $start = new \DateTime("$annee-01-01");
        $end = new \DateTime("$annee-12-31");
        return $this->createQueryBuilder('p')
            ->andWhere('p.date BETWEEN :start AND :end')
            ->setParameter('start' , $start)
            ->setParameter('end' , $end)
            ->getQuery()
            ->getResult();
    }

    // src/Repository/DepenseRepository.php

public function immobilisation(): array
{
    return $this->createQueryBuilder('d')
        ->innerJoin('d.categorie', 'c')
        ->andWhere('c.amortissement IS NOT NULL')
        ->orWhere('c.compte LIKE :start')
        ->setParameter('start', '22%')
        // ->orderBy('d.date', 'DESC')
        ->getQuery()
        ->getResult();
}

}
