<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Entity\LivrerProduit;
use App\Entity\Approvisionnement;
use App\Entity\Produit;

use Doctrine\ORM\EntityManagerInterface;

class mouvement extends AbstractExtension
{
      public function __construct(private EntityManagerInterface $entityManager)
    {
    }
    public function getFunctions(): array
    {
        return [
            new TwigFunction('mouvement', [$this, 'stock']),
        ];
    }

    public function stock(Produit $produit, $date1, $date2)
    {
        $livraison = $this->entityManager->getRepository(LivrerProduit::class)->mouvement($produit, $date1, $date2);
        $reappro = $this->entityManager->getRepository(Approvisionnement::class)->mouvement($produit, $date1, $date2);
            $result = [];
            foreach ([$reappro,$livraison] as $tableau) {
                foreach ($tableau as $row) {
                    $date = $row->getDate()->format('Y-m-d');
                    // dd($date);
                    // On regroupe les lignes par date
                    $result[$date][] = $row;
                }
            }
            ksort($result);
            // dd($result);
            $flat = [];

            foreach ($result as $date => $rows) {
                foreach ($rows as $row) {
                    $flat[] = $row;
                }
            } 

        return $flat;
    }
}