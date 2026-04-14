<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Panier;
use App\Entity\User;
use App\Form\ProduitType;
use App\Repository\ApprovisionnementRepository;
use App\Repository\ApprovisionnerRepository;
use App\Repository\CommandeProduitRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

#[Route("/{_locale}/produit") ]
class ProduitController extends AbstractController
{
    public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

#[Route("/", name :"produit_index", methods : ["GET"]) ]
    public function index(SessionInterface $session, ProduitRepository $produitRepository): Response
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {

         $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]);    
        $dataPanier = [];

              foreach($panier as $commande){
                $commande->getProduit()->setQuantite($commande->getQuantite());
                $dataPanier[] = [
                    "produit" => $commande->getProduit(),
                    "promotion" => $commande->getReduction(),
                ];
            }

            $response = $this->render('produit/index.html.twig', [
                'produits' => $produitRepository->findAll(),
                'panier' => $dataPanier,
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
        else if ($this->security->isGranted('ROLE_BACK')) {

            $response = $this->render('produit/admin/index.html.twig', [
                'produits' => $produitRepository->findAll(),
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
        } else  {
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
    
    #[Route("/Promotions", name :"produit_promo", methods : ["GET"]) ]
    public function promo(SessionInterface $session, ProduitRepository $produitRepository): Response
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {

            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]); 
             $dataPanier = [];

              foreach($panier as $commande){
                $commande->getProduit()->setQuantite($commande->getQuantite());
                $dataPanier[] = [
                    "produit" => $commande->getProduit(),
                    "promotion" => $commande->getReduction(),
                ];
            }

            $response = $this->render('produit/promotions.html.twig', [
                'produits' => $produitRepository->promo(),
                'panier' => $dataPanier,
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
        else if ($this->security->isGranted('ROLE_BACK')) {

            $response = $this->render('produit/admin/index.html.twig', [
                'produits' => $produitRepository->findAll(),
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
        } else  {
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

    #[Route("/new", name :"produit_new", methods : ["GET","POST"]) ]
    public function new(Request $request): Response
    {
        if ($this->security->isGranted('ROLE_STOCK')) {
            $produit = new Produit();
            $form = $this->createForm(ProduitType::class, $produit);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if($produit->getPrix() < $produit->getPrixpublic()) {
                    $entityManager = $this->entityManager;
                    $entityManager->persist($produit);
                    $entityManager->flush();
                    $this->addFlash('notice', 'Nouveau Produit Ajouté');

                    $response = $this->redirectToRoute('produit_index', [], Response::HTTP_SEE_OTHER);
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
                else{
                    $this->addFlash('notice', 'Le prix public doit être strictement supérieur au prix de cession');
                }
            }
            $response = $this->render('produit/admin/new.html.twig', [
                'produit' => $produit,
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

    #[Route("/Nouveaute", name :"produit_nouveaute", methods : ["GET"]) ]
    public function nouveaute(SessionInterface $session, ProduitRepository $produitRepository): Response
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {

             $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]); 
             $dataPanier = [];

              foreach($panier as $commande){
                $commande->getProduit()->setQuantite($commande->getQuantite());
                $dataPanier[] = [
                    "produit" => $commande->getProduit(),
                    "promotion" => $commande->getReduction(),
                ];
            }

            $response = $this->render('produit/nouveaute.html.twig', [
                'produits' => $produitRepository->nouveaute(),
                'panier' => $dataPanier,
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

    #[Route("/Arrivage", name :"produit_arrivage", methods : ["GET"]) ]
    public function arrivage(SessionInterface $session, ApprovisionnerRepository $approvisionnerRepository, ApprovisionnementRepository $approvisionnementRepository): Response
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {

           $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]); 
               $dataPanier = [];

              foreach($panier as $commande){
                $commande->getProduit()->setQuantite($commande->getQuantite());
                $dataPanier[] = [
                    "produit" => $commande->getProduit(),
                    "promotion" => $commande->getReduction(),
                ];
            }
            $approvisionner = $approvisionnerRepository->arrivage();//recuperation des approvisioonement de moins de 7 jours
            if(count($approvisionner) > 1){ // si plus d' un appron
                $appro = [];
                foreach ($approvisionner as $item){// mettre les id approvisionner dans un tableau
                    $appro[] = $item->getId();
                }
                $approvisionnements = $approvisionnementRepository->arrivage($appro);// recuperation des approvisionnement des id dans le tableau
            }else{
                $approvisionnements = $approvisionnementRepository->findBy(['approvisionner' => $approvisionner]);
            }

            $response = $this->render('produit/arrivage.html.twig', [
                'approvisionnements' => $approvisionnements,
                'panier' => $dataPanier,
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

    #[Route("/Arrivage_admin", name :"produit_arrivage_admin", methods : ["GET"]) ]
    public function arrivage_admin(SessionInterface $session, ApprovisionnerRepository $approvisionnerRepository, ApprovisionnementRepository $approvisionnementRepository): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {

            $approvisionner = $approvisionnerRepository->arrivage();//recuperation des approvisioonement de moins de 7 jours
            if(count($approvisionner) > 1){ // si plus d' un appron
                $appro = [];
                foreach ($approvisionner as $item){// mettre les id approvisionner dans un tableau
                    $appro[] = $item->getId();
                }
                $approvisionnements = $approvisionnementRepository->arrivage($appro);// recuperation des approvisionnement des id dans le tableau
            }else{
                $approvisionnements = $approvisionnementRepository->findBy(['approvisionner' => $approvisionner]);
            }

            $response = $this->render('produit/admin/arrivage.html.twig', [
                'approvisionnements' => $approvisionnements,
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

    #[Route("/Vente", name :"produit_vente", methods : ["GET"]) ]
    public function vente(SessionInterface $session, CommandeProduitRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {

            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]); 
              $dataPanier = [];

             foreach($panier as $commande){
                $commande->getProduit()->setQuantite($commande->getQuantite());
                $dataPanier[] = [
                    "produit" => $commande->getProduit(),
                    "promotion" => $commande->getReduction(),
                ];
            }
            // dd($dataPanier);
            $ventemensuel = $repository->achatmensuel($this->getUser()->getId());
            $venteannuel = $repository->achatannuel($this->getUser()->getId());//


            $response = $this->render('produit/vente.html.twig', [
                'ventemensuel' => $ventemensuel,
                'venteannuel' => $venteannuel,
                'panier' => $dataPanier,
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

 #[Route("/Vente_admin", name :"produit_vente_admin", methods : ["GET"]) ]
    public function vente_admin(SessionInterface $session, CommandeProduitRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {


            $ventemensuel = $repository->ventemensuel();
            $venteannuel = $repository->venteannuel();//


            $response = $this->render('produit/admin/vente.html.twig', [
                'ventemensuel' => $ventemensuel,
                'venteannuel' => $venteannuel,
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

    #[Route("/MonTop", name :"produit_top", methods : ["GET"]) ]
    public function top(SessionInterface $session, CommandeProduitRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {

            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]); 
              $dataPanier = [];

              foreach($panier as $commande){
                $commande->getProduit()->setQuantite($commande->getQuantite());
                $dataPanier[] = [
                    "produit" => $commande->getProduit(),
                    "promotion" => $commande->getReduction(),
                ];
            }

            $topmensuel = $repository->topmensuel($this->getUser());
//            $venteannuel = $repository->topannuel($this->getUser());//


            $response = $this->render('produit/top.html.twig', [
                'ventemensuel' => $topmensuel,
//                'venteannuel' => $venteannuel,
                'panier' => $dataPanier,
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

    #[Route("/{id}", name :"produit_show", methods : ["GET"]) ]
    public function show(Produit $produit): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {

            $response = $this->render('produit/admin/show.html.twig', [
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
        } else if ($this->security->isGranted('ROLE_CLIENT')) {

            $response = $this->render('produit/show.html.twig', [
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

    #[Route("/{id}/edit", name :"produit_edit", methods : ["GET","POST"]) ]
    public function edit(Request $request, Produit $produit): Response
    {

        if ($this->security->isGranted('ROLE_STOCK')) {
            $prixtamp = $produit;
            $form = $this->createForm(ProduitType::class, $produit);
            if (!$this->security->isGranted('ROLE_ADMIN')) {
                $form->remove('prix');
                $form->remove('prixpublic');
                $form->remove('pght');
            }
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                if($produit->getPrix() < $produit->getPrixpublic()){
                    
                // if($produit->getPrix() != $prixtamp->getPrix() 
                //     || $produit->getPrixpublic() != $prixtamp->getPrixpublic() 
                // || $produit->getPght() != $prixtamp->getPght() ){

                //     if ($this->security->isGranted('ROLE_ADMIN')) {
                //             $this->entityManager->flush();
                //             $this->addFlash('notice', 'Produit modifié avec succès');

                //             $response = $this->redirectToRoute('produit_show', ['id' => $produit->getId()], Response::HTTP_SEE_OTHER);
                //             $response->setSharedMaxAge(0);
                //             $response->headers->addCacheControlDirective('no-cache', true);
                //             $response->headers->addCacheControlDirective('no-store', true);
                //             $response->headers->addCacheControlDirective('must-revalidate', true);
                //             $response->setCache([
                //                 'max_age' => 0,
                //                 'private' => true,
                //             ]);
                //             return $response;

                //     }
                //     else{
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
                    
                    $this->entityManager->flush();
                    $this->addFlash('notice', 'Produit modifié avec succès');

                    $response = $this->redirectToRoute('produit_show', ['id' => $produit->getId()], Response::HTTP_SEE_OTHER);
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
                else{
                    $this->addFlash('notice', 'Le prix public doit être strictement supérieur au prix de cession');
                }


            }

            $response = $this->render('produit/admin/edit.html.twig', [
                'produit' => $produit,
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



    #[Route("/{id}", name :"produit_delete", methods : ["POST"]) ]
    public function delete(Request $request, Produit $produit): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete' . $produit->getId(), $request->request->get('_token'))) {
                $entityManager = $this->entityManager;
                $entityManager->remove($produit);
                $entityManager->flush();
            }
            return $this->redirectToRoute('produit_index', [], Response::HTTP_SEE_OTHER);
        }
        catch (\Exception $exception){
            $this->addFlash('notice', 'Ce produit ne peut être supprimer pour des raisons de traçabilité');
            return $this->redirectToRoute('produit_index', [], Response::HTTP_SEE_OTHER);

        }


    }


}
