<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Entity\Panier;
use App\Entity\CommandeProduit;
use App\Entity\Credit;
use App\Entity\Ecriture;
use App\Entity\Paiement;
use App\Entity\User;
use App\Entity\Client;
use App\Entity\Versement;
use App\Form\CommandeType;
use App\Form\PaiementFormType;
use App\Form\VersementType;
use App\Repository\CommandeProduitRepository;
use App\Repository\LivrerProduitRepository;
use App\Repository\CommandeRepository;
use App\Repository\PaiementRepository;
use App\Repository\ProduitRepository;
use App\Repository\StockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;


#[Route("/{_locale}/Palmares_", name :"vente_") ]
class VenteController extends AbstractController
{
      public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

    #[Route("Articles/", name :"article") ]
    public function sortie(ProduitRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {

            $response = $this->render('vente/articles.html.twig', [
                'produits' => $repository->vente_article(),
            ]);
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;
        } else {
            $response = $this->redirectToRoute('security_logout');
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;
        }
    }

    

    #[Route("/Article/{id}", name :"show", methods : ["GET"]) ]
    public function produithistory(Produit $produit, CommandeProduitRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_STOCK') || $this->security->isGranted('ROLE_FINANCE')) {
            $ventes = $repository->findBy(['produit' => $produit],['date' => "DESC"]);
            $quantite = 0;
            $montant = 0;
            foreach($ventes as $vente){
                $quantite += $vente->getQuantite();
                $montant += $vente->getSession() * $vente->getQuantite();
            }
            $response = $this->render('vente/vente_show.html.twig', [
                'ventes' => $ventes,
                'produit' => $produit,
                'quantite' => $quantite,
                'montant' => $montant,
            ]);
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;
        } else {
            $response = $this->redirectToRoute('security_logout');
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;
        }
    }




}
