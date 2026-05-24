<?php

namespace App\Repository;

use App\Entity\CommandeProduit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CommandeProduit|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommandeProduit|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommandeProduit[]    findAll()
 * @method CommandeProduit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandeProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandeProduit::class);
    }

    public function ventemensuel()
    {
//        $date = new \Datetime();
//        date_sub($date,date_interval_create_from_date_string("30 days"));
//        $creation = date_format($date,"Y-m-d");
//
        $date = date('Y') . "-" . date("m") . "-01";


        $query = $this->createQueryBuilder('a');
        $query->expr()->count('a.produit');
        $query->Join('a.commande', 'c', 'WITH', 'c.suivi = true')
            ->addSelect('c')
            ->Where('a.date BETWEEN :debut AND :fin')
            ->setParameter('debut', $date)
            ->setParameter('fin', date("Y-m-d"))
            ->groupBy('a.produit')
            ->orderBy($query->expr()->count('a.produit'), 'DESC');
        return $query->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    public function venteannuel()
    {
        $date = date('Y') . "-01-01";


        $query = $this->createQueryBuilder('a');
        $query->expr()->count('a.produit');
        $query->Join('a.commande', 'c', 'WITH', 'c.suivi = true')
            ->addSelect('c')
            ->Where('a.date BETWEEN :debut AND :fin')
            ->setParameter('debut', $date)
            ->setParameter('fin', date("Y-m-d"))
            ->groupBy('a.produit')
            ->orderBy($query->expr()->count('a.produit'), 'DESC');
        return $query->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }


    public function achatmensuel($user)
    {
//        $date = new \Datetime();
//        date_sub($date,date_interval_create_from_date_string("30 days"));
//        $creation = date_format($date,"Y-m-d");
//
        $date = date('Y') . "-" . date("m") . "-01";


        $query = $this->createQueryBuilder('a');
        $query->expr()->count('a.produit');
        $query->Join('a.commande', 'c', 'WITH', 'c.suivi = true')
            ->addSelect('c')
            ->Where('a.date BETWEEN :debut AND :fin')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->setParameter('debut', $date)
            ->setParameter('fin', date("Y-m-d"))
            ->groupBy('a.produit')
            ->orderBy($query->expr()->count('a.produit'), 'DESC');
        return $query->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    public function achatannuel($user)
    {
        $date = date('Y') . "-01-01";


        $query = $this->createQueryBuilder('a');
        $query->expr()->count('a.produit');
        $query->Join('a.commande', 'c', 'WITH', 'c.suivi = true')
            ->addSelect('c')
            ->Where('a.date BETWEEN :debut AND :fin')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->setParameter('debut', $date)
            ->setParameter('fin', date("Y-m-d"))
            ->groupBy('a.produit')
            ->orderBy($query->expr()->count('a.produit'), 'DESC');
        return $query->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    public function topmensuel($user)
    {
//        $date = new \Datetime();
//        date_sub($date,date_interval_create_from_date_string("30 days"));
//        $creation = date_format($date,"Y-m-d");
//
        $date = date('Y') . "-" . date("m") . "-01";


        $query = $this->createQueryBuilder('a');
        $query->expr()->count('a.produit');
        $query->Join('a.commande', 'c', 'WITH', 'c.suivi = true')
            ->addSelect('c')
            ->Where('a.date BETWEEN :debut AND :fin')
            ->setParameter('debut', $date)
            ->setParameter('fin', date("Y-m-d"))
            ->andWhere('c.user = :user')
            ->setParameter('user', $user->getId())
            ->groupBy('a.produit')
            ->orderBy($query->expr()->count('a.produit'), 'DESC');
        return $query->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }


    public function topannuel($user)
    {
//        $date = new \Datetime();
//        date_sub($date,date_interval_create_from_date_string("30 days"));
//        $creation = date_format($date,"Y-m-d");
//
        $date = date('Y') . "-01-01";


        $query = $this->createQueryBuilder('a');
        $query->expr()->count('a.produit');
        $query->Join('a.commande', 'c', 'WITH', 'c.suivi = true')
            ->addSelect('c')
            ->Where('a.date BETWEEN :debut AND :fin')
            ->setParameter('debut', $date)
            ->setParameter('fin', date("Y-m-d"))
            ->andWhere('c.user = :user')
            ->setParameter('user', $user->getId())
            ->groupBy('a.produit')
            ->orderBy($query->expr()->count('a.produit'), 'DESC');
        return $query->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    
    public function article_client_show($client,$produit)
    {//palmares article par client
        return $this->createQueryBuilder('p')
            ->Join('p.commande', 'c')
            ->Where('c.user = :client')
            ->andWhere('p.produit = :produit')
            ->setParameter('client', $client)
            ->andWhere('c.suivi = :suivi')
            ->setParameter('suivi', true)
            ->setParameter('produit', $produit)
            ->orderBy("c.date", "DESC")
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
    //  * @return CommandeProduit[] Returns an array of CommandeProduit objects
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
    public function findOneBySomeField($value): ?CommandeProduit
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
