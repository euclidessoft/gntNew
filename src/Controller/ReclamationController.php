<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\ReclamationProduit;
use App\Entity\Commande;
use App\Entity\Pharmacie;
use App\Entity\Produit;
use App\Entity\Panier;
use App\Entity\User;
use App\Form\ReclamationType;
use App\Repository\LivrerProduitRepository;
use App\Repository\CommandeRepository;
use App\Repository\LivrerRepository;
use App\Repository\ProduitRepository;
use App\Repository\ReclamationRepository;
use App\Repository\ReclamationProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;

#[Route("/{_locale}/Commande_Reclamation") ]
class ReclamationController extends AbstractController
{
    public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

    #[Route("/", name :"reclamation_index", methods : ["GET"]) ]
    public function index(SessionInterface $session, ReclamationRepository $reclamationRepository): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {
            
            $response = $this->render('reclamation/admin/index.html.twig', [
                'reclamations' => $reclamationRepository->findBy(['cloture' => null]),
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

        }else if ($this->security->isGranted('ROLE_CLIENT')) {
            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]); 
              $dataPanier = [];
            
             $reclamations = $reclamationRepository->findBy(['pharmacie' => $this->getUser()->getPharmacie(), 'cloture' => null]);

            $response = $this->render('reclamation/index.html.twig', [
                'reclamations' => $reclamations,
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
        }else  {
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

    #[Route("/Cloturer/{user}", name :"reclamation_index_cloturer", methods : ["GET"]) ]
    public function clo(SessionInterface $session, ReclamationRepository $reclamationRepository, User $user): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {

            $response = $this->render('reclamation/admin/cloturer.html.twig', [
                'reclamations' => $reclamationRepository->findBy(['status' => true]),
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

        }elseif ($this->security->isGranted('ROLE_CLIENT')) {
             $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]); 
            
           
             $reclamations = $reclamationRepository->findBy(['pharmacie' => $user->getPharmacie()->getId(), 'status' => true]);

            $response = $this->render('reclamation/cloturer.html.twig', [
                'reclamations' => $reclamations,
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
        }else  {
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

     #[Route("/ListeCommande/{user}", name :"reclamation_liste_commande", methods : ["GET"]) ]
    public function list(CommandeRepository $repository, SessionInterface $session): Response
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {
            $commandes = [];
            if($this->getUser()->getTuteur() === null){
                $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]);
                $commandes = $repository->retourClient($this->getUser()->getId());
             }else{
                $commandes = $repository->retourClient($this->getUser()->getTuteur()->getId());
                $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]); 
             }
              $response = $this->render('reclamation/list.html.twig', [
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
    
    #[Route("/new/{commande}/{user}", name :"reclamation_new", methods : ["GET","POST"]) ]
    public function new(SessionInterface $session, Request $request,Commande $commande, Pharmacie $user, LivrerProduitRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {
            $session->remove('reclamation');
            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]); 
            //   $dataPanier = [];

            //   foreach($panier as $commande){
            //     $commande->getProduit()->setQuantite($commande->getQuantite());
            //     $dataPanier[] = [
            //         "produit" => $commande->getProduit(),
            //         "promotion" => $commande->getReduction(),
            //     ];
            // }
            // $reclamation = new Reclamation();
            // $reclamation->setUser($user);
            // $form = $this->createForm(ReclamationType::class, $reclamation, ['id' => $this->getUser()->getId()]);
            // $form->handleRequest($request);

            // if ($form->isSubmitted() && $form->isValid()) {
            //     $entityManager = $this->entityManager;
            //     $entityManager->persist($reclamation);
            //     $entityManager->flush();


            //     $response = $this->redirectToRoute('reclamation_index', ['user' => $user->getId()], Response::HTTP_SEE_OTHER);
            //     $response->setSharedMaxAge(0);
            //     $response->headers->addCacheControlDirective('no-cache', true);
            //     $response->headers->addCacheControlDirective('no-store', true);
            //     $response->headers->addCacheControlDirective('must-revalidate', true);
            //     $response->setCache([
            //         'max_age' => 0,
            //         'private' => true,
            //     ]);
            //     return $response;
            // }

            $response = $this->render('reclamation/new.html.twig', [
                'commande' => $commande,
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                'reclamation' => $session->get("reclamation", []),
                // 'form' => $form->createView(),
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
            $response = $this->redirectToRoute('security_login');
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

    
    #[Route("/add/", name :"reclamation_add") ]
    public function add(Request $request, ProduitRepository $produitRepository, SessionInterface $session)
    {
        // On récupère le panier actuel
        $retour = $session->get("reclamation", []);
        if ($request->isXmlHttpRequest()) {// traitement de la requete ajax
            $id = $request->get('prod');// recuperation de id produit
            $motif = $request->get('motif');// recuperation de id produit
            $comment = $request->get('comment');// recuperation de id produit
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
            $res['comment'] = $comment;//$produit->getQuantite();
            $retour[] = $res;


            // On sauvegarde dans la session
            $session->set("reclamation", $retour);
//
            suite:
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
            return $response;
        }

    }

    
    #[Route("/delete/", name :"reclamation_supprime") ]
    public function recdelete(Request $request, ProduitRepository $repository, SessionInterface $session)
    {
        // On récupère le panier actuel
        $retour = $session->get("reclamation", []);
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
        $session->set("reclamation", $retour);
        $res['id'] = 'ok';
        $res['nb'] = count($retour);
        $response = new Response();
        $response->headers->set('content-type', 'application/json');
        $re = json_encode($res);
        $response->setContent($re);
        return $response;
    }

    
    #[Route("/Reclamation_valider/", name :"reclamation_valider", methods : ["POST"]) ]
    public function reclamation_valider(Request $request, SessionInterface $session): Response
    {

        if ($this->security->isGranted('ROLE_CLIENT')) {

            $com = $request->get('commande');
            $em = $this->entityManager;
            $commande = $em->getRepository(Commande::class)->find($com);
            $produits = $session->get('reclamation', []);
            $reclamation =  $em->getRepository(Reclamation::class)->findOneBy(['commande' => $commande->getId()]);
            if($reclamation === null){
                $reclamation = new Reclamation();
                $reclamation->setCommande($commande);
                $reclamation->setPharmacie($this->getUser()->getPharmacie());
                $em->persist($reclamation);
            }
            foreach ($produits as $prod) {
                $produit = $em->getRepository(Produit::class)->find($prod['id']);
                $reclamationproduit = new ReclamationProduit();
                $reclamationproduit->setProduit($produit);
                // $reclamationproduit->setRetour($retour);
                $reclamationproduit->setCommande($commande);
                $reclamationproduit->setReclamation($reclamation);
                $reclamationproduit->setMotif($prod['motif']);
                $reclamationproduit->setCommentaire($prod['comment']);
                $reclamationproduit->setLot($prod['lot']);
                $reclamationproduit->setPeremption(new \Datetime($prod['peremption']));
                $reclamationproduit->setQuantite($prod['quantite']);
                $reclamationproduit->setPrix($produit->getPrix());
                $reclamationproduit->setPrixpublic($produit->getPrixpublic());
                $produit->getTva() == true ? $reclamationproduit->setTva($produit->getPrix() * 0.1925): $reclamationproduit->setTva(0);
                $em->persist($reclamationproduit);
                $em->flush();

            }
            // $em->flush();
            $this->addFlash('notice', 'Réclamation enregistée avec succès');
            $session->remove('reclamation');

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



    // #[Route("/new/{user}", name :"reclamation_new", methods : ["GET","POST"]) ]
    // public function new(SessionInterface $session, Request $request, User $user): Response
    // {
    //     if ($this->security->isGranted('ROLE_CLIENT')) {
    //         $this->getUser()->getTuteur() === null ?
    //          $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
    //          $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]); 
    //           $dataPanier = [];

    //           foreach($panier as $commande){
    //             $commande->getProduit()->setQuantite($commande->getQuantite());
    //             $dataPanier[] = [
    //                 "produit" => $commande->getProduit(),
    //                 "promotion" => $commande->getReduction(),
    //             ];
    //         }
    //         $reclamation = new Reclamation();
    //         $reclamation->setUser($user);
    //         $form = $this->createForm(ReclamationType::class, $reclamation, ['id' => $this->getUser()->getId()]);
    //         $form->handleRequest($request);

    //         if ($form->isSubmitted() && $form->isValid()) {
    //             $entityManager = $this->entityManager;
    //             $entityManager->persist($reclamation);
    //             $entityManager->flush();


    //             $response = $this->redirectToRoute('reclamation_index', ['user' => $user->getId()], Response::HTTP_SEE_OTHER);
    //             $response->setSharedMaxAge(0);
    //             $response->headers->addCacheControlDirective('no-cache', true);
    //             $response->headers->addCacheControlDirective('no-store', true);
    //             $response->headers->addCacheControlDirective('must-revalidate', true);
    //             $response->setCache([
    //                 'max_age' => 0,
    //                 'private' => true,
    //             ]);
    //             return $response;
    //         }

    //         $response = $this->render('reclamation/new.html.twig', [
    //             'reclamation' => $reclamation,
    //             'form' => $form->createView(),
    //             'panier' => $dataPanier,
    //         ]);
    //         $response->setSharedMaxAge(0);
    //         $response->headers->addCacheControlDirective('no-cache', true);
    //         $response->headers->addCacheControlDirective('no-store', true);
    //         $response->headers->addCacheControlDirective('must-revalidate', true);
    //         $response->setCache([
    //             'max_age' => 0,
    //             'private' => true,
    //         ]);
    //         return $response;
    //     } else {
    //         $response = $this->redirectToRoute('security_login');
    //         $response->setSharedMaxAge(0);
    //         $response->headers->addCacheControlDirective('no-cache', true);
    //         $response->headers->addCacheControlDirective('no-store', true);
    //         $response->headers->addCacheControlDirective('must-revalidate', true);
    //         $response->setCache([
    //             'max_age' => 0,
    //             'private' => true,
    //         ]);
    //         return $response;
    //     }
    // }

    #[Route("/Traiter/{reclamation}", name :"reclamation_cloturer", methods : ["GET","POST"]) ]
    public function cloturer(SessionInterface $session, Request $request, Reclamation $reclamation): Response
    {
        if ($this->security->isGranted('ROLE_BACK') && $this->isCsrfTokenValid('cloturer' . $reclamation->getId(), $request->request->get('_token'))) {
            $em = $this->entityManager;
            $reclamation->setCloture(new \DateTime());
            $reclamation->setUsercloture($this->getUser());
            $reclamation->setStatus(true);

            $this->addFlash('notice', 'Reclamation  clocturée');
            $em->persist($reclamation);
            $em->flush();
                $response = $this->redirectToRoute('reclamation_index', ['user' => $this->getUser()->getId()], Response::HTTP_SEE_OTHER);
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
            $response = $this->redirectToRoute('security_login');
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

    #[Route("/{id}/", name :"reclamation_show", methods : ["GET"]) ]
    public function show(Reclamation $reclamation, ReclamationProduitRepository $repository, LivrerProduitRepository $livrerProduitRepository, SessionInterface $session): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {



            $commandeproduits = $livrerProduitRepository->findBy(['commande' => $reclamation->getCommande()]);
            $reclamations = $repository->findBy(['commande' => $reclamation->getCommande()]);
            return $this->render('reclamation/admin/show.html.twig', [
                'reclamation' => $reclamation,
                'commandes' => $commandeproduits,
                'reclamations' => $reclamations,
            ]);
        } else if ($this->security->isGranted('ROLE_CLIENT')) {
            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]); 
         
            $commandeproduits = $livrerProduitRepository->findBy(['commande' => $reclamation->getCommande()]);
            $reclamations = $repository->findBy(['commande' => $reclamation->getCommande()]);
            return $this->render('reclamation/show.html.twig', [
                'reclamation' => $reclamation,
                'commandes' => $commandeproduits,
                'reclamations' => $reclamations,
                'panier' => $panier,
            ]);
        } else {
            $response = $this->redirectToRoute('security_login');
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

    
    #[Route("_pdf/{id}/", name :"reclamation_show_pdf", methods : ["GET"]) ]
    public function showpdf(Reclamation $reclamation, ReclamationProduitRepository $repository, LivrerProduitRepository $livrerProduitRepository, GotenbergPdfInterface $gotenberg): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {



            $commandeproduits = $livrerProduitRepository->findBy(['commande' => $reclamation->getCommande()]);
            $reclamations = $repository->findBy(['commande' => $reclamation->getCommande()]);
           
             return $gotenberg
        ->html()
        ->content('reclamation/admin/showpdf.html.twig', [
                'reclamation' => $reclamation,
                'commandes' => $commandeproduits,
                'reclamations' => $reclamations,
            ])
        ->fileName('facture.pdf')
        ->generate()
        ->stream();
        } else if ($this->security->isGranted('ROLE_CLIENT')) {
           
            $commandeproduits = $livrerProduitRepository->findBy(['commande' => $reclamation->getCommande()]);
            $reclamations = $repository->findBy(['commande' => $reclamation->getCommande()]);
            
            return $gotenberg
        ->html()
        ->content('reclamation/showpdf.html.twig', [
                'reclamation' => $reclamation,
                'commandes' => $commandeproduits,
                'reclamations' => $reclamations,
            ])
        ->fileName('facture.pdf')
        ->generate()
        ->stream();
        } else {
            $response = $this->redirectToRoute('security_login');
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

    #[Route("/{id}/edit", name :"reclamation_edit", methods : ["GET","POST"]) ]
    public function edit(Request $request, Reclamation $reclamation): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name :"reclamation_delete", methods : ["POST"]) ]
    public function delete(Request $request, Reclamation $reclamation): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->request->get('_token'))) {
            $entityManager = $this->entityManager;
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('reclamation_index', [], Response::HTTP_SEE_OTHER);
    }
}
