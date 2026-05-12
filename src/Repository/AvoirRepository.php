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

    
    public function Annuelle(int $year): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.date >= :start')
            ->andWhere('c.date < :end')
            ->setParameter('start', new \DateTimeImmutable("$year-01-01"))
            ->setParameter('end', new \DateTimeImmutable(($year + 1) . "-01-01"))
            ->getQuery()
            ->getResult();
    }

       public function premiertranche($client, $mois)
    {
         $date = $mois."-01  00:00:00";
         $startDate = new \DateTime($date);
         $date = $mois."-15  23:59:59";
        $endDate = new \DateTime($date);
        return $this->createQueryBuilder('p')
            ->where('p.client = :id')
            ->andWhere('p.date BETWEEN :start AND :end')
            // ->andWhere('p.payer = :payer')
            ->setParameter('id' , $client)
            // ->setParameter('payer' , false)
            ->setParameter('start' , $startDate)
            ->setParameter('end' , $endDate)
            ->orderBy('p.date', "DESC")
            ->getQuery()
            ->getResult();
    }

     public function commandepremiertranche( $mois)
    {
         $date = $mois."-01  00:00:00";
         $startDate = new \DateTime($date);
         $date = $mois."-15  23:59:59";
        $endDate = new \DateTime($date);
        return $this->createQueryBuilder('p')
            // ->where('p.client = :id')
            ->andWhere('p.traitement BETWEEN :start AND :end')
            ->andWhere('p.credit = :credit')
            ->andWhere('p.payer = :payer')
            // ->setParameter('id' , $client)
            ->setParameter('credit' , true)
            ->setParameter('payer' , false)
            ->setParameter('start' , $startDate)
            ->setParameter('end' , $endDate)
            ->groupBy('p.client')
            ->orderBy('p.date', "DESC")
            ->getQuery()
            ->getResult();
    }
      public function deuxiemetranche($client, $mois)
    {
         $date = $mois."-15  00:00:00";
         $startDate = new \DateTime($date);
         $date = $mois."-15";
        $endDate = new \DateTime($date);
        $dernierjour = $endDate->format('t');
        $date = $mois."-".$dernierjour."  23:59:59";
        $endDate = new \DateTime($date);
        return $this->createQueryBuilder('p')
            ->where('p.client = :id')
            ->andWhere('p.date BETWEEN :start AND :end')
            // ->andWhere('p.payer = :payer')
            ->setParameter('id' , $client)
            // ->setParameter('payer' , false)
            ->setParameter('start' , $startDate)
            ->setParameter('end' , $endDate)
            ->orderBy('p.date', "DESC")
            ->getQuery()
            ->getResult();
    }

      public function commandedeuxiemetranche( $mois)
    {
         $date = $mois."-15  00:00:00";
         $startDate = new \DateTime($date);
         $date = $mois."-15";
        $endDate = new \DateTime($date);
        $dernierjour = $endDate->format('t');
        $date = $mois."-".$dernierjour."  23:59:59";
        $endDate = new \DateTime($date);
        return $this->createQueryBuilder('p')
            // ->where('p.client = :id')
            ->andWhere('p.traitement BETWEEN :start AND :end')
            ->andWhere('p.credit = :credit')
            ->andWhere('p.payer = :payer')
            // ->setParameter('id' , $client)
            ->setParameter('credit' , true)
            ->setParameter('payer' , false)
            ->setParameter('start' , $startDate)
            ->setParameter('end' , $endDate)
            ->groupBy('p.client')
            ->orderBy('p.date', "DESC")
            ->getQuery()
            ->getResult();
    }

     public function clientprecedent($client)
    {
         
         $date = "2026-04-14  23:59:59";
         $startDate = new \DateTime($date);
        return $this->createQueryBuilder('p')
            ->where('p.client = :id')
            ->andWhere('p.date <= :start')
            // ->andWhere('p.payer = :payer')
            ->setParameter('id' , $client)
            // ->setParameter('payer' , false)
            ->setParameter('start' , $startDate)
            ->orderBy('p.date', "DESC")
            ->getQuery()
            ->getResult();
    }

     public function avantage($client, $p1, $p2){
        $date = new \Datetime($p1);
        $debut = (clone $date)->setTime(0, 0, 0);

        $date = new \Datetime($p2);
        $fin = (clone $date)->setTime(23, 59, 59);
        $qb = $this->createQueryBuilder('c')
       
        ->andWhere('c.date BETWEEN :debut AND :fin')
        ->andWhere('c.client = :client')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->setParameter('client', $client);

        return $qb->getQuery()->getResult();
    }

     public function balance($client, $p1, $p2){
        $debut = new \Datetime($p1);
        // $debut = (clone $date)->setTim;

        $fin = new \Datetime($p2);
        // $fin = (clone $date)->setTime(23, 59, 59);
        $qb = $this->createQueryBuilder('c')
        // ->Addselect("u.id, SUM(c.montant) as paie") 
        ->andWhere('c.date BETWEEN :debut AND :fin')
        ->andWhere('c.client = :user')
            ->setParameter('user', $client)
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin);
            // ->setParameter('espece', false);
        // ->join('c.achats', 'a')
        // ->groupBy('u.id');
        // ->orderBy('mois', 'DESC');

        return $qb->getQuery()->getResult();
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
