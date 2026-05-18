<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Entity\Fournisseur;
use App\Entity\Approvisionnement;
use App\Entity\Produit;

use Doctrine\ORM\EntityManagerInterface;

class fournisseurExtension extends AbstractExtension
{
      public function __construct(private EntityManagerInterface $entityManager)
    {
    }
    public function getFunctions(): array
    {
        return [
            new TwigFunction('AnalyseFournisseur', [$this, 'analyse']),
        ];
    }

    public function analyse(Produit $produit, Fournisseur $fournisseur, $date1, $date2)
    {
        $quantite = 0;
        $ca = 0;
        $appros = $this->entityManager->getrepository(Approvisionnement::class)->fournisseur($produit, $fournisseur, $date1, $date2);
        foreach($appros as $appro){
            $quantite += $appro->getQuantite();
            $ca += ($appro->getQuantite() * $appro->getPght());

        }

        return ['ca' => $ca, 'quantite' => $quantite];
    }
}