<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Approvisionnement;
use App\Entity\Approvisionner;
use App\Entity\Avoir;
use App\Entity\Candidature;
use App\Entity\Commande;
use App\Entity\Produit;
use App\Entity\Retour;
use App\Entity\RetourProduit;
use App\Entity\Stock;
use App\Form\CandidatureType;
use App\Repository\CommandeProduitRepository;
use App\Repository\CommandeRepository;
use App\Repository\ImageRepository;
use App\Repository\LivrerProduitRepository;
use App\Repository\ProduitRepository;
use App\Repository\PromotionRepository;
use App\Repository\RetourProduitRepository;
use App\Repository\RetourRepository;
use App\Repository\StockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

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
                'commandes' => $repository->retour(),
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

    #[Route("/Retour_valider/", name :"retour_valider", methods : ["POST"]) ]
    public function retour_valider(Request $request, SessionInterface $session): Response
    {

        if ($this->security->isGranted('ROLE_STOCK')) {

            $com = $request->get('commande');
            $em = $this->entityManager;
            $commande = $em->getRepository(Commande::class)->find($com);
            $produits = $session->get('retour', []);
            $retour = new Retour();
            $em->persist($retour);
            $retour->setCommande($commande);
            foreach ($produits as $prod) {
                $produit = $em->getRepository(Produit::class)->find($prod['id']);
                $retourproduit = new RetourProduit();
                $retourproduit->setProduit($produit);
                $retourproduit->setRetour($retour);
                $retourproduit->setCommande($commande);
                $retourproduit->setMotif($prod['motif']);
                $retourproduit->setLot($prod['lot']);
                $retourproduit->setPeremption(new \Datetime($prod['peremption']));
                $retourproduit->setQuantite($prod['quantite']);
                $em->persist($retourproduit);
                $em->flush();

            }
            $em->flush();
            $this->addFlash('notice', 'Retour enregisté avec succès');
            $session->remove('retour');

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
            $approvisionnenment = new Approvisionnement($produit, $approvisionner, $quantite);
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

    #[Route("/Creer_avoir/", name :"retour_creer_avoir", methods : ["POST"]) ]
    public function retour_avoir(Request $request): Response
    {

        if ($this->security->isGranted('ROLE_ADMIN')) {



            $em = $this->entityManager;
            $retourProduit = $request->get('retour');
            $RetourProduit = $em->getRepository(RetourProduit::class)->find($retourProduit);
            $avoir = new Avoir($RetourProduit->getCommande()->getUser(), $this->getUser(), $RetourProduit->getCommande());
            $avoir->setMontant($RetourProduit->getQuantite() * $RetourProduit->getProduit()->getPrix());
            $avoir->setRetour($RetourProduit->getRetour());

            $RetourProduit->setValider(true);
            $RetourProduit->setRembourser(true);
            $RetourProduit->setAvoir(true);

            $em->persist($RetourProduit);
            $em->persist($avoir);
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


}
