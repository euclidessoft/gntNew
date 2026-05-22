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
        // date_sub($date,date_interval_create_from_date_string("7 days"));
        date_sub($date,date_interval_create_from_date_string("180 days"));
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
        // $date = new \Datetime();
        // date_sub($date,date_interval_create_from_date_string("30 days"));
        // $creation = date_format($date,"Y-m-d");



        $query = $this->createQueryBuilder('a')
            // ->Where('a.dateefectlivraison > :date')
            // ->setParameter('date', $creation)
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
            ->andWhere('a.traitement >= :start')
            ->andWhere('a.traitement < :end')
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
            ->andWhere('c.traitement >= :start')
            ->andWhere('c.traitement < :end')
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

     public function premiertranche($user, $mois)
    {
         $date = $mois."-01  00:00:00";
         $startDate = new \DateTime($date);
         $date = $mois."-15  23:59:59";
        $endDate = new \DateTime($date);
        return $this->createQueryBuilder('p')
            ->where('p.user = :id')
            ->andWhere('p.date BETWEEN :start AND :end')
            ->andWhere('p.credit = :credit')
            ->andWhere('p.payer = :payer')
            ->setParameter('id' , $user)
            ->setParameter('credit' , true)
            ->setParameter('payer' , false)
            ->setParameter('start' , $startDate)
            ->setParameter('end' , $endDate)
            ->orderBy('p.date', "DESC")
            ->getQuery()
            ->getResult();
    }

     public function commandepremiertranche($mois)
    {
         $date = $mois."-01  00:00:00";
         $startDate = new \DateTime($date);
         $date = $mois."-15  23:59:59";
        $endDate = new \DateTime($date);
        return $this->createQueryBuilder('p')
            // ->where('p.user = :id')
            ->andWhere('p.date BETWEEN :start AND :end')
            ->andWhere('p.credit = :credit')
            ->andWhere('p.payer = :payer')
            // ->setParameter('id' , $user)
            ->setParameter('credit' , true)
            ->setParameter('payer' , false)
            ->setParameter('start' , $startDate)
            ->setParameter('end' , $endDate)
            ->groupBy('p.user')
            ->orderBy('p.date', "DESC")
            ->getQuery()
            ->getResult();
    }
      public function deuxiemetranche($user, $mois)
    {
         $date = $mois."-15  00:00:00";
         $startDate = new \DateTime($date);
         $date = $mois."-15";
        $endDate = new \DateTime($date);
        $dernierjour = $endDate->format('t');
        $date = $mois."-".$dernierjour."  23:59:59";
        $endDate = new \DateTime($date);
        return $this->createQueryBuilder('p')
            ->where('p.user = :id')
            ->andWhere('p.date BETWEEN :start AND :end')
            ->andWhere('p.credit = :credit')
            ->andWhere('p.payer = :payer')
            ->setParameter('id' , $user)
            ->setParameter('credit' , true)
            ->setParameter('payer' , false)
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
            // ->where('p.user = :id')
            ->andWhere('p.date BETWEEN :start AND :end')
            ->andWhere('p.credit = :credit')
            ->andWhere('p.payer = :payer')
            // ->setParameter('id' , $user)
            ->setParameter('credit' , true)
            ->setParameter('payer' , false)
            ->setParameter('start' , $startDate)
            ->setParameter('end' , $endDate)
            ->groupBy('p.user')
            ->orderBy('p.date', "DESC")
            ->getQuery()
            ->getResult();
    }

     public function journalier($user): array
    {
        $date = new \Datetime();
        $debut = (clone $date)->setTime(0, 0, 0);
        $fin = (clone $date)->setTime(23, 59, 59);

        return $this->createQueryBuilder('c')
            ->andWhere('c.date BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

     public function clientjournalier($user, $jour): array
    {
         $date = new \Datetime($jour);
        $debut = (clone $date)->setTime(0, 0, 0);
        $fin = (clone $date)->setTime(23, 59, 59);

        return $this->createQueryBuilder('c')
            ->andWhere('c.date BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

     public function plage($client, $date1, $date2): array
    {
        $date = new \Datetime($date1);
        $debut = (clone $date)->setTime(0, 0, 0);

        $date = new \Datetime($date2);
        $fin = (clone $date)->setTime(23, 59, 59);

        return $this->createQueryBuilder('c')
            ->andWhere('c.date BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->andWhere('c.user = :user')
            ->setParameter('user', $client)
            ->orderBy('c.id',"DESC")
            ->getQuery()
            ->getResult();
    }

    public function avantage($p1, $p2){
        $date = new \Datetime($p1);
        $debut = (clone $date)->setTime(0, 0, 0);

        $date = new \Datetime($p2);
        $fin = (clone $date)->setTime(23, 59, 59);
        $qb = $this->createQueryBuilder('c')
        ->Addselect("u.id, SUM(c.Montant) as ca") 
        ->addSelect("
            SUM(CASE 
                WHEN c.credit = :espece THEN c.Montant 
                ELSE 0 
            END) AS achat
        ")
        ->join("c.user","u")
        ->andWhere('c.traitement BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->setParameter('espece', false)
        // ->join('c.achats', 'a')
        ->groupBy('u.id')
        ->orderBy('u.id');

        return $qb->getQuery()->getResult();
    }


    public function historiqueclient($client, $p1, $p2){
        $date = new \Datetime($p1);
        $debut = (clone $date)->setTime(0, 0, 0);

        $date = new \Datetime($p2);
        $fin = (clone $date)->setTime(23, 59, 59);
        $qb = $this->createQueryBuilder('c')
        ->andWhere('c.date BETWEEN :debut AND :fin')
        ->andWhere('c.user = :user')
            ->setParameter('user', $client)
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin);

        return $qb->getQuery()->getResult();
    }

     public function historiquecompteclient($client){
       
        $qb = $this->createQueryBuilder('c')
        ->andWhere('c.user = :user')
            ->setParameter('user', $client);

        return $qb->getQuery()->getResult();
    }

      public function precedent()
    {
         $date = "2026-04-14  23:59:59";
         $startDate = new \DateTime($date);
        return $this->createQueryBuilder('p')
            // ->where('p.user = :id')
            ->andWhere('p.traitement <= :start')
            ->andWhere('p.credit = :credit')
            ->andWhere('p.payer = :payer')
            // ->setParameter('id' , $user)
            ->setParameter('credit' , true)
            ->setParameter('payer' , false)
            ->setParameter('start' , $startDate)
            ->groupBy('p.user')
            ->orderBy('p.date', "DESC")
            ->getQuery()
            ->getResult();
    }
       public function clientprecedent($user)
    {
         $date = "2026-04-14  23:59:59";
         $startDate = new \DateTime($date);
        return $this->createQueryBuilder('p')
            ->where('p.user = :id')
            ->andWhere('p.traitement <= :start')
            ->andWhere('p.credit = :credit')
            ->andWhere('p.payer = :payer')
            ->setParameter('id' , $user)
            ->setParameter('credit' , true)
            ->setParameter('payer' , false)
            ->setParameter('start' , $startDate)
            ->orderBy('p.date', "DESC")
            ->getQuery()
            ->getResult();
    }

    public function VentesProduitParMois($produit)
    {
        return $this->createQueryBuilder('c')
            ->select("DATE_FORMAT(c.traitement, '%Y-%m') as mois")
            ->addSelect('SUM(l.quantite) as quantite')
            ->join('c.lignes', 'l')
            ->where('l.produit = :produit')
            ->AndWhere('c.suivi = :suivi')
            ->setParameter('produit', $produit)
            ->setParameter('suivi', true)
            ->groupBy('mois')
            ->orderBy('mois', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //  public function deuxiemetranche($user)
    // {
    //      $endDate = new \DateTime('last day of this month 23:59:59');
    //      $date = date("Y")."-".date("m")."-01  00:00:00";
    //     $startDate = new \DateTime($date);
    //     return $this->createQueryBuilder('p')
    //         ->where('p.user = :id')
    //         ->andWhere('p.traitement BETWEEN :start AND :end')
    //         ->setParameter('id' , $user)
    //         ->setParameter('start' , $startDate)
    //         ->setParameter('end' , $endDate)
    //         ->orderBy('p.date', "DESC")
    //         ->getQuery()
    //         ->getResult();
    // }


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
