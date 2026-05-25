<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Approvisionnement;
use App\Entity\Approvisionner;
use App\Entity\Avoir;
use App\Entity\Inventaire;
use App\Entity\Candidature;
use App\Entity\Commande;
use App\Entity\Produit;
use App\Entity\Retour;
use App\Entity\RetourProduit;
use App\Entity\CommandeProduit;
use App\Entity\Stock;
use App\Form\CandidatureType;
use App\Repository\CommandeProduitRepository;
use App\Repository\ApprovisionnementRepository;
use App\Repository\CommandeRepository;
use App\Repository\ImageRepository;
use App\Repository\LivrerProduitRepository;
use App\Repository\ProduitRepository;
use App\Repository\PromotionRepository;
use App\Repository\RetourProduitRepository;
use App\Repository\RetourRepository;
use App\Repository\StockRepository;
use App\Repository\InventaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;

#[Route("/{_locale}/Stock" , name :"stock_") ]
class StockController extends AbstractController
{
       public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

    #[Route("/", name :"index", methods : ["GET"]) ]
    public function stock(StockRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_STOCK') || $this->security->isGranted('ROLE_FINANCE')) {

            $response = $this->render('stock/stock.html.twig', [
                'stock' => $repository->stock(),
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

     #[Route("/Inventaire", name :"inventaire", methods : ["GET"]) ]
    public function inventaire_index(InventaireRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_STOCK') || $this->security->isGranted('ROLE_FINANCE')) {

            $response = $this->render('stock/inventaire_index.html.twig', [
                'inventaires' => $repository->inventaire(),
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

    
     #[Route("/Inventaire_new", name :"inventaire_new", methods : ["GET"]) ]
    public function inventaire(StockRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {

            $response = $this->render('stock/inventaire.html.twig', [
                'stock' => $repository->findAll(),
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

    
     #[Route("/Inventaire_show/{date}", name :"inventaire_show", methods : ["GET"]) ]
    public function inventaireshow(InventaireRepository $repository, $date): Response
    {
        if ($this->security->isGranted('ROLE_STOCK') || $this->security->isGranted('ROLE_FINANCE')) {

            $response = $this->render('stock/inventaireshow.html.twig', [
                'inventaires' => $repository->findBy(['date' => new \Datetime($date)]),
                'date' => $date,
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

     #[Route("/Inventaire_show_pdf/{date}", name :"inventaire_show_pdf", methods : ["GET"]) ]
    public function inventaireshowpdf(InventaireRepository $repository, $date, GotenbergPdfInterface $gotenberg): Response
    {
        if ($this->security->isGranted('ROLE_STOCK') || $this->security->isGranted('ROLE_FINANCE')) {

            return $gotenberg
        ->html()
        ->content('stock/inventaireshowpdf.html.twig', [
                'inventaires' => $repository->findBy(['date' => new \Datetime($date)]),
            ])
        ->fileName('facture.pdf')
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


    #[Route("/Ajustement", name :"ajust", methods : ["GET"]) ]
    public function ajust(RetourRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_STOCK') || $this->security->isGranted('ROLE_FINANCE')) {

            $response = $this->render('stock/ajust.html.twig', [
                'retours' => $repository->FindAll(),
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

    #[Route("/Surveiller", name :"surveiller", methods : ["GET"]) ]
    public function surveiller(ProduitRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {

            $response = $this->render('stock/surveiller.html.twig', [
                'produits' => $repository->surveil(),
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


    #[Route("/Rupture", name :"rupture", methods : ["GET"]) ]
    public function rupture(ProduitRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {

            $response = $this->render('stock/rupture.html.twig', [
                'produits' => $repository->findBy(['stock' => 0]),
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

    #[Route("/Peremption", name :"peremption", methods : ["GET"]) ]
    public function peremption(StockRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {

            $response = $this->render('stock/peremption.html.twig', [
                'stocks' => $repository->peremption(),
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


    #[Route("/Retour/", name :"retour", methods : ["GET"]) ]
    public function retour(CommandeRepository $repository, SessionInterface $session): Response
    {
        if ($this->security->isGranted('ROLE_STOCK')) {
            $session->remove('retour');

            $response = $this->render('stock/retour.html.twig', [
                // 'commandes' => $repository->retour(),
                'commandes' => $repository->findBy(['livrer' => true]),
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

    #[Route("/Retour_index/", name :"retour_index", methods : ["GET"]) ]
    public function retourindex(RetourRepository $repository, SessionInterface $session): Response
    {
        if ($this->security->isGranted('ROLE_STOCK')) {
            $session->remove('retour');

            $response = $this->render('stock/retour_index.html.twig', [
                'retours' => $repository->findAll(),
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

    #[Route("/Create_Retour_Show/{id}", name :"retour_show", methods : ["GET"]) ]
    public function retour_show(Commande $commande, LivrerProduitRepository $repository, SessionInterface $session): Response
    {
        $session->remove('retour');

        if ($this->security->isGranted('ROLE_STOCK')) {

            $response = $this->render('stock/retour_show.html.twig', [
                'commande' => $commande,
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                'retour' => $session->get("retour", []),
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


    #[Route("/Retour_history_show/{id}", name :"retour_history_show", methods : ["GET"]) ]
    public function retourhistoryshow(Retour $retour, RetourProduitRepository $repository, SessionInterface $session): Response
    {

        if ($this->security->isGranted('ROLE_STOCK')) {

            $response = $this->render('stock/history_show.html.twig', [
                'retour' => $retour,
                'retourproduits' => $repository->findBy(['retour' => $retour]),
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

    

    #[Route("/Retour_history_show_print/{id}", name :"retour_history_show_print", methods : ["GET"]) ]
    public function retourhistoryshowprint(Retour $retour, RetourProduitRepository $repository, SessionInterface $session): Response
    {

        if ($this->security->isGranted('ROLE_STOCK')) {

            $response = $this->render('stock/history_show_print.html.twig', [
                'retour' => $retour,
                'retourproduits' => $repository->findBy(['retour' => $retour]),
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



    
    #[Route("/Retour_history_show_pdf/{id}", name :"retour_history_show_pdf", methods : ["GET"]) ]
    public function retourhistoryshowpdf(Retour $retour, RetourProduitRepository $repository, GotenbergPdfInterface $gotenberg ): Response
    {

        if ($this->security->isGranted('ROLE_STOCK')) {

            return $gotenberg
        ->html()
        ->content('stock/historyshowpdf.html.twig', [
                'retour' => $retour,
                'retourproduits' => $repository->findBy(['retour' => $retour]),
            ])
        ->fileName('retour.pdf')
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

    #[Route("/Retour_valider/", name :"retour_valider", methods : ["POST"]) ]
    public function retour_valider(Request $request, SessionInterface $session): Response
    {

        if ($this->security->isGranted('ROLE_STOCK')) {

            $com = $request->get('commande');
            $em = $this->entityManager;
            $commande = $em->getRepository(Commande::class)->find($com); 
            if($commande->getPayer()){
                 $this->addFlash('notice', 'Retour pas pris en compte, Pour commande a credit non payée');
                goto saut;
            }
            $commandeProduits = $em->getRepository(CommandeProduit::class)->findBy(['commande' => $commande]);
           
            $commande->setRetour(true);
            $em->persist($commande);
            $produits = $session->get('retour', []);
            $retour = new Retour();
            $retour->setPharmacie($commande->getUser()->getPharmacie());
            $em->persist($retour);
            $retour->setCommande($commande);

            
            $approvisionner = new Approvisionner();
            $approvisionner->setUser($this->getUser());
            $approvisionner->setretour($retour);
            $em->persist($approvisionner);
            $montant = 0;
            $tva = 0;
            $prelevement = 0;
            foreach ($produits as $prod) {
                $produit = $em->getRepository(Produit::class)->find($prod['id']);
                $tvaproduit = 0;
                $prelevementproduit = 0;
                $montantproduit = 0;
                $prix = 0;
                $prixpublic = 0;
                foreach($commandeProduits as $commandeProduit){
                    if($commandeProduit->getProduit()->getId() ==  $produit->getId() 
                        && $commandeProduit->getCommande()->getId() == $commande->getId()){
                        // modification de la commande
                        $commandeProduit->setQuantitecommande($commandeProduit->getQuantite());
                        $commandeProduit->setQuantite($commandeProduit->getQuantite() - $prod['quantite']);
                        $prix = $commandeProduit->getSession();
                        $prixpublic = $commandeProduit->getPublique();
                         $montantproduit = $prod['quantite'] * $prix;
                        if($commandeProduit->getTva() != 0){
                            $tvaproduit = $prix * $prod['quantite'] * 0.1925;
                            $commandeProduit->setTva($commandeProduit->getTva() - $tvaproduit);
                        } 
                        if($commande->getAcompte() != 0){
                            $prelevementproduit = $montantproduit * 0.02;
                            // $commande->setAcompte($commande->getAcompte() - $prelevementproduit);
                        }
                        $em->persist($commandeProduit);

                        $montant = $montant + $montantproduit;
                        $tva += $tvaproduit;
                        $prelevement += $prelevementproduit;
                    }
                }
                $retourproduit = new RetourProduit();
                $retourproduit->setProduit($produit);
                $retourproduit->setRetour($retour);
                $retourproduit->setCommande($commande);
                $retourproduit->setMotif($prod['motif']);
                $retourproduit->setLot($prod['lot']);
                $retourproduit->setPeremption(new \Datetime($prod['peremption']));
                $retourproduit->setQuantite($prod['quantite']);
                $retourproduit->setPrix($prix);
                $retourproduit->setPrixpublic($prixpublic);
                $tvaproduit != 0 ? $retourproduit->setTva($tvaproduit): $retourproduit->setTva(0);
                
            //      $quantite = $request->get('quantite');
            // $lot = $request->get('lot');
            // $peremption = $request->get('peremption');
            // $id = $request->get('produit');
            // $retour = $request->get('retour');
            // $em = $this->entityManager;
            // $produit = $em->getRepository(Produit::class)->find($id);
            // $retour = $em->getRepository(RetourProduit::class)->findOneBy(['retour' => $retour, 'produit' => $produit, 'lot' => $lot]);
            $retourproduit->setReapprovisionner(true);
            $em->persist($retourproduit);
            $approvisionnenment = new Approvisionnement($produit, $approvisionner, $prod['quantite'], null);
            $approvisionnenment->setLot($prod['lot']);
            $approvisionnenment->setPeremption(new \DateTime($prod['peremption']));
            $stock = $em->getRepository(Stock::class)->findOneBy(['produit' => $produit, 'lot' => $prod['lot']]);
            $stock == null ? $stock = new Stock($produit, $prod['lot'], $prod['peremption'], $prod['quantite']) : $stock->setQuantite($stock->getQuantite() + $prod['quantite']);
            $em->persist($stock);
            $produit->setStock($produit->getStock() + $prod['quantite']);
            $em->persist($produit);
            $em->persist($approvisionnenment);

            // $em->flush();

                // $em->persist($retourproduit);
                $em->flush();

            }
            
            $commande->setMontant($commande->getMontant() - $montant);
            $commande->setTva($commande->getTva() - $tva);
            $commande->setAcompte($commande->getAcompte() - $prelevement);
            $commande->setRetour(true);
            $em->persist($commande);
            $em->flush();

            $this->addFlash('notice', 'Retour enregisté avec succès');
            $session->remove('retour');
                saut:
            $res['id'] = 'ok';
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
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

    
    #[Route("/Retour_simple/", name :"retour_valider_simple", methods : ["POST"]) ]
    public function retour_simple(Request $request, SessionInterface $session): Response
    {

        if ($this->security->isGranted('ROLE_STOCK')) {
            
            $com = $request->get('commande');
            $em = $this->entityManager;
            $commande = $em->getRepository(Commande::class)->find($com); 
           
            $commandeProduits = $em->getRepository(CommandeProduit::class)->findBy(['commande' => $commande]);
           
            $commande->setRetour(true);
            $em->persist($commande);
            $produits = $session->get('retour', []);
            $retour = new Retour();
            $retour->setPharmacie($commande->getUser()->getPharmacie());
            $em->persist($retour);
            $retour->setCommande($commande);

            
            $approvisionner = new Approvisionner();
            $approvisionner->setUser($this->getUser());
            $approvisionner->setretour($retour);
            $em->persist($approvisionner);
            $montant = 0;
            $tva = 0;
            $prelevement = 0;
            foreach ($produits as $prod) {
                $produit = $em->getRepository(Produit::class)->find($prod['id']);
                $tvaproduit = 0;
                $prelevementproduit = 0;
                $montantproduit = 0;
                $prix = 0;
                $prixpublic = 0;
                foreach($commandeProduits as $commandeProduit){
                    if($commandeProduit->getProduit()->getId() ==  $produit->getId() 
                        && $commandeProduit->getCommande()->getId() == $commande->getId()){
                        // modification de la commande
                        $commandeProduit->setQuantitecommande($commandeProduit->getQuantite());
                        $commandeProduit->setQuantite($commandeProduit->getQuantite() - $prod['quantite']);
                        $prix = $commandeProduit->getSession();
                        $prixpublic = $commandeProduit->getPublique();
                         $montantproduit = $prod['quantite'] * $prix;
                        if($commandeProduit->getTva() != 0){
                            $tvaproduit = $prix * $prod['quantite'] * 0.1925;
                            $commandeProduit->setTva($commandeProduit->getTva() - $tvaproduit);
                        } 
                        if($commande->getAcompte() != 0){
                            $prelevementproduit = $montanproduit * 0.02;
                            // $commande->setAcompte($commande->getAcompte() - $prelevementproduit);
                        }
                        $em->persist($commandeProduit);

                        $montant = $montant + $montantproduit;
                        $tva += $tvaproduit;
                        $prelevement += $prelevementproduit;
                    }
                }
                $retourproduit = new RetourProduit();
                $retourproduit->setProduit($produit);
                $retourproduit->setRetour($retour);
                $retourproduit->setCommande($commande);
                $retourproduit->setMotif($prod['motif']);
                $retourproduit->setLot($prod['lot']);
                $retourproduit->setPeremption(new \Datetime($prod['peremption']));
                $retourproduit->setQuantite($prod['quantite']);
                $retourproduit->setPrix($prix);
                $retourproduit->setPrixpublic($prixpublic);
                $tvaproduit != 0 ? $retourproduit->setTva($tvaproduit): $retourproduit->setTva(0);
                
            //      $quantite = $request->get('quantite');
            // $lot = $request->get('lot');
            // $peremption = $request->get('peremption');
            // $id = $request->get('produit');
            // $retour = $request->get('retour');
            // $em = $this->entityManager;
            // $produit = $em->getRepository(Produit::class)->find($id);
            // $retour = $em->getRepository(RetourProduit::class)->findOneBy(['retour' => $retour, 'produit' => $produit, 'lot' => $lot]);
            $retourproduit->setReapprovisionner(true);
            $em->persist($retourproduit);
            $approvisionnenment = new Approvisionnement($produit, $approvisionner, $prod['quantite'], null);
            $approvisionnenment->setLot($prod['lot']);
            $approvisionnenment->setPeremption(new \DateTime($prod['peremption']));
            $stock = $em->getRepository(Stock::class)->findOneBy(['produit' => $produit, 'lot' => $prod['lot']]);
            $stock == null ? $stock = new Stock($produit, $prod['lot'], $prod['peremption'], $prod['quantite']) : $stock->setQuantite($stock->getQuantite() + $prod['quantite']);
            $em->persist($stock);
            $produit->setStock($produit->getStock() + $prod['quantite']);
            $em->persist($produit);
            $em->persist($approvisionnenment);

            // $em->flush();

                // $em->persist($retourproduit);
                $em->flush();

            }
            

            $this->addFlash('notice', 'Retour enregisté avec succès');
            $session->remove('retour');
                saut:
            $res['id'] = 'ok';
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
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


    #[Route("/Retour_valider_avoir/", name :"retour_valider_avoir", methods : ["POST"]) ]
    public function retour_valider_avoir(Request $request, SessionInterface $session): Response
    {// retour avec avoir

        if ($this->security->isGranted('ROLE_STOCK')) {

            $com = $request->get('commande');
            $em = $this->entityManager;
            $commande = $em->getRepository(Commande::class)->find($com);
             if(!$commande->getPayer()){
                 $this->addFlash('notice', 'Retour pas pris en compte, Pour commande deja payée');
                goto sautavoir;
            }
            $commandeProduits = $em->getRepository(CommandeProduit::class)->findBy(['commande' => $commande]);
           
            $produits = $session->get('retour', []);
            $retour = new Retour();
            $avoir = new Avoir($commande->getUser(), $this->getUser(), $commande);
            $retour->setAvoir($avoir);
            $retour->setPharmacie($commande->getUser()->getPharmacie());
            $em->persist($retour);
            $em->persist($avoir);
            $retour->setCommande($commande);

            $approvisionner = new Approvisionner();
            $approvisionner->setUser($this->getUser());
            $approvisionner->setRetour($retour);
            $em->persist($approvisionner);
            $montant = 0;
            $tva = 0;
            // $prelevement = 0;
            foreach ($produits as $prod) {
                $produit = $em->getRepository(Produit::class)->find($prod['id']);
                $tvaproduit = 0;
                // $prelevementproduit = 0;
                $montantproduit = 0;
                $prix = 0;
                $prixpublic = 0;
                foreach($commandeProduits as $commandeProduit){
                    if($commandeProduit->getProduit()->getId() ==  $produit->getId() 
                        && $commandeProduit->getCommande()->getId() == $commande->getId()){
                        // modification de la commande
                        // $commandeProduit->setQuantitecommande($commandeProduit->getQuantite());
                        // $commandeProduit->setQuantite($commandeProduit->getQuantite() - $prod['quantite']);
                        $prix = $commandeProduit->getSession();
                        $prixpublic = $commandeProduit->getPublique();
                         $montantproduit = $prod['quantite'] * $prix;

                        if($commandeProduit->getTva() != 0){
                            $tvaproduit = $prix * $prod['quantite'] * 0.1925;
                            // $commandeProduit->setTva($commandeProduit->getTva() - $tvaproduit);
                        } 
                        // if($commande->getAcompte() != 0){
                        //     $prelevementproduit = $montantproduit * 0.02;
                        //     // $commande->setAcompte($commande->getAcompte() - $prelevementproduit);
                        // }
                        $em->persist($commandeProduit);

                        $montant = $montant + $montantproduit;
                        $tva += $tvaproduit;
                        // $prelevement += $prelevementproduit;
                    }
                }

                $retourproduit = new RetourProduit();
                $retourproduit->setProduit($produit);
                $retourproduit->setRetour($retour);
                $retourproduit->setCommande($commande);
                $retourproduit->setMotif($prod['motif']);
                $retourproduit->setLot($prod['lot']);
                $retourproduit->setPeremption(new \Datetime($prod['peremption']));
                $retourproduit->setQuantite($prod['quantite']);
                $retourproduit->setPrix($prix);
                $retourproduit->setPrixpublic($prixpublic);
                $tvaproduit != 0 ? $retourproduit->setTva($tvaproduit): $retourproduit->setTva(0);
                
                // partie avoir
                $retourproduit->setValider(true);
                $retourproduit->setRembourser(true);
                $retourproduit->setAvoir(true);
                //fin avoir
            //      $quantite = $request->get('quantite');
            // $lot = $request->get('lot');
            // $peremption = $request->get('peremption');
            // $id = $request->get('produit');
            // $retour = $request->get('retour');
            // $em = $this->entityManager;
            
            // $produit = $em->getRepository(Produit::class)->find($id);
            // $retour = $em->getRepository(RetourProduit::class)->findOneBy(['retour' => $retour, 'produit' => $produit, 'lot' => $lot]);
            $retourproduit->setReapprovisionner(true);
            $em->persist($retourproduit);
            $approvisionnenment = new Approvisionnement($produit, $approvisionner, $prod['quantite'], null);
            $approvisionnenment->setLot($prod['lot']);
            $approvisionnenment->setPeremption(new \DateTime($prod['peremption']));
            $stock = $em->getRepository(Stock::class)->findOneBy(['produit' => $produit, 'lot' => $prod['lot']]);
            $stock == null ? $stock = new Stock($produit, $prod['lot'], $prod['peremption'], $prod['quantite']) : $stock->setQuantite($stock->getQuantite() + $prod['quantite']);
            $em->persist($stock);
            $produit->setStock($produit->getStock() + $prod['quantite']);
            $em->persist($produit);
            $em->persist($approvisionnenment);

            // $em->flush();

                // $em->persist($retourproduit);
                $em->flush();

            }
            
           
            // $commande->setMontant($commande->getMontant() - $montant);
            // $commande->setTva($commande->getTva() - $tva);
            // $commande->setAcompte($commande->getAcompte() - $prelevement);
            // $commande->setRetour(true);
            // $em->persist($commande);

            $avoir->setMontant($montant);
            $avoir->setTva($tva);
            $avoir->setRetour($retour);
            $em->persist($avoir);
            $em->flush();

            $this->addFlash('notice', 'Retour plus avoir enregistés avec succès');
            $session->remove('retour');
                sautavoir:
            $res['id'] = 'ok';
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
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

    
    #[Route("/Sortie_Produit/", name :"sortie_produit", methods : ["GET"]) ]
    public function sortie(ProduitRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_STOCK') || $this->security->isGranted('ROLE_FINANCE')) {

            $response = $this->render('stock/sortie.html.twig', [
                'produits' => $repository->sortie(),
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

    #[Route("/Reapprovisionner/", name :"retour_reapprovisionner", methods : ["POST"]) ]
    public function retour_reapprovisionner(Request $request): Response
    {

        if ($this->security->isGranted('ROLE_ADMIN')) {

            $quantite = $request->get('quantite');
            $lot = $request->get('lot');
            $peremption = $request->get('peremption');
            $id = $request->get('produit');
            $retour = $request->get('retour');
            $em = $this->entityManager;
            $approvisionner = new Approvisionner();
            $approvisionner->setUser($this->getUser());
            $em->persist($approvisionner);
            $produit = $em->getRepository(Produit::class)->find($id);
            $retour = $em->getRepository(RetourProduit::class)->findOneBy(['retour' => $retour, 'produit' => $produit, 'lot' => $lot]);
            $retour->setReapprovisionner(true);
            $em->persist($retour);
            $approvisionnenment = new Approvisionnement($produit, $approvisionner, $quantite, null);
            $approvisionnenment->setLot($lot);
            $approvisionnenment->setPeremption(new \DateTime($peremption));
            $stock = $em->getRepository(Stock::class)->findOneBy(['produit' => $produit, 'lot' => $lot]);
            $stock == null ? $stock = new Stock($produit, $lot, $peremption, $quantite) : $stock->setQuantite($stock->getQuantite() + $quantite);
            $em->persist($stock);
            $produit->setStock($produit->getStock() + $quantite);
            $em->persist($produit);
            $em->persist($approvisionnenment);

            $em->flush();

            $res['id'] = 'Réapprovisionner avec succès';
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
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

 #[Route("/Valider_Remboursement/", name :"retour_valider_remboursement", methods : ["POST"]) ]
    public function retour_valider_remboursement(Request $request): Response
    {

        if ($this->security->isGranted('ROLE_ADMIN')) {


            $lot = $request->get('lot');
            $id = $request->get('produit');
            $retour = $request->get('retour');
            $em = $this->entityManager;

            $produit = $em->getRepository(Produit::class)->find($id);
            $retour = $em->getRepository(RetourProduit::class)->findOneBy(['retour' => $retour, 'produit' => $produit, 'lot' => $lot]);
            $retour->setValider(true);
            $em->persist($retour);

            $em->flush();

            $res['id'] = 'Remboursement accordé';
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
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

     #[Route("/Repportmouvement/", name :"mouvement") ]
    public function rapporttiers()
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

            return $this->render('stock/rapport_mouvement.html.twig');
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

    
    #[Route("/mouvement_lien", name :"mouvement_lien") ]
    public function liendaysbrouyard(Request $request)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $date1 = $request->get('date1');
            $date2 = $request->get('date2');
            $lien = $this->generateUrl('stock_mouvement_stock', ['date1' => $date1, 'date2' => $date2]);
            $res['ok'] = $lien;
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
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
    
    #[Route("/Mouvement_stock/{date1}/{date2}", name :"mouvement_stock", methods : ["GET"]) ]
    public function mouvement(ProduitRepository $repo, $date1, $date2): Response
    {
        if ($this->security->isGranted('ROLE_STOCK') || $this->security->isGranted('ROLE_FINANCE')) {
           
            $response = $this->render('stock/mouvement.html.twig', [
                'produits' => $repo->reapprovisionnement(),
                'day1' => $date1,
                'day2' => $date2,
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
    
    #[Route("/Mouvement_stock_pdf/{date1}/{date2}", name :"mouvement_stock_pdf", methods : ["GET"]) ]
    public function mouvement_pdf(ProduitRepository $repo, $date1, $date2, GotenbergPdfInterface $gotenberg): Response
    {
        if ($this->security->isGranted('ROLE_STOCK') || $this->security->isGranted('ROLE_FINANCE')) {
           
        return $gotenberg
        ->html()
        ->content('stock/mouvementpdf.html.twig', [
                'produits' => $repo->reapprovisionnement(),
                'day1' => $date1,
                'day2' => $date2,
            ])
        ->fileName('mouvement.pdf')
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

    

  


    #[Route("/{id}", name :"produit_show", methods : ["GET"]) ]
    public function produit(Produit $produit, StockRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_STOCK') || $this->security->isGranted('ROLE_FINANCE')) {

            $response = $this->render('stock/produit_show.html.twig', [
                'stock' => $repository->findBy(['produit' => $produit]),
                'produit' => $produit,
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

    

    #[Route("/History_Produit/{id}", name :"history_produit_show", methods : ["GET"]) ]
    public function produithistory(Produit $produit, LivrerProduitRepository $repository, ApprovisionnementRepository $repo): Response
    {
        if ($this->security->isGranted('ROLE_STOCK') || $this->security->isGranted('ROLE_FINANCE')) {
            $livraison = $repository->findBy(['produit' => $produit]);
            $reappro = $repo->findBy(['produit' => $produit]);
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
                // dd($flat);
            $response = $this->render('stock/sortie_show.html.twig', [
                'stocks' => $flat,
                'produit' => $produit,
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
    


    #[Route("/print/{id}", name :"produit_show_print", methods : ["GET"]) ]
    public function produitprint(Produit $produit, StockRepository $repository): Response
    {
         if ($this->security->isGranted('ROLE_STOCK') || $this->security->isGranted('ROLE_FINANCE')) {

            $response = $this->render('stock/produit_show_print.html.twig', [
                'stock' => $repository->findBy(['produit' => $produit]),
                'produit' => $produit,
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


    #[Route("/add/", name :"retour_add") ]
    public function add(Request $request, ProduitRepository $produitRepository, SessionInterface $session)
    {
        // On récupère le panier actuel
        $retour = $session->get("retour", []);
        if ($request->isXmlHttpRequest()) {// traitement de la requete ajax
            $id = $request->get('prod');// recuperation de id produit
            $motif = $request->get('motif');// recuperation de id produit
            $quantite = $request->get('quantite');// recuperation de la quantite commamde
            $lot = $request->get('lot');// recuperation de la quantite commamde
            $peremption = $request->get('peremption');// recuperation de la quantite commamde

            foreach ($retour as $key => $item) {
                if ($item['id'] == $id && $item['lot'] == $lot) {
                    $res['id'] = 'Un produit avec les même reference a été ajouté';
                    goto suite;
                }
            }
            $produit = $produitRepository->find($id);
            $res['idp'] = 'ok';
            $res['id'] = $id;
            $res['lot'] = $lot;
            $res['peremption'] = $peremption;
            $res['ref'] = $produit->getReference();
            $res['designation'] = $produit->getDesigantion();
            $res['quantite'] = $quantite;//$produit->getQuantite();
            $res['motif'] = $motif;//$produit->getQuantite();
            $retour[] = $res;


            // On sauvegarde dans la session
            $session->set("retour", $retour);
//
            suite:
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
            return $response;
        }

    }

    
    #[Route("/editinventaire/", name :"editinventaire") ]
    public function editinventaire(Request $request, StockRepository $Repository, ProduitRepository $produitRepository, SessionInterface $session)
    {
        // On récupère le panier actuel
        // $panier = $session->get("panier", []);
        if( $request->isXmlHttpRequest() )
        {// traitement de la requete ajax
            $id = $request->get('stock');// recuperation de id produit
            $quantite = $request->get('quantite');// nouvelle quantite
            $quant = $request->get('quant');// ancienne quantite
            $motif = $request->get('motif');// motif
            $reduction = 0;
            // if(empty($panier[$id])){//verification existance produit dans le panier
            $stock = $Repository->find($id); // recuperation de id produit dans la db
            $produit = $produitRepository->find($stock->getProduit()->getId()); // recuperation de id produit dans la db
            $diff = $quantite - $quant;
            $produit->setStock($produit->getStock() + $diff);
            
            $stock->setQuantite($stock->getQuantite() + $diff);

            $inventaire = new Inventaire($produit, $this->getUser(),$motif,$quant,$quantite);
            $inventaire->setLot($stock->getLot());
            $inventaire->setPeremption($stock->getPeremption());

            $this->entityManager->persist($inventaire);
            $this->entityManager->persist($produit);
            $this->entityManager->persist($stock);
            $this->entityManager->flush();

            // log
             $heure = date("d/m/Y H:i:s");
            file_put_contents(__DIR__ . '/inventaitre.log', $heure." ".$this->getUser()->getId()." ".$this->getUser()->getNom()." ".$this->getUser()->getPrenom()." ".$produit->getDesigantion()." Qauntite anterieur:".$quant." nouvelle quantite:".$quantite."\n", FILE_APPEND);

            $res['id'] = 'ok';
            

            $response = new Response();
            $response->headers->set('content-type','application/json');
            $re = json_encode($res);
            $response->setContent($re);
            return $response;
        }

    }

    #[Route("/edit/", name :"edit") ]
    public function edit(Request $request, SessionInterface $session)
    {

        if ($request->isXmlHttpRequest()) {// traitement de la requete ajax
            $id = $request->get('prod');// recuperation de id produit
            $quantite = $request->get('quantite');// recuperation de la quantite commamde
            $retour = $session->get("retour", []);
            if (!empty($retour[$id])) {//verification existance produit dans le panier

                $produit = $retour[$id]['produit'];
                $produit->setQuantite($quantite);
                $retour[$id]['produit'] = $produit;

                // On sauvegarde dans la session
                $session->set("retour", $retour);

                $res['id'] = 'ok';
                $res['panier'] = $quantite;

            } else {
                $res['id'] = 'no';
            }

            //$session->set("approv", $approv);
            $res['id'] = 'ok';
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
            return $response;
        }

    }

    #[Route("/delete/", name :"retour_delete") ]
    public function delete(Request $request, ProduitRepository $repository, SessionInterface $session)
    {
        // On récupère le panier actuel
        $retour = $session->get("retour", []);
        $id = $request->get('prod');
        $lot = $request->get('lot');
        foreach ($retour as $key => $item) {
            if ($item['id'] == $id && $item['lot'] == $lot) {
                unset($retour[$key]);
            }
        }
//        $id = $repository->find($request->get('prod'))->getId();
//        foreach ($approv as $key => $item) {
//            if ($item['produit']->getId() == $id) {
//                unset($approv[$id]);
//            }
//        }
        // On sauvegarde dans la session
        $session->set("retour", $retour);
        $res['id'] = 'ok';
        $res['nb'] = count($retour);
        $response = new Response();
        $response->headers->set('content-type', 'application/json');
        $re = json_encode($res);
        $response->setContent($re);
        return $response;
    }

    #[Route("/deleteAll/", name :"delete_all") ]
    public function deleteAll(SessionInterface $session)
    {
        $session->remove("panier");

        $response = $this->redirectToRoute('commande_panier_panier', [], Response::HTTP_SEE_OTHER);
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

     #[Route("/{id}", name :"epuise", methods : ["POST"]) ]
    public function epuise(Request $request, Stock $stock): Response
    {
        // try {
            if ($this->isCsrfTokenValid('delete' . $stock->getId(). 'lot' . $stock->getLot(), $request->request->get('_token'))) {
            // dd($request);    
                $entityManager = $this->entityManager;
                $quant = $stock->getQuantite();
                

                $inventaire = new Inventaire($stock->getProduit(), $this->getUser(),$request->request->get('motif'),$quant,0);
                $inventaire->setLot($stock->getLot());
                $inventaire->setPeremption($stock->getPeremption());
                $stock->getProduit()->setStock(0);
                $entityManager->persist($inventaire);
                $entityManager->persist($stock->getProduit());
                $entityManager->remove($stock);
                $entityManager->flush();
                 // log
             $heure = date("d/m/Y H:i:s");
            file_put_contents(__DIR__ . '/inventaitre.log', $heure." ".$this->getUser()->getId()." ".$this->getUser()->getNom()." ".$this->getUser()->getPrenom()." ".$stock->getProduit()->getDesigantion()." Qauntite anterieur:".$quant." nouvelle quantite: 0 \n", FILE_APPEND);

            }
            $this->addFlash('notice', 'Produit Supprimé');
            return $this->redirectToRoute('stock_inventaire_new', [], Response::HTTP_SEE_OTHER);
        // }
        // catch (\Exception $exception){
        //     $this->addFlash('notice', 'Ce produit ne peut être supprimer pour des raisons de traçabilité');
        //     return $this->redirectToRoute('produit_index', [], Response::HTTP_SEE_OTHER);

        // }


    }


}
