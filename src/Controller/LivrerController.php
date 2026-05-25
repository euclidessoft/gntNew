<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Panier;
use App\Entity\Livrer;
use App\Entity\LivrerProduit;
use App\Entity\LivrerReste;
use App\Entity\Retour;
use App\Entity\Stock;
use App\Entity\User;
use App\Entity\Client;
use App\Form\LivrerType;
use App\Form\PharmauserType;
use App\Repository\CommandeProduitRepository;
use App\Repository\CommandeRepository;
use App\Repository\LivrerProduitRepository;
use App\Repository\LivrerRepository;
use App\Repository\LivrerResteRepository;
use App\Repository\ProduitRepository;
use App\Repository\RetourProduitRepository;
use App\Repository\StockRepository;
use App\Service\SMSService;
use App\Service\LamService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\PdfService;

#[Route("/{_locale}/livraison", name :"livraison_") ]
class LivrerController extends AbstractController
{
       public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

    #[Route("/", name :"index", methods : ["GET"]) ]
    public function index(LivrerRepository $livrerRepository, CommandeRepository $repository, SessionInterface $session, RetourProduitRepository $retourProduitRepository): Response
    {
        if ($this->security->isGranted('ROLE_STOCK')) {

            $response = $this->render('livrer/index.html.twig', [
                'retours' => $retourProduitRepository->findBy(['rembourser' => false, 'avoir' => false, 'valider' => true]),
                'livrers' => $livrerRepository->findBy(['reste' => true]),
                'commandes' => $repository->findBy(['suivi' => true, 'livraison' => false]),
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
        } elseif ($this->security->isGranted('ROLE_LIVREUR')) {


            $response = $this->render('livrer/index_livreur.html.twig', [
                'commandes' => $livrerRepository->findBy(['livreur' => $this->getUser()->getId(), 'livrer' => false]),
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
        } elseif ($this->security->isGranted('ROLE_CLIENT')) {
             $livrers = null;
            $retours = null;
            if($this->getUser()->getTuteur() === null){ 
                $livrers = $livrerRepository->findBy(['reste' => true, 'user' => $this->getUser()->getId()]);
                $retours = $retourProduitRepository->retour_client($this->getUser()->getId());
                $commandes = $repository->findBy(['suivi' => true, 'livraison' => false, 'user' => $this->getUser()->getId()]);
            }
            else{
                $livrers = $livrerRepository->findBy(['reste' => true, 'user' => $this->getUser()->getTuteur()->getId()]);
                $retours = $retourProduitRepository->retour_client($this->getUser()->getTuteur()->getId());
                $commandes = $repository->findBy(['suivi' => true, 'livraison' => false, 'user' => $this->getUser()->getTuteur()->getId()]);
            }
             $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]); 
            $response = $this->render('livrer/index_client.html.twig', [
                'retours' => $retours ,
                'livrers' => $livrers,
                'commandes' => $commandes,
                'panier' => $panier,
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

    #[Route("/Historique/", name :"historique") ]
    public function histo_admin(LivrerRepository $repository, SessionInterface $session, CommandeRepository $commandeRepository)
    {
        if ($this->security->isGranted('ROLE_STOCK') || $this->security->isGranted('ROLE_FINANCE')) {
            //$panier = $session->get("panier", []);

            $response = $this->render('livrer/history.html.twig', [
                'livrers' => $repository->historique(),
                //'panier' => $panier,
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
        } elseif ($this->security->isGranted('ROLE_LIVREUR')) {
            //$panier = $session->get("panier", []);

            $response = $this->render('livrer/history_livreur.html.twig', [
                'livrers' => $repository->findBy(['livreur' => $this->getUser()->getId(), 'livrer' => true]),
                //'panier' => $panier,
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
        } elseif ($this->security->isGranted('ROLE_CLIENT')) {
            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]); 
            $this->getUser()->getTuteur() === null ? 
            $livrers = $repository->historique_client($this->getUser()->getId()) :
            $livrers = $repository->historique_client($this->getUser()->getTuteur()->getId());
            
            $response = $this->render('livrer/history_client.html.twig', [
                'livrers' => $livrers,
                'panier' => $panier,
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

    #[Route("/{id}", name :"show", methods : ["GET","POST"]) ]
    public function show(Request $request, Commande $commande, CommandeProduitRepository $comprodrepository, ProduitRepository $repository, SessionInterface $session): Response
    {// traitement livraison
        if ($this->security->isGranted('ROLE_STOCK')) {
            if ($commande->getLivraison()) {
                $this->addFlash('notice', 'Livraison déjà traitée');
                $response = $this->redirectToRoute('livraison_index');
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
            $livrer = new Livrer($commande, $this->getUser());
            $form = $this->createForm(LivrerType::class, $livrer);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $session->set('livreur', $livrer->getLivreur()->getId());
                return $this->redirectToRoute('livraison_valider', ['id' => $commande->getId()]);

            }

            $session->remove("livraison", []);
            $commandeproduits = $comprodrepository->findBy(['commande' => $commande]);
            $listcommande = [];
            foreach ($commandeproduits as $commandeproduit) {
                $stock = $repository->find($commandeproduit->getProduit()->getId())->getStock();
                $commandeproduit->setStock($stock);// affectation produit pour verification
                $listcommande[] = $commandeproduit;
            }
            $session->set("traitement", []);

            $response = $this->render('livrer/show.html.twig', [
                'commandes' => $listcommande,
                'commandereference' => $commande,
                'form' => $form->createView(),
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

    #[Route("/Livraison_show/{id}", name :"livreur_show", methods : ["GET","POST"]) ]
    public function livreurshow(Livrer $livrer, Request $request): Response
    {// traitement livraison

        if ($this->security->isGranted('ROLE_LIVREUR') && $livrer->getLivreur() == $this->getUser()) {
            //  $commande = new Commande();

            // $form = $this->createForm(PharmauserType::class, $commande, ['attr' => ['id' => $livrer->getCommande()->getUser()->getPharmacie()->getId()]]);
            // $form->handleRequest($request);

            // if ($form->isSubmitted()) {}

            $response = $this->render('livrer/show_livreur.html.twig', [
//                'commandes' => $commandeproduits,
                'commandereference' => $livrer->getCommande(),
                'livrer' => $livrer,
                // 'form' => $form->createView(),
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

    #[Route("/Valider_Livraison_show/{id}", name :"valider_livreur_show", methods : ["GET","POST"]) ]
    public function validerlivreur(Livrer $livrer): Response
    {// traitement livraison

        if ($this->security->isGranted('ROLE_LIVREUR') && $livrer->getLivreur() == $this->getUser()) {

            $em = $this->entityManager;
            $livrer->setDateefectlivraison(new \DateTime());
            $livrer->setLivrer(true);
            $livrer->getCommande()->setLivrer(true);
            $livrer->getCommande()->setDateefectlivraison(new \DateTime());
            $em->persist($livrer);
            $em->persist($livrer->getCommande());
            $em->flush();
            $this->addFlash('notice', 'livraison effectué avec succès');
            

            $response = $this->redirectToRoute('livraison_index');
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

    #[Route("/Reste/{id}", name :"reste_show", methods : ["GET", "POST"]) ]
    public function reste(Request $request, Commande $commande, LivrerResteRepository $livrerResteRepository, CommandeProduitRepository $comprodrepository, ProduitRepository $repository, SessionInterface $session): Response
    {// traitement livraison
        if ($this->security->isGranted('ROLE_LIVREUR')) {

            $session->remove("livraison");
            $commandeproduits = $livrerResteRepository->findBy(['commande' => $commande]);
            $listcommande = [];
            foreach ($commandeproduits as $commandeproduit) {
                $stock = $repository->find($commandeproduit->getProduit()->getId())->getStock();
                $commandeproduit->setStock($stock);
                $listcommande[] = $commandeproduit;
            }
            $livrer = new Livrer($commande, $this->getUser());
            $form = $this->createForm(LivrerType::class, $livrer);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $session->set('livreur', $livrer->getLivreur()->getId());
                return $this->redirectToRoute('livraison_reste_valider', ['id' => $commande->getId()]);

            }
            $session->set("traitement", []);
            $response = $this->render('livrer/reste_show.html.twig', [
                'commandes' => $listcommande,
                'commandereference' => $commande,
                'form' => $form->createView(),
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


    #[Route("/Retour/{id}", name :"retour_show", methods : ["GET", "POST"]) ]
    public function retour(Request $request, Retour $retour, RetourProduitRepository $retourProduitRepository, ProduitRepository $repository, SessionInterface $session): Response
    {// traitement livraison
        if ($this->security->isGranted('ROLE_LIVREUR')) {
            $session->remove("livraison");
            $commandeproduits = $retourProduitRepository->findBy(['retour' => $retour, 'valider' => true, 'rembourser' => false]);
            $listcommande = [];
            foreach ($commandeproduits as $commandeproduit) {
                $stock = $repository->find($commandeproduit->getProduit()->getId())->getStock();
                $commandeproduit->setStock($stock);
                $listcommande[] = $commandeproduit;
            }
            $commande = new Commande();
            $livrer = new Livrer($commande, $this->getUser());
            $form = $this->createForm(LivrerType::class, $livrer);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $session->set('livreur', $livrer->getLivreur()->getId());
                return $this->redirectToRoute('livraison_retour_valider', ['id' => $retour->getId()]);

            }
            $session->set("traitement", []);
            $response = $this->render('livrer/retour_show.html.twig', [
                'commandes' => $listcommande,
                'commandereference' => $retour,
                'form' => $form->createView(),
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

    #[Route("/Reste_print/{id}", name :"reste_show_print", methods : ["GET"]) ]
    public function resteprint(Commande $commande, LivrerProduitRepository $livrerProduitRepository, CommandeProduitRepository $comprodrepository, ProduitRepository $repository, SessionInterface $session): Response
    {// traitement livraison


        $commandeproduits = $livrerProduitRepository->findBy(['commande' => $commande]);

        $response = $this->render('livrer/reste_show_print.html.twig', [
            'commandes' => $commandeproduits,
            'commandereference' => $commande,
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
    }

    #[Route("/New_Reste_print/{id}/{livrer}", name :"new_reste_show_print", methods : ["GET"]) ]
    public function newresteprint(Commande $commande, Livrer $livrer, LivrerProduitRepository $livrerProduitRepository, CommandeProduitRepository $comprodrepository, ProduitRepository $repository, SessionInterface $session): Response
    {// traitement livraison


        $commandeproduits = $livrerProduitRepository->findBy(['commande' => $commande, 'livrer' => $livrer]);

        $response = $this->render('livrer/reste_show_print.html.twig', [
            'commandes' => $commandeproduits,
            'commandereference' => $commande,
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
    }

    #[Route("/Retour_print/{id}", name :"retour_show_print", methods : ["GET"]) ]
    public function retourprint(Request $request, Retour $retour, LivrerProduitRepository $livrerProduitRepository, ProduitRepository $repository, SessionInterface $session): Response
    {// traitement livraison


        $commandeproduits = $livrerProduitRepository->findBy(['retour' => $retour]);

        $response = $this->render('livrer/retour_show_print.html.twig', [
            'commandes' => $commandeproduits,
            'commandereference' => $retour,
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
    }

    #[Route("/Historique/{id}", name :"historique_show", methods : ["GET"]) ]
    public function history_show(Commande $commande, LivrerRepository $livrerRepository, LivrerProduitRepository $livrerProduitRepository, ProduitRepository $repository, SessionInterface $session): Response
    {// traitement livraison

        if ($this->security->isGranted('ROLE_STOCK') || $this->security->isGranted('ROLE_FINANCE')) {

            if ($commande->getLivraison()) {

                $livrer = $livrerRepository->findBy(['commande' => $commande]);

                $response = $this->render('livrer/history_show.html.twig', [
//                'commandes' => $commandeproduits,
                    'commandereference' => $commande,
                    'livrer' => $livrer,
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

            }
        } elseif ($this->security->isGranted('ROLE_CLIENT')) {

             $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]); 
             if ($commande->getLivraison()) {
                 if($this->getUser()->getTuteur() === null){
                if($commande->getUser() != $this->getUser()){
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
             } else{
                if($commande->getUser() != $this->getUser()->getTuteur()){
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
                $livrer = $livrerRepository->findBy(['commande' => $commande]);
//            $histo = [];
//            foreach ($livrer as $item) {
//                $histo[] = $item->getId();
//            }
//
//            $commandeproduits = $livrerProduitRepository->historique($histo);


                $response = $this->render('livrer/history_show_client.html.twig', [
//                'commandes' => $commandeproduits,
                    'commandereference' => $commande,
                    'livrer' => $livrer,
                    'panier' => $panier,
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

            }
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

    

    #[Route("/Historique_pdf/{id}", name :"historique_show_pdf", methods : ["GET"]) ]
    public function history_showpdf(Commande $commande, LivrerRepository $livrerRepository, LivrerProduitRepository $livrerProduitRepository, ProduitRepository $repository,  PdfService $pdfService): Response
    {// traitement livraison

        if ($this->security->isGranted('ROLE_STOCK') || $this->security->isGranted('ROLE_FINANCE')) {

            if ($commande->getLivraison()) {

                $livrer = $livrerRepository->findBy(['commande' => $commande]);
               
                 return $pdfService->streamPdf(
            'livrer/historyshowpdf.html.twig', [
//                'commandes' => $commandeproduits,
                    'commandereference' => $commande,
                    'livrer' => $livrer,
                ],
            sprintf('livraison-%s.pdf', 1)
        );


            }
        } elseif ($this->security->isGranted('ROLE_CLIENT')) {

            if ($commande->getLivraison()) {
                 if($this->getUser()->getTuteur() === null){
                if($commande->getUser() != $this->getUser()){
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
             } else{
                if($commande->getUser() != $this->getUser()->getTuteur()){
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
                $livrer = $livrerRepository->findBy(['commande' => $commande]);
               

                 return $pdfService->streamPdf(
           'livrer/historyshowpdf.html.twig', [
//                'commandes' => $commandeproduits,
                    'commandereference' => $commande,
                    'livrer' => $livrer,
                ],
            sprintf('livraison-%s.pdf', 1)
        );

            }
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

    #[Route("/Livreur_Historique/{id}", name :"livreur_historique_show", methods : ["GET"]) ]
    public function livreurhistoriqueshow(Livrer $livrer): Response
    {// traitement livraison

        if ($this->security->isGranted('ROLE_LIVREUR') && $livrer->getLivreur() == $this->getUser()) {

            $response = $this->render('livrer/history_show_livreur.html.twig', [
//                'commandes' => $commandeproduits,
                'commandereference' => $livrer->getCommande(),
                'livrer' => $livrer,
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

    #[Route("/Historique_print/{id}", name :"historique_show_print", methods : ["GET"]) ]
    public function history_show_print(Commande $commande, LivrerRepository $livrerRepository, LivrerProduitRepository $livrerProduitRepository, ProduitRepository $repository, SessionInterface $session): Response
    {// traitement livraison
        if ($this->security->isGranted('ROLE_USER') || $this->security->isGranted('ROLE_BACK')) {

//            $panier = $session->get("livraison", []);
            if ($commande->getLivraison()) {
                $livrer = $livrerRepository->findBy(['commande' => $commande]);
//            $histo = [];
//            foreach ($livrer as $item) {
//                $histo[] = $item->getId();
//            }
//
//            $commandeproduits = $livrerProduitRepository->historique($histo);


                $response = $this->render('livrer/history_show_print.html.twig', [
//                'commandes' => $commandeproduits,
                    'commandereference' => $commande,
                    'livrer' => $livrer,
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

            }
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

    #[Route("/add/", name :"modif") ]
    public function modif(Request $request, ProduitRepository $produitRepository, SessionInterface $session)
    {
        // On récupère le panier actuel
        $livraison = $session->get("livraison", []);
        if ($request->isXmlHttpRequest()) {// traitement de la requete ajax
            $id = $request->get('prod');// recuperation de id produit
            $quantite = $request->get('quantite');// recuperation de la quantite commamde
            if (empty($livraison[$id])) {//verification existance produit dans le panier
//                $produit = $produitRepository->find($id); // recuperation de id produit dans la db
//                if($produit->getMincommande() <= $quantite) {// verification quantite minimum
//                    $produit->setQuantite($quantite);

                $livraison[$id] = $quantite;

                // On sauvegarde dans la session
                $session->set("livraison", $livraison);

                $res['id'] = 'ok';
//                    $res['panier'] = count($panier);
//                }
            } else {
                $res['id'] = 'no';
            }

            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
            return $response;
        }

    }

    #[Route("/details/", name :"details") ]
    public function details(Request $request, StockRepository $repository, SessionInterface $session)
    {
        // On récupère le panier actuel
        if ($request->isXmlHttpRequest()) {// traitement de la requete ajax
            $id = $request->get('prod');// recuperation de id produit
            $quantite = $request->get('quantite');// recuperation de la quantite commamde
            $stock = $repository->findBy(['produit' => $id]); // recuperation de id produit dans la db
            if (count($stock) > 0) {
                foreach ($stock as $stockproduit) {
                    $res[] = [
                        'id' => 'ok',
                        'lot' => $stockproduit->getLot(),
                        'peremption' => $stockproduit->getPeremption(),
                        'quantite' => $stockproduit->getQuantite(),
                        'stock' => $stockproduit->getQuantite(),
                    ];
                }
            } else {
                $res[] = [
                    'id' => 'no',
                    'lot' => 'indisponible',
                    'peremption' => 'indisponible',
                    'quantite' => 'indisponible',
                    'stock' => 'indisponible',
                ];
            }


            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
            return $response;
        }

    }

    #[Route("/valider_produit/", name :"valider_produit") ]
    public function validerproduit(Request $request, StockRepository $repository, SessionInterface $session)
    {
        // On récupère le panier actuel
//        if ($request->isXmlHttpRequest()) {// traitement de la requete ajax


        $id = $request->get('produit');// recuperation de id produit
        $chaine = $request->get('chaine');// recuperation de id produit
        $livraison = $session->get("traitement", []);
        $livraison[$id] = $chaine;


        $session->set("traitement", $livraison);


        $res['id'] = 'ok';

        $response = new Response();
        $response->headers->set('content-type', 'application/json');
        $re = json_encode($res);
        $response->setContent($re);
        return $response;
//        }

    }

    #[Route("/valider/{id}", name :"valider") ]
    public function valider(Request $request,ParameterBagInterface $params,HttpClientInterface $client, Commande $commande, CommandeProduitRepository $comprodrepository, ProduitRepository $repository, SessionInterface $session)
    {
        if ($this->security->isGranted('ROLE_STOCK')) {

//            $panier = $session->get("livraison", []);
            $livraison = $session->get("traitement", []);
            if (count($livraison) == 0) {
                $this->addFlash('notice', 'Donner les details des produits avant validation.');
                return $this->redirectToRoute('livraison_show', ['id' => $commande->getid()]);
            }
            $em = $this->entityManager;
            $commandeproduits = $comprodrepository->findBy(['commande' => $commande]);
            $livreur = $em->getRepository(User::class)->find($session->get('livreur'));

            $livrer = new Livrer($commande, $this->getUser());
            $livrer->setLivreur($livreur);
            $commande->setLivraison(true);
            $commande->setDatelivrer(new \DateTime());
            $commande->setLivreur($livreur);

            foreach ($commandeproduits as $commandeproduit) {
                $produit = $repository->find($commandeproduit->getProduit()->getId());
                if (isset($livraison[$produit->getId()])) {

                    $ug = $commandeproduit->getUg();
                   
                    // fin traitement promotion


                    $restetamp = false;
                    $restealivrer = 0;
                    $quantitelivrer = 0;

                    if ($commandeproduit->getQuantite() > $produit->getStock() && $produit->getStock() > 0) {
                        $restetamp = true;
                    }

                    $chaine = $livraison[$produit->getId()];
                    $ligne = explode("#", $chaine);


                    foreach ($ligne as $key => $item) {
                        $lot = explode("{/", $item);
                        if ($key != 0) {
                            $id = $lot[0];
                            $numerolot = $lot[1];
                            $quantite = $lot[2];
                            $livrerProduit = new LivrerProduit($livrer, $produit, $commandeproduit->getQuantite(), null, null, $commande, null, null);

                            $stock = $em->getRepository(Stock::class)->findOneBy(['produit' => $id, 'lot' => $numerolot]);

                            if (($stock->getQuantite() - $quantite) >= 0) {// livraison avec un stock suffisant

                                $produit->livraison($quantite);
                                $stock->setQuantite($stock->getQuantite() - $quantite);
                                $quantitelivrer = $quantitelivrer + $quantite;

                                $livrerProduit->setQuantitelivrer($quantite);
                                $livrerProduit->setArchive($stock->getQuantite());
                                $livrerProduit->setLot($numerolot);
                                $livrerProduit->setPeremption($stock->getPeremption());
                                $livrerProduit->setRestealivrer($restealivrer);

                                if ($ug != 0) $livrerProduit->setPromotion($commandeproduit->getPromotion());
                                if ($stock->getQuantite() == 0) {//suppression produit dans stock
                                    $em->remove($stock);
                                } else {
                                    $em->persist($stock);
                                }

                            } else {
                                goto terminer;
                            }
                            $em->persist($livrerProduit);
                        }

                    }
                    if ($restetamp == true || $quantitelivrer < $commandeproduit->getQuantite()) {

                        $reste = new LivrerReste($livrer, $commande, $produit, $commandeproduit->getQuantite(), $quantitelivrer, $commande->getUser());
                        $reste->setSession($commandeproduit->getSession());
                        if($commande->getCredit() == true) $reste->setCredit(true) ;
                        if($commandeproduit->getTva() != 0) $reste->setTva(true);

                        $em->persist($reste);
                        $livrer->setReste(true);
                    }

                    $em->persist($produit);
                } else {
                    $reste = new LivrerReste($livrer, $commande, $produit, $commandeproduit->getQuantite(), 0, $commande->getUser());
                    $reste->setSession($commandeproduit->getSession());
                    if($commande->getCredit() == true) $reste->setCredit(true) ;
                    if($commandeproduit->getTva() != 0) $reste->setTva(true);

                    $em->persist($reste);
                    $livrer->setReste(true);
                }
            }
            
            $em->persist($livrer);
            $em->persist($commande);
            $em->flush();
            $this->addFlash('notice', 'Livraison enregistrée avec succés');
            
            // envoie sms notification
             $text = "GNTPharma - sortie de stock\n Commande: ". $livrer->getCommande()->getId()."\n Client: ". $livrer->getCommande()->getUser()->getNom()."\n Montant: ".number_format($livrer->getCommande()->getMontant(), 2, ',', ' ');
            
            // try{
        
            //     $lam = new lamService($text, $client, $params);
            //     $lam->send();
                
            // }
            // catch(throwable $e){
                // try{
               
                //     $ws = new SMSService($params);//connect
                //     $ws->sendMessage($text);
                // }
                // catch(throwable $e){
                //     Goto suit;
                // }
            // }
            // fin sms
            // $response = $this->redirectToRoute('livraison_index');
            suit:
            $response = $this->redirectToRoute('livraison_historique_show_print', ['id' => $commande->getId()]);

            $session->remove('traitement');
            $session->remove('livreur');

            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;

            terminer:
            $this->addFlash('echec', 'Vérifier les quantités avant validation');
            $response = $this->redirectToRoute('livraison_show', ['id' => $commande->getId()]);

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

    #[Route("/restevalider/{id}", name :"reste_valider") ]
    public function reste_valider(Commande $commande, LivrerRepository $livrerrepository, LivrerResteRepository $livrerResteRepository, ProduitRepository $repository, SessionInterface $session)
    {
        if ($this->security->isGranted('ROLE_STOCK')) {


            $em = $this->entityManager;
            $commandeproduits = $livrerResteRepository->findBy(['commande' => $commande]);

            $oldlivrer = $livrerrepository->findOneBy(['commande' => $commande]);
            $oldlivrer->setReste(false);
            $em->persist($oldlivrer);
            $livreur = $em->getRepository(User::class)->find($session->get('livreur'));
            $livrer = new Livrer($commande, $this->getUser());
            $livrer->setLivreur($livreur);
            $commande->setLivraison(true);
            $commande->setDatelivrer(new \DateTime());
            $commande->setLivreur($livreur);

            foreach ($commandeproduits as $commandeproduit) {
                $produit = $repository->find($commandeproduit->getProduit()->getId());


                $livraison = $session->get("traitement", []);
                $restetamp = false;
                $restealivrer = 0;
                $chaine = $livraison[$produit->getId()];
                $ligne = explode("#", $chaine);
                foreach ($ligne as $key => $item) {
                    $lot = explode("{/", $item);
                    if ($key != 0) {
                        $id = $lot[0];
                        $numerolot = $lot[1];
                        $quantite = $lot[2];
                        $stock = $em->getRepository(Stock::class)->findOneBy(['produit' => $id, 'lot' => $numerolot]);
//                        if ($commandeproduit->getQuantite() > $produit->getStock() && $produit->getStock() > 0) {
//                            $restetamp = true;
//                        }

                        if (($stock->getQuantite() - $quantite) >= 0) {// livraison avec un stock suffisant

                            $produit->livraison($quantite);
                            $stock->setQuantite($stock->getQuantite() - $quantite);
                            $livrerProduit = new LivrerProduit($livrer, $produit, $commandeproduit->getQuantite(), $quantite, $stock->getQuantite(), $commande, $numerolot, $stock->getPeremption());

                            if ($stock->getQuantite() == 0) {//suppression produit dans stock
                                $em->remove($stock);
                            } else {
                                $em->persist($stock);
                            }
                            $em->persist($livrerProduit);
                            $reste = $livrerResteRepository->find($commandeproduit->getId());
                            $em->remove($reste);//suppression reste a livrer


                        }

                    }
                }

                $em->persist($produit);

            }


            $em->persist($livrer);
            $em->persist($commande);
            $em->flush();
            $this->addFlash('notice', 'Livraison enregistrée avec succés');
            $response = $this->redirectToRoute('livraison_new_reste_show_print', ['id' => $commande->getId(), 'livrer' => $livrer->getId()]);


            $session->remove('traitement');
            $session->remove('livreur');

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

    #[Route("/retourvalider/{id}", name :"retour_valider") ]
    public function retour_valider(Retour $retour, LivrerRepository $livrerrepository, RetourProduitRepository $retourProduitRepository, ProduitRepository $repository, SessionInterface $session)
    {
        if ($this->security->isGranted('ROLE_STOCK')) {

            $commande = $retour->getCommande();
            $em = $this->entityManager;
            $commandeproduits = $retourProduitRepository->findBy(['retour' => $retour, 'valider' => true, 'rembourser' => false]);

            $livreur = $em->getRepository(User::class)->find($session->get('livreur'));
            $livrer = new Livrer($commande, $this->getUser());
            $livrer->setLivreur($livreur);
            $livrer->setRetour($retour);
            $em->persist($retour);

            foreach ($commandeproduits as $commandeproduit) {
                $produit = $repository->find($commandeproduit->getProduit()->getId());
                $retourproduit = $retourProduitRepository->find($commandeproduit->getId());
                $retourproduit->setRembourser(true);


                $livraison = $session->get("traitement", []);
                $restetamp = false;
                $restealivrer = 0;
                $chaine = $livraison[$produit->getId()];
                $ligne = explode("#", $chaine);
                foreach ($ligne as $key => $item) {
                    $lot = explode("{/", $item);
                    if ($key != 0) {
                        $id = $lot[0];
                        $numerolot = $lot[1];
                        $quantite = $lot[2];
                        $stock = $em->getRepository(Stock::class)->findOneBy(['produit' => $id, 'lot' => $numerolot]);
//                        if($quantite == $commandeproduit->getQuantite()) {
                        if (($stock->getQuantite() - $quantite) >= 0) {// livraison avec un stock suffisant

                            $produit->livraison($quantite);
                            $stock->setQuantite($stock->getQuantite() - $quantite);
                            $livrerProduit = new LivrerProduit($livrer, $produit, $commandeproduit->getQuantite(), $quantite, $stock->getQuantite(), $commande, $numerolot, $stock->getPeremption());
                            $livrerProduit->setRetour($retour);
                            if ($stock->getQuantite() == 0) {//suppression produit dans stock
                                $em->remove($stock);
                            } else {
                                $em->persist($stock);
                            }
                            $em->persist($livrerProduit);
                            $em->remove($commandeproduit);//suppression reste a livrer


                        }
//                        }
//                        else{
//                            $this->addFlash('notice', "La quantité à livrer et la quantité retournée doivent être la même");
//                            $this->redirectToRoute('livraison_retour_show', ['id' => $retour->getId()]);
//                        }
                    }
                }

                $em->persist($produit);
                $em->persist($retourproduit);

            }


            $em->persist($livrer);
            $em->persist($commande);
            $em->flush();
            $this->addFlash('notice', 'Livraison enregistrée avec succés');
            $response = $this->redirectToRoute('livraison_retour_show_print', ['id' => $retour->getId()]);


            $session->remove('traitement');
            $session->remove('livreur');

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

    #[Route("/Creation_image/", name :"creation_image") ]
    public function creation_image(Request $request)
    {

        $chaine = $request->request->get('image');
        $liver = $request->request->get('livrer');
        $recepteur = $request->request->get('recepteur');
        // $nom = $request->request->get('nom');
        // $tel = $request->request->get('tel');


        $folderPath = __DIR__ . "/../../public";
//        $folderPath = "C:/wamp/www/gntpharm/public/Documents/";
        $image_parts = explode(";base64,", $chaine);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $filename = "/Documents/" . uniqid() . ".PNG";
        $file = $folderPath . $filename;
        file_put_contents($file, $image_base64);

        $em = $this->entityManager;
        $livraison = $em->getRepository(Livrer::class)->find($liver);
        // $client = $em->getRepository(Client::class)->find($recepteur);
        // $livraison->setRecepteur($client);
        $livraison->setNom($recepteur);
        $livraison->setLivrer(true);
        $livraison->getCommande()->setLivrer(true);
        $livraison->getCommande()->setDateefectlivraison(new \DateTime());
        $livraison->setSignature($filename);
        $livraison->setDateefectlivraison(new \DateTime());
        $em->persist($livraison);
        $em->persist($livraison->getCommande());
        $em->flush();
        $this->addFlash('notice', 'livraison réussie avec succès');

        $res['id'] = 'ok';

        $response = new Response();
        $response->headers->set('content-type', 'application/json');
        $re = json_encode($res);
        $response->setContent($re);
        return $response;
//        }

    }


    #[Route("/{id}", name :"delete", methods : ["POST"]) ]
    public function delete(Request $request, Livrer $livrer): Response
    {
        if ($this->isCsrfTokenValid('delete' . $livrer->getId(), $request->request->get('_token'))) {
            $entityManager = $this->entityManager;
            $entityManager->remove($livrer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('livrer_index', [], Response::HTTP_SEE_OTHER);
    }

    
    #[Route("/Client/Pharmauser/{id}", name :"pharmauser") ]
    public function pharmauser(LivrerRepository $repository, SessionInterface $session, CommandeRepository $commandeRepository, Client $client)
    {
        if ($this->security->isGranted('ROLE_CLIENT_ADMIN')) {
            $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]);
            
            $livrers = $repository->findBy(['recepteur' =>$client->getId()]);
            
            $response = $this->render('Client/reception.html.twig', [
                'livrers' => $livrers,
                'client' => $client,
                'panier' => $panier,
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
