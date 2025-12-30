<?php

namespace App\Repository;

use App\Entity\LivrerReste;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LivrerReste|null find($id, $lockMode = null, $lockVersion = null)
 * @method LivrerReste|null findOneBy(array $criteria, array $orderBy = null)
 * @method LivrerReste[]    findAll()
 * @method LivrerReste[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LivrerResteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LivrerReste::class);
    }

    public function avoir_reste($commande)
    {
        $query = $this->createQueryBuilder('p');
        $query->where('p.commande = :commande')
            ->setParameter('commande', $commande)
//            ->AndWhere('p.produit = :produit')
//            ->setParameter('produit', $produit)
//            ->AndWhere($query->expr()->in('p.produit', $produits))
            ->getQuery()
            ->getResult()
        ;
    }

    public function Annuelle(int $year): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.date >= :start')
            ->andWhere('c.date < :end')
            ->AndWhere('c.credit = :credit')
            ->setParameter('start', new \DateTimeImmutable("$year-01-01"))
            ->setParameter('end', new \DateTimeImmutable(($year + 1) . "-01-01"))
            ->setParameter('credit', false)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return LivrerReste[] Returns an array of LivrerReste objects
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
    public function findOneBySomeField($value): ?LivrerReste
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
