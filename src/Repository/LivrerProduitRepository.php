<?php

namespace App\Repository;

use App\Entity\LivrerProduit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LivrerProduit|null find($id, $lockMode = null, $lockVersion = null)
 * @method LivrerProduit|null findOneBy(array $criteria, array $orderBy = null)
 * @method LivrerProduit[]    findAll()
 * @method LivrerProduit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LivrerProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LivrerProduit::class);
    }

    public function historique(array $appro)
    {
        $query = $this->createQueryBuilder('a');
        return $query->where($query->expr()->in('a.livrer', $appro))
            ->getQuery()
            ->getResult();
    }

      public function mouvement($produit, $date1, $date2): array
    {
        $debut = new \Datetime($date1);
        // $debut = (clone $date)->setTime(0, 0, 0);

        $fin = new \Datetime($date2);
        // $fin = (clone $date)->setTime(23, 59, 59);

        return $this->createQueryBuilder('c')
            ->andWhere('c.date BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->andWhere('c.produit = :produit')
            ->setParameter('produit', $produit)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return LivrerProduit[] Returns an array of LivrerProduit objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LivrerProduit
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
