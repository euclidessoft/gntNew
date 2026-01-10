<?php

namespace App\Repository;

use App\Entity\Ecriture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Ecriture|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ecriture|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ecriture[]    findAll()
 * @method Ecriture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EcritureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ecriture::class);
    }


    public function ouverture()
    {
        $query = $this->createQueryBuilder('a')
            ->Where('a.date < :genre')
            ->setParameter('genre', date('Y-m-d'))
            ->getQuery();
        return $query->getResult();
    }

    public function ouverturecaisse()
    {
        $query = $this->createQueryBuilder('a')
            ->Where('a.date < :genre')
            ->setParameter('genre', date('Y-m-d'))
            ->andWhere('a.type = :caisse')
            ->setParameter('caisse', "Espece")
            ->getQuery();
        return $query->getResult();
    }
    public function ouverturebanque()
    {
        $query = $this->createQueryBuilder('a')
            ->Where('a.date < :genre')
            ->setParameter('genre', date('Y-m-d'))
            ->andWhere('a.type = :caisse')
            ->setParameter('caisse', "Banque")
            ->getQuery();
        return $query->getResult();
    }
    public function brouyard()
    {
        $query = $this->createQueryBuilder('a')
            ->Where('a.date = :genre')
            ->setParameter('genre', date('Y-m-d'))
            ->getQuery();
        return $query->getResult();
    }
    public function brouyardcaisse()
    {
        $query = $this->createQueryBuilder('a')
            ->Where('a.date = :genre')
            ->setParameter('genre', date('Y-m-d'))
            ->andWhere('a.type = :caisse')
            ->setParameter('caisse', "Espece")
            ->getQuery();
        return $query->getResult();
    }
    public function brouyardbanque()
    {
        $query = $this->createQueryBuilder('a')
            ->Where('a.date = :genre')
            ->setParameter('genre', date('Y-m-d'))
            ->andWhere('a.type = :banque')
            ->setParameter('banque', "Banque")
            ->getQuery();
        return $query->getResult();
    }
    public function daybrouyard($date)
    {
        $query = $this->createQueryBuilder('a')
            ->Where('a.date = :genre')
            ->setParameter('genre', $date)
            ->getQuery();
        return $query->getResult();
    }

    public function daybrouyardcaisse($date)
    {
        $query = $this->createQueryBuilder('a')
            ->Where('a.date = :genre')
            ->setParameter('genre', $date)
            ->andWhere('a.type = :caisse')
            ->setParameter('caisse', "Espece")
            ->getQuery();
        return $query->getResult();
    }

    public function daybrouyardbanque($date)
    {
        $query = $this->createQueryBuilder('a')
            ->Where('a.date = :genre')
            ->setParameter('genre', $date)
            ->andWhere('a.type = :banque')
            ->setParameter('banque', "Banque")
            ->getQuery();
        return $query->getResult();
    }

    public function ouvertureplace($datedebut)
    {
        $query = $this->createQueryBuilder('a')
            ->Where('a.date < :genre')
            ->setParameter('genre', $datedebut)
            ->getQuery();
        return $query->getResult();
    }
    public function ouvertureplacecaisse($datedebut)
    {
        $query = $this->createQueryBuilder('a')
            ->Where('a.date < :genre')
            ->setParameter('genre', $datedebut)
            ->andWhere('a.type = :caisse')
            ->setParameter('caisse', "Espece")
            ->getQuery();
        return $query->getResult();
    }

    public function ouvertureplacebanque($datedebut)
    {
        $query = $this->createQueryBuilder('a')
            ->Where('a.date < :genre')
            ->setParameter('genre', $datedebut)
            ->andWhere('a.type = :caisse')
            ->setParameter('caisse', "Banque")
            ->getQuery();
        return $query->getResult();
    }

    public function plage($date, $date1)
    {
        $query = $this->createQueryBuilder('a')
            ->Where('a.date BETWEEN :debut AND :fin')
            ->setParameter('debut', $date)
            ->setParameter('fin', $date1)
            ->getQuery();
        return $query->getResult();
    }

    public function plagecaisse($date, $date1)
    {
        $query = $this->createQueryBuilder('a')
            ->Where('a.date BETWEEN :debut AND :fin')
            ->setParameter('debut', $date)
            ->setParameter('fin', $date1)
            ->andWhere('a.type = :caisse')
            ->setParameter('caisse', "Espece")
            ->getQuery();
        return $query->getResult();
    }

    public function plagebanque($date, $date1)
    {
        $query = $this->createQueryBuilder('a')
            ->Where('a.date BETWEEN :debut AND :fin')
            ->setParameter('debut', $date)
            ->setParameter('fin', $date1)
            ->andWhere('a.type = :caisse')
            ->setParameter('caisse', "Banque")
            ->getQuery();
        return $query->getResult();
    }
     public function journalAnnuelle(int $year): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.date >= :start')
            ->andWhere('c.date < :end')
            ->setParameter('start', new \DateTimeImmutable("$year-01-01"))
            ->setParameter('end', new \DateTimeImmutable(($year + 1) . "-01-01"))
            ->getQuery()
            ->getResult();
    }

     public function Annuelle(int $year): array
    {
        return $this->createQueryBuilder('c')
            // ->andWhere('c.date >= :start')
            ->andWhere('c.date < :end')
            // ->setParameter('start', new \DateTimeImmutable("$year-01-01"))
            ->setParameter('end', new \DateTimeImmutable(($year + 1) . "-01-01"))
            ->getQuery()
            ->getResult();
    }
}
