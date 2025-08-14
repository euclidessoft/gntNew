<?php

namespace App\Controller;

use App\Entity\Promotion;
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
            $panier = $session->get("panier", []);
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
                    $em->persist($promotion);
                    foreach ($promo as $product) {
                        $produit = $produitRepository->find($product['produit']->getId());
                        // $produit->setPromotion($promotion);
                        //$em->persist($produit);
                        $promotionproduit = new PromotionProduit($produit, $promotion);

                        $em->persist($promotionproduit);
                    }
                    // dd($promotion);;
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

            $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]);

            $response = $this->render('promotion/encours.html.twig', [
                'promotions' => $promotionRepository->CouranteClient(),
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

    #[Route("/arreter", name :"promotion_arreter") ]
    public function arreter(Request $request, ProduitRepository $repository, SessionInterface $session)
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


    #[Route("/Activer/", name :"promotion_activer", methods : ["GET"]) ]
    public function activer(PromotionProduitRepository $produitrepo, SessionInterface $session): Response
    {

        $date = new \DateTime();
        $em = $this->entityManager;
        $start = $em->getRepository(Promotion::class)->findBy(['debut' => $date]);
        $end = $em->getRepository(Promotion::class)->findBy(['fin' => $date]);


        foreach ($start as $promo) {
            $promoproduits = $produitrepo->findBy(['promotion' => $promo]);
            foreach ($promoproduits as $promoproduit) {
                $promoproduit->getProduit()->setPromotion($promo);
                $em->persist($promoproduit);
            }
            $em->flush();
        }
        return $this->render('promotion/admin/index.html.twig', [
            'promotions' => $end,
        ]);

    }


    #[Route("/{id}", name :"promotion_show", methods : ["GET"]) ]
    public function show(Promotion $promotion, PromotionProduitRepository $produitrepo, SessionInterface $session): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {

            return $this->render('promotion/admin/show.html.twig', [
                'promotion' => $promotion,
                'produitspromotion' => $produitrepo->findBy(['promotion' => $promotion])
            ]);
        } elseif ($this->security->isGranted('ROLE_CLIENT')) {
            $date = new \DateTime();
            $promo = 0;
            if ($promotion->getDebut() <= $date) {
                $promo = 1;
            }
            $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]);
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
                'produitspromotion' => $produitrepo->findBy(['promotion' => $promotion]),
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

    #[Route("/{id}/edit", name :"promotion_edit", methods : ["GET","POST"]) ]
    public function edit(Request $request, Promotion $promotion): Response
    {
        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('promotion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('promotion/admin/edit.html.twig', [
            'promotion' => $promotion,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/cancel/{id}", name :"promotion_cancel", methods : ["POST"]) ]
    public function cancel(Request $request, Promotion $promotion): Response
    {
        if ($this->isCsrfTokenValid('delete' . $promotion->getId(), $request->request->get('_token'))) {
            $entityManager = $this->entityManager;
            $promotionproduits = $entityManager->getRepository(PromotionProduit::class)->findBy(['promotion' => $promotion]);
            foreach ($promotionproduits as $promotionproduit) {
                $entityManager->remove($promotionproduit);
            }
            $entityManager->remove($promotion);
            $entityManager->flush();
            $this->addFlash('notice', 'Promotion supprimée');
        }

        return $this->redirectToRoute('promotion_index', [], Response::HTTP_SEE_OTHER);
    }
}
