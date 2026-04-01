<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Commande|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commande|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commande[]    findAll()
 * @method Commande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    public function current($user)
    {
        // On passe par le QueryBuilder vide de l'EntityManager pour l'exemple
        $query = $this ->createQueryBuilder('a')
            ->Where('a.user = :user')
            ->setParameter('user', $user)
            ->AndWhere('a.suivi = :suivi')
            ->setParameter('suivi', true)
        ;
        return $query;
    }  

    public function extranet($user)
    {
        // On passe par le QueryBuilder vide de l'EntityManager pour l'exemple
        $query = $this ->createQueryBuilder('a')
            ->Where('a.user = :user')
            ->setParameter('user', $user)
            ->AndWhere('a.suivi = :suivi')
            ->setParameter('suivi', 0)
            ->AndWhere('a.admin != :admin')
            ->setParameter('admin', "")
        ;
        return $query->getQuery()
            ->getResult();
    }

    public function historique_livreur($livreur)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.livreur = :id')
            ->setParameter('id', $livreur)
            ->andWhere('l.livrer = :livrer')
            ->setParameter('livrer', true)
            ->getQuery()
            ->getResult()
            ;
    }

    public function retour()
    {
        $date = new \Datetime();
        date_sub($date,date_interval_create_from_date_string("7 days"));
        $creation = date_format($date,"Y-m-d");



        $query = $this->createQueryBuilder('a')
            ->Where('a.dateefectlivraison > :date')
            ->setParameter('date', $creation)
            ->Andwhere('a.livrer = :val')
            ->setParameter('val', true)
            ->Andwhere('a.retour = :retour')
            ->setParameter('retour', false)
            ->getQuery();
        return $query->getResult();
    }

     public function retourClient($client)
    {
        $date = new \Datetime();
        date_sub($date,date_interval_create_from_date_string("30 days"));
        $creation = date_format($date,"Y-m-d");



        $query = $this->createQueryBuilder('a')
            ->Where('a.dateefectlivraison > :date')
            ->setParameter('date', $creation)
            ->Andwhere('a.livrer = :val')
            ->setParameter('val', true)
            ->Andwhere('a.user = :client')
            ->setParameter('client', $client)
            ->getQuery();
        return $query->getResult();
    }

    public function creditAvance(int $year)
    {
        // achat a credit non livrer avec avance recu
        $query = $this ->createQueryBuilder('a')
            ->Where('a.payer = :payer')
            ->AndWhere('a.suivi = :suivi')
            ->AndWhere('a.livraison = :livraison')
            ->AndWhere('a.credit = :credit')
            ->AndWhere('a.versement > :versement')
            ->andWhere('a.date >= :start')
            ->andWhere('a.date < :end')
            ->setParameter('start', new \DateTimeImmutable("$year-01-01"))
            ->setParameter('end', new \DateTimeImmutable(($year + 1) . "-01-01"))
            ->setParameter('payer', false)
            ->setParameter('suivi', true)
            ->setParameter('livraison', false)
            ->setParameter('credit', true)
            ->setParameter('versement', 0)
        ;
        return $query->getQuery()
            ->getResult();
    }

    public function Annuelle(int $year): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.date >= :start')
            ->andWhere('c.date < :end')
            ->andWhere('c.payer = :payer')
            ->AndWhere('c.suivi = :suivi')
            ->setParameter('start', new \DateTimeImmutable("$year-01-01"))
            ->setParameter('end', new \DateTimeImmutable(($year + 1) . "-01-01"))
            ->setParameter('payer', true)
            ->setParameter('suivi', true)
            ->getQuery()
            ->getResult();
    }
    public function avances(int $year): array
    {
        return $this->createQueryBuilder('c')
            // ->andWhere('c.date >= :start')
            ->andWhere('c.date < :end')
            ->andWhere('c.payer = :payer')
            ->AndWhere('c.suivi = :suivi')
            ->AndWhere('c.livraison = :livraison')
            // ->setParameter('start', new \DateTimeImmutable("$year-01-01"))
            ->setParameter('end', new \DateTimeImmutable(($year + 1) . "-01-01"))
            ->setParameter('payer', true)
            ->setParameter('suivi', true)
            ->setParameter('livraison', false)
            ->getQuery()
            ->getResult();
    }

    public function credits(int $year): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.date >= :start')
            ->andWhere('c.date < :end')
            ->andWhere('c.payer = :payer')
            ->AndWhere('c.suivi = :suivi')
            ->AndWhere('c.livraison = :livraison')
            ->AndWhere('c.credit = :credit')
            ->setParameter('start', new \DateTimeImmutable("$year-01-01"))
            ->setParameter('end', new \DateTimeImmutable(($year + 1) . "-01-01"))
            ->setParameter('payer', false)
            ->setParameter('suivi', true)
            ->setParameter('livraison', true)
            ->setParameter('credit', true)
            ->getQuery()
            ->getResult();
    }

     public function vente_client($client)
    {
        // achat a credit non livrer avec avance recu
        $query = $this ->createQueryBuilder('a')
            ->Where('a.user = :client')
            ->andWhere('a.suivi = :suivi')
            ->setParameter('suivi', true)
            ->setParameter('client', $client)
            ->orderBy("a.date","DESC")
        ;
        return $query->getQuery()
            ->getResult();
    }


    // /**
    //  * @return Commande[] Returns an array of Commande objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Commande
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
