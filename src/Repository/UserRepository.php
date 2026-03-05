<?php


namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
   
    public function client()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.client = :val')
            ->setParameter('val', true)
        ;
    } 
    public function pharmauser($pharmacie)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.pharmacie = :pharmacie')
            ->setParameter('pharmacie', $pharmacie)
        ;
    } 
    public function livreur()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.livreur = :val')
            ->setParameter('val', true)
        ;
    }

    
}
