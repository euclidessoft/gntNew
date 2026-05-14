<?php

namespace App\Controller;

use App\Entity\Avoir;
use App\Entity\Commande;
use App\Entity\Produit;
use App\Entity\Panier;
use App\Entity\CommandeProduit;
use App\Entity\Credit;
use App\Entity\Ecriture;
use App\Entity\Paiement;
use App\Entity\User;
use App\Entity\Client;
use App\Entity\Fournisseur;
use App\Entity\Versement;
use App\Form\CommandeType;
use App\Form\PaiementFormType;
use App\Form\VersementType;
use App\Repository\CommandeProduitRepository;
use App\Repository\LivrerProduitRepository;
use App\Repository\CommandeRepository;
use App\Repository\ApprovisionnementRepository;
use App\Repository\FactureRepository;
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
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;


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
            $totalug = 0;
            foreach($ventes as $vente){
                $quantite += $vente->getQuantite();
                $montant += $vente->getSession() * $vente->getQuantite();
                 
                 $totalug += $vente->getUg();
            }
            $response = $this->render('vente/vente_show.html.twig', [
                'ventes' => $ventes,
                'produit' => $produit,
                'quantite' => $quantite,
                'montant' => $montant,
                'totalug' => $totalug,
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

    

    #[Route("/Article_pdf/{id}", name :"show_pdf", methods : ["GET"]) ]
    public function produithistorypdf(Produit $produit, CommandeProduitRepository $repository, GotenbergPdfInterface $gotenberg): Response
    {
        if ($this->security->isGranted('ROLE_STOCK') || $this->security->isGranted('ROLE_FINANCE')) {
            $ventes = $repository->findBy(['produit' => $produit],['date' => "DESC"]);
            $quantite = 0;
            $montant = 0;
            $totalug = 0;
            foreach($ventes as $vente){
                $quantite += $vente->getQuantite();
                $montant += $vente->getSession() * $vente->getQuantite();
                 
                    
                    $totalug += $vente->getUg();
            }

            return $gotenberg
        ->html()
        ->content('vente/vente_showpdf.html.twig', [
                'ventes' => $ventes,
                'produit' => $produit,
                'quantite' => $quantite,
                'montant' => $montant,
                'totalug' => $totalug,
            ])
        ->fileName('palmares.pdf')
        ->generate()
        ->stream();
            
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
    
    #[Route("/Article/{client}/{id}", name :"client_show", methods : ["GET"]) ]
    public function produitclienthistory(Client $client, Produit $produit, CommandeProduitRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_STOCK') || $this->security->isGranted('ROLE_FINANCE')) {
            $ventes = $repository->article_client_show($client->getid(),$produit->getId());
            $quantite = 0;
            $montant = 0;
            foreach($ventes as $vente){
                $quantite += $vente->getQuantite();
                $montant += $vente->getSession() * $vente->getQuantite();
            }
            $response = $this->render('vente/vente_client_show.html.twig', [
                'ventes' => $ventes,
                'produit' => $produit,
                'quantite' => $quantite,
                'montant' => $montant,
                'user' => $client,
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
    
    #[Route("/Fournisseur_Article/{fournisseur}/{produit}", name :"fournisseur_show", methods : ["GET"]) ]
    public function produitfournisseurhistory(Fournisseur $fournisseur, Produit $produit, ApprovisionnementRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_STOCK') || $this->security->isGranted('ROLE_FINANCE')) {
            $ventes = $repository->findBy(['fournisseur' => $fournisseur, 'produit' => $produit]);
            $quantite = 0;
            $montant = 0;
            foreach($ventes as $vente){
                $quantite += $vente->getQuantite();
                $montant += $vente->getPght() * $vente->getQuantite();
            }
            $response = $this->render('vente/achat_fournisseur_show.html.twig', [
                'ventes' => $ventes,
                'produit' => $produit,
                'quantite' => $quantite,
                'montant' => $montant,
                'user' => $fournisseur,
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
    
    #[Route("Chiffre_client/{client}", name :"chiffre_client") ]
    public function chiffreclient(Client $client,CommandeRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {
             $commandes = $repository->vente_client($client->getId());
             $avoirs = $this->entityManager->getRepository(Avoir::class)->findBy([ 'client' => $client->getId()]);
            //  dd($avoirs);
                $result = [];
                foreach ([$commandes, $avoirs] as $tableau) {
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

            $montant = 0;
            foreach($flat as $commande){
                if($commande instanceof Avoir) $montant -= $commande->getMontant();
                else $montant += $commande->getMontant();
            }
            $response = $this->render('vente/client.html.twig', [
                'commandes' => $flat,
                'user' => $client,
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

     #[Route("Client_Articles/{client}", name :"client_article") ]
    public function clientsortie(Client $client, ProduitRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {

            $response = $this->render('vente/client_articles.html.twig', [
                'produits' => $repository->article_client($client->getId()),
                'user' => $client,
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

    

     #[Route("Fournisseur_Articles/{fournisseur}", name :"fournisseur_article") ]
    public function fournisseurachat(Fournisseur $fournisseur): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {

            $response = $this->render('vente/fournisseur_articles.html.twig', [
                'produits' => $fournisseur->getProduits(),
                'user' => $fournisseur,
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

    
    
    #[Route("Chiffre_fournisseur/{fournisseur}", name :"chiffre_fourniseur") ]
    public function chiffrefournisseur(Fournisseur $fournisseur,FactureRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {
             $factures = $repository->findBy(['fournisseur' => $fournisseur->getId()]);
            $montant = 0;
            foreach($factures as $facture){
                $montant += $facture->getMontant();
            }
            $response = $this->render('vente/fournisseur.html.twig', [
                'factures' => $factures,
                'fournisseur' => $fournisseur,
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
