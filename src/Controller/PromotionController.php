<?php

namespace App\Controller;

use App\Entity\Promotion;
use App\Entity\Produit;
use App\Entity\Panier;
use App\Entity\PromotionProduit;
use App\Form\PromotionType;
use App\Repository\ProduitRepository;
use App\Repository\PromotionProduitRepository;
use App\Repository\PromotionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;

#[Route("/{_locale}/Promotions") ]
class PromotionController extends AbstractController
{
    public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

#[Route("/", name :"promotion_index", methods : ["GET"]) ]
    public function index(SessionInterface $session, PromotionRepository $promotionRepository): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {


            $response = $this->render('promotion/admin/index.html.twig', [
                'promotions' => $promotionRepository->findAll(),
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
             $dataPanier = [];

            foreach ($panier as $commande) {
                $dataPanier[] = [
                    'produit' => $commande['produit'],
                ];
            }
            $response = $this->render('promotion/index.html.twig', [
                'promotions' => $promotionRepository->findAll(),
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

    #[Route("/new", name :"promotion_new", methods : ["GET","POST"]) ]
    public function new(Request $request, ProduitRepository $produitRepository, SessionInterface $session): Response
    {

        if ($this->security->isGranted('ROLE_ADMIN')) {

            $produits = $produitRepository->findAll();

            $promo = $session->get("promo", []);
            $dataPanier = [];

            foreach ($promo as $commande) {
                $dataPanier[] = [
                    "produit" => $commande['produit'],
                ];
            }
            $promotion = new Promotion();
            $form = $this->createForm(PromotionType::class, $promotion);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                $em = $this->entityManager;
                $promo = $session->get("promo", []);
                if (count($promo) >= 1) {
                    $promotion->setUser($this->getUser());
                    foreach ($promo as $product) {
                        $produit = $produitRepository->find($product['produit']->getId());
                        // $produit->setPromotion($promotion);
                        // $em->persist($produit);
                        $promotion->addProduit($produit);

                        
                    }
                   $em->persist($promotion);
                    $em->flush();
                    $session->remove("promo");
                    $this->addFlash('notice', 'Promotion crée');
                    $response = $this->redirectToRoute('promotion_index', [], Response::HTTP_SEE_OTHER);
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
                    $this->addFlash('danger', 'Veuillez ajouter des produits à la promotion');
                }
            }

            if ($request->isXmlHttpRequest()) {// traitement de la requete ajax
                $id = $request->get('prod');// recuperation de id produit
                $quantite = $request->get('quantite');// recuperation de la quantite commamde
                if (empty($promo[$id])) {//verification existance produit dans le panier
                    $produit = $produitRepository->find($id); // recuperation de id produit dans la db

                    $produit->setQuantite($quantite);

                    $promo[$id] = [// placement produit et quantite dans le panier
                        "produit" => $produit,
                    ];

                    // On sauvegarde dans la session
                    $session->set("promo", $promo);

                    $res['id'] = 'ok';
                    $res['ref'] = $produit->getReference();
                    $res['designation'] = $produit->getDesigantion();
                    $res['fabriquant'] = $produit->getFabriquant();
                    $res['quantite'] = $produit->getQuantite();

                } else {
                    $res['id'] = 'no';
                }

                $response = new Response();
                $response->headers->set('content-type', 'application/json');
                $re = json_encode($res);
                $response->setContent($re);
                return $response;
            }

            $response = $this->render('promotion/admin/new.html.twig', [
                'promotion' => $promotion,
                'produits' => $produits,
                'panier' => $dataPanier,
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

    #[Route("/PromotionsCourantes", name :"promotion_courante", methods : ["GET","POST"]) ]
    public function courante(SessionInterface $session, PromotionRepository $promotionRepository): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {


            $response = $this->render('promotion/admin/encours.html.twig', [
                'promotions' => $promotionRepository->Courante(),
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

             $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]); 

            $response = $this->render('promotion/encours.html.twig', [
                'promotions' => $promotionRepository->Courante(),
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

    
    #[Route("/PromotionsCourantes_pdf", name :"promotion_courante_pdf", methods : ["GET","POST"]) ]
    public function courantepdf(SessionInterface $session, PromotionRepository $promotionRepository, GotenbergPdfInterface $gotenberg): Response
    {
        if ($this->security->isGranted('ROLE_BACK') || $this->security->isGranted('ROLE_CLIENT')) {

            return $gotenberg
        ->html()
        ->content('promotion/admin/encourspdf.html.twig', [
                'promotions' => $promotionRepository->Courante(),
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


    #[Route("/add/", name :"promotion_add") ]
    public function add(Request $request, ProduitRepository $produitRepository, SessionInterface $session)
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            // On récupère le panier actuel
            $promo = $session->get("promo", []);
            if ($request->isXmlHttpRequest()) {// traitement de la requete ajax
                $id = $request->get('prod');// recuperation de id produit
                if (empty($promo[$id])) {//verification existance produit dans le panier
                    $produit = $produitRepository->find($id); // recuperation de id produit dans la db

                    $promo[$id] = [// placement produit et quantite dans le panier
                        "produit" => $produit,
                    ];

                    // On sauvegarde dans la session
                    $session->set("promo", $promo);

                    $res['id'] = 'ok';
                    $res['ref'] = $produit->getReference();
                    $res['designation'] = $produit->getDesigantion();
                    $res['fabriquant'] = $produit->getFabriquant();

                } else {
                    $res['id'] = 'no';
                }

                $response = new Response();
                $response->headers->set('content-type', 'application/json');
                $re = json_encode($res);
                $response->setContent($re);
                return $response;
            }
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

    #[Route("/delete", name :"promotion_delete") ]
    public function promodelete(Request $request, ProduitRepository $repository, SessionInterface $session)
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {

            // On récupère le panier actuel
            $promo = $session->get("promo", []);
            $id = $repository->find($request->get('prod'))->getId();

            if (!empty($promo[$id])) {
                unset($promo[$id]);
            }

            // On sauvegarde dans la session
            $session->set("promo", $promo);
            $res['id'] = 'ok';
            $res['nb'] = count($promo);
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
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

    #[Route("/arreter/{id}", name :"promotion_arreter") ]
    public function arreter(Request $request,Promotion $promotion, ProduitRepository $repository, SessionInterface $session)
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {

            if ($this->isCsrfTokenValid('arreter' . $promotion->getId(), $request->request->get('_token'))) {
                $entityManager = $this->entityManager;
                $promotion->setActive(false);
                foreach ($promotion->getProduits() as $produit) {
                    $produit->setPromotion(null);
                    $entityManager->persist($produit);
                }
                $entityManager->persist($promotion);
                $entityManager->flush();
                $this->addFlash('notice', 'Promotion arretée');
            }

            return $this->redirectToRoute('promotion_courante', [], Response::HTTP_SEE_OTHER);

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


    #[Route("/Activer/", name :"promotion_activer", methods : ["GET"]) ]
    public function activer(): Response
    {

        $date = new \DateTime();
        $em = $this->entityManager;
        // $start = $em->getRepository(Promotion::class)->findAll();
        $start = $em->getRepository(Promotion::class)->findBy(['debut' => $date]);
        $end = $em->getRepository(Promotion::class)->findBy(['fin' => $date]);


        foreach ($start as $promotion) {
            foreach($promotion->getProduits() as $produit){
                $produit->setPromotion($promotion);
                $em->persist($produit);
            }
           
        }
        
        foreach ($end as $promo) {
            foreach($promo->getProduits() as $produit){
                $produit->setPromotion($null);
            
                $em->persist($produit);
            }
           
        }
         $em->flush();
         return new Response("✅ tâches mises à jour.");
    }


    #[Route("/{id}", name :"promotion_show", methods : ["GET"]) ]
    public function show(Promotion $promotion, SessionInterface $session): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {

            return $this->render('promotion/admin/show.html.twig', [
                'promotion' => $promotion,
                // 'produitspromotion' => $produitrepo->findBy(['promotion' => $promotion])
            ]);
        } elseif ($this->security->isGranted('ROLE_CLIENT')) {
            $date = new \DateTime();
            $promo = 0;
            if ($promotion->getDebut() <= $date) {
                $promo = 1;
            }
            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]); 
             $dataPanier = [];
            $total = 0;

             foreach($panier as $commande){
                $commande->getProduit()->setQuantite($commande->getQuantite());
                $dataPanier[] = [
                    "produit" => $commande->getProduit(),
                    "promotion" => $commande->getReduction(),
                ];
            }

            return $this->render('promotion/show.html.twig', [
                'promotion' => $promotion,
                'panier' => $dataPanier,
                'promo' => $promo,
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

    

    #[Route("_pdf/{id}", name :"promotion_show_pdf", methods : ["GET"]) ]
    public function showpdf(Promotion $promotion, GotenbergPdfInterface $gotenberg): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {

          
            return $gotenberg
        ->html()
        ->content('promotion/admin/showpdf.html.twig', [
                'promotion' => $promotion,
                // 'produitspromotion' => $produitrepo->findBy(['promotion' => $promotion])
            ])
        ->fileName('promotion.pdf')
        ->generate()
        ->stream();

        } elseif ($this->security->isGranted('ROLE_CLIENT')) {
            $date = new \DateTime();
            $promo = 0;
            if ($promotion->getDebut() <= $date) {
                $promo = 1;
            }
            

            return $gotenberg
        ->html()
        ->content('promotion/showpdf.html.twig', [
                'promotion' => $promotion,
                'promo' => $promo,
            ])
        ->fileName('promotion.pdf')
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

    #[Route("/{id}/edit", name :"promotion_edit", methods : ["GET","POST"]) ]
    public function edit(Request $request,Promotion $promotion, ProduitRepository $produitRepository, SessionInterface $session): Response
    {

        if ($this->security->isGranted('ROLE_ADMIN')) {
            
        
            $promo = $session->get("promo", []);

            $produits = $produitRepository->findAll();

             if (!$request->isMethod('POST')) {
                foreach($promotion->getProduits() as $produit){
                    $id = $produit->getId();

                        $promo[$id] = [// placement produit et quantite dans le panier
                            "produit" => $produit,
                        ];

                        // On sauvegarde dans la session
                        
                }
                $session->set("promo", $promo);
             }

            
            $dataPanier = [];

            foreach ($promo as $commande) {
                $dataPanier[] = [
                    "produit" => $commande['produit'],
                ];
            }
            $form = $this->createForm(PromotionType::class, $promotion);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->entityManager;
                $promo = $session->get("promo", []);
                if (count($promo) >= 1) {

                   $editPromo = new Promotion();
                    foreach ($promo as $product) {
                        $produit = $produitRepository->find($product['produit']->getId());
                        $editPromo->addProduit($produit);
                        $date = new \Datetime();
                        if($promotion->getDebut() <=  $date){
                            $produit->setPromotion($promotion);
                            $em->persist($produit);

                        }
                        
                    }
                    $promotion->editPromo($editPromo->getProduits());
                    $em->persist($promotion);
                    // dd($promotion);;
                    $em->flush();
                    $session->remove("promo");
                    $this->addFlash('notice', 'Promotion modifiée');
                    $response = $this->redirectToRoute('promotion_courante', [], Response::HTTP_SEE_OTHER);
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
                    $this->addFlash('danger', 'Veuillez ajouter des produits à la promotion');
                }
            }

            return $this->render('promotion/admin/edit.html.twig', [
                'promotion' => $promotion,
                    'produits' => $produits,
                    'panier' => $dataPanier,
                    'form' => $form->createView(),
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

    #[Route("/cancel/{id}", name :"promotion_cancel", methods : ["POST"]) ]
    public function cancel(Request $request, Promotion $promotion): Response
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
        $date = new \Datetime();
        if ($this->isCsrfTokenValid('delete' . $promotion->getId(), $request->request->get('_token')) && $promotion->getDebut() > $date) {
            $entityManager = $this->entityManager;

            foreach ($promotion->getProduits() as $produit) {
                $produit->setPromotion(null);
                $entityManager->persist($produit);
            }
            $entityManager->remove($promotion);
            $entityManager->flush();
            $this->addFlash('notice', 'Promotion supprimée');
        }else{
             $this->addFlash('notice', 'Impossible de supprimer cette promotion');   
        }

        return $this->redirectToRoute('promotion_courante', [], Response::HTTP_SEE_OTHER);
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
}
