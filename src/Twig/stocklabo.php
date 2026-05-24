<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Entity\LivrerProduit;
use App\Entity\Approvisionnement;
use App\Entity\Produit;

use Doctrine\ORM\EntityManagerInterface;

class stocklabo extends AbstractExtension
{
      public function __construct(private EntityManagerInterface $entityManager)
    {
    }
    public function getFunctions(): array
    {
        return [
            new TwigFunction('stocklabo', [$this, 'labo']),
        ];
    }

    public function labo(Produit $produit, $mois)
    {
        $depart = 0;
        $appros = $this->entityManager->getRepository(Approvisionnement::class)->findBy(['produit' => $produit]);
        foreach($appros as $appro){
            $depart += $appro->getQuantite();

        }

         $debut = 0;
         $departs = $this->entityManager->getRepository(LivrerProduit::class)->departmois($produit, $mois);
         foreach($departs as $dep){
             $debut += $dep->getQuantitelivrer();

         }

         $vente = 0;
         $soldes = $this->entityManager->getRepository(LivrerProduit::class)->mois($produit, $mois);
          foreach($soldes as $solde){
             $vente += $solde->getQuantitelivrer();

         }

         return ['depart' => $depart, 'debut' => $depart - $debut, 'vente' => $vente];
    }
}