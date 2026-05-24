<?php

namespace App\Repository;

use App\Entity\Approvisionnement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Approvisionnement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Approvisionnement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Approvisionnement[]    findAll()
 * @method Approvisionnement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApprovisionnementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Approvisionnement::class);
    }

    public function arrivage(array $appro)
    {
        $query = $this->createQueryBuilder('a');
        return $query->where($query->expr()->in('a.approvisionner', $appro))
            ->groupBy('a.produit')
            ->getQuery()
            ->getResult();
    }

    public function payer()
    {
         return $query = $this->createQueryBuilder('a')
            ->andWhere('a.regler = :con')
            ->setParameter('con' , false)
            ->groupBy('a.fournisseur')
            ->getQuery()
            ->getResult();
    }

    public function resultat(int $year)
    {
         $query = $this->createQueryBuilder('a');
           return $query->andWhere('a.fournisseur IS NOT NULL')
            ->andWhere('a.date >= :start')
            ->andWhere('a.date < :end')
            ->setParameter('start', new \DateTimeImmutable("$year-01-01"))
            ->setParameter('end', new \DateTimeImmutable(($year + 1) . "-01-01"))
            ->getQuery()
            ->getResult();
    }

    
     public function fournisseur($produit, $fournisseur, $date1, $date2): array
    {
        $date = new \Datetime($date1);
        $debut = (clone $date)->setTime(0, 0, 0);

        $date = new \Datetime($date2);
        $fin = (clone $date)->setTime(23, 59, 59);

        return $this->createQueryBuilder('c')
            ->andWhere('c.date BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->andWhere('c.fournisseur = :fournisseur')
            ->andWhere('c.produit = :produit')
            ->setParameter('fournisseur', $fournisseur)
            ->setParameter('produit', $produit)
            ->orderBy('c.id',"DESC")
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

    
	public function labo( array $pel)
	{	
		$query = $this->createQueryBuilder('a');
			$query->where($query->expr()->in('a.produit', $pel));
		return $query->getQuery()->execute();
	}

    // /**
    //  * @return Approvisionnement[] Returns an array of Approvisionnement objects
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
    public function findOneBySomeField($value): ?Approvisionnement
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
