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
use App\Entity\Fournisseur;
use App\Entity\Versement;
use App\Form\CommandeType;
use App\Form\PaiementFormType;
use App\Form\VersementType;
use App\Repository\CommandeProduitRepository;
use App\Repository\LivrerProduitRepository;
use App\Repository\CommandeRepository;
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
                 $ug = 0;
                    // traitement promotion floor()
                    if (!empty($vente->getPromotion())) {
                        if (!empty($vente->getPromotion()->getPremier())) {
                            
                            $promo = $vente->getPromotion();

                            if ($vente->getQuantite() / $promo->getCinquieme() >= 1) {

                                $unite = floor($vente->getQuantite() / $promo->getCinquieme());
                                $ug += $unite * $promo->getUgcinquieme();
                                $suite = $vente->getQuantite() - $unite * $promo->getCinquieme();

                                if ($suite / $promo->getQuatrieme() >= 1) {

                                    $unite = floor($suite / $promo->getQuatrieme());
                                    $ug += $unite * $promo->getUgquatrieme();
                                    $suite = $suite - $unite * $promo->getQuatrieme();

                                    if ($suite / $promo->getTroisieme() >= 1) {

                                        $unite = floor($suite / $promo->getTroisieme());
                                        $ug += $unite * $promo->getUgtroisieme();
                                        $suite = $suite - $unite * $promo->getTroisieme();

                                        if ($suite / $promo->getDeuxieme() >= 1) {

                                            $unite = floor($suite / $promo->getDeuxieme());
                                            $ug += $unite * $promo->getUgdeuxieme();
                                            $suite = $suite - $unite * $promo->getDeuxieme();

                                            if ($suite / $promo->getPremier() >= 1) {
                                                $unite = floor($suite / $promo->getPremier());
                                                $ug += $unite * $promo->getUgpremier();
                                            }

                                        } elseif ($suite / $promo->getPremier() >= 1) {

                                            $unite = floor($suite / $promo->getPremier());
                                            $ug += $unite * $promo->getUgpremier();
                                        }
                                    }
                                }

                            } elseif ($vente->getQuantite() / $promo->getQuatrieme() >= 1) {

                                $unite = floor($vente->getQuantite() / $promo->getQuatrieme());
                                $ug += $unite * $promo->getUgquatrieme();
                                $suite = $vente->getQuantite() - $unite * $promo->getQuatrieme();

                                if ($suite / $promo->getTroisieme() >= 1) {

                                    $unite = floor($suite / $promo->getTroisieme());
                                    $ug += $unite * $promo->getUgtroisieme();
                                    $suite = $suite - $unite * $promo->getTroisieme();

                                    if ($suite / $promo->getDeuxieme() >= 1) {

                                        $unite = floor($suite / $promo->getDeuxieme());
                                        $ug += $unite * $promo->getUgdeuxieme();
                                        $suite = $suite - $unite * $promo->getDeuxieme();

                                        if ($suite / $promo->getPremier() >= 1) {
                                            $unite = floor($suite / $promo->getPremier());
                                            $ug += $unite * $promo->getUgpremier();
                                        }

                                    } elseif ($suite / $promo->getPremier() >= 1) {

                                        $unite = floor($suite / $promo->getPremier());
                                        $ug += $unite * $promo->getUgpremier();
                                    }
                                }
                            } elseif ($vente->getQuantite() / $vente->getPromotion()->getTroisieme() >= 1) {

                                $unite = floor($vente->getQuantite() / $vente->getPromotion()->getTroisieme());
                                $ug = $ug + $unite * $vente->getPromotion()->getUgtroisieme();
                                $suite = $vente->getQuantite() - $unite * $vente->getPromotion()->getTroisieme();

                                if ($suite / $vente->getPromotion()->getDeuxieme() >= 1) {

                                    $unite = floor($suite / $vente->getPromotion()->getDeuxieme());//round
                                    $ug = $ug + $unite * $vente->getPromotion()->getUgdeuxieme();
                                    $suite = $suite - $unite * $vente->getPromotion()->getDeuxieme();

                                    if ($suite / $vente->getPromotion()->getPremier() >= 1) {
                                        $unite = floor($suite / $vente->getPromotion()->getPremier());//round
                                        $ug = $ug + $unite * $vente->getPromotion()->getUgpremier();
                                    }

                                } elseif ($suite / $vente->getPromotion()->getPremier() >= 1) {
                                    $unite = floor($suite / $vente->getPromotion()->getPremier());//round
                                    $ug = $ug + $unite * $vente->getPromotion()->getUgpremier();
                                }

                            } elseif ($vente->getQuantite() / $vente->getPromotion()->getDeuxieme() >= 1) {


                                $unite = floor($vente->getQuantite() / $vente->getPromotion()->getDeuxieme());//round
                                $ug = $ug + $unite * $vente->getPromotion()->getUgdeuxieme();
                                $suite = $vente->getQuantite() - $unite * $vente->getPromotion()->getDeuxieme();

                                if ($suite / $vente->getPromotion()->getPremier() >= 1) {
                                    $unite = floor($suite / $vente->getPromotion()->getPremier());//round
                                    $ug = $ug + $unite * $vente->getPromotion()->getUgpremier();
                                }
                            } elseif ($vente->getQuantite() / $vente->getPromotion()->getPremier() >= 1) {
                                $unite = floor($vente->getQuantite() / $vente->getPromotion()->getPremier());//round
                                $ug = $ug + $unite * $vente->getPromotion()->getUgpremier();

                            }
                        }
                    }
                    $totalug += $ug;
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
    
    #[Route("Chiffre_client/{client}", name :"chiffre_client") ]
    public function chiffreclient(Client $client,CommandeRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {
             $commandes = $repository->vente_client($client->getId());
            $montant = 0;
            foreach($commandes as $commande){
                $montant += $commande->getMontant();
            }
            $response = $this->render('vente/client.html.twig', [
                'commandes' => $commandes,
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
