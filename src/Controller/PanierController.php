<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Panier;
use App\Repository\ApprovisionnementRepository;
use App\Repository\ApprovisionnerRepository;
use App\Repository\AvoirRepository;
use App\Repository\CommandeProduitRepository;
use App\Repository\CommandeRepository;
use App\Repository\LivrerRepository;
use App\Repository\ProduitRepository;
use App\Repository\PromotionRepository;
use App\Repository\ReclamationRepository;
use App\Repository\StockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/{_locale}/Commande", name :"commande_") ]
class PanierController extends AbstractController
{
       public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }
    #[Route("/", name :"index") ]
    public function index(StockRepository $stockRepository, LivrerRepository $livrerRepository, ReclamationRepository $reclamationRepository,SessionInterface $session, ProduitRepository $produitRepository, CommandeProduitRepository $repository,ApprovisionnerRepository $approvisionnerRepository, ApprovisionnementRepository $approvisionnementRepository, PromotionRepository $promotionRepository, AvoirRepository $avoirRepository, CommandeRepository $commandeRepository)
    {
        $approvisionnements=[];
        $approvisionner = $approvisionnerRepository->arrivage();//recuperation des approvisioonement de moins de 7 jours
        if(count($approvisionner) > 0){ // si plus d' un appron
            $appro = [];
            foreach ($approvisionner as $item){// mettre les id approvisionner dans un tableau
                $appro[] = $item->getId();
            }
            $approvisionnements = $approvisionnementRepository->arrivage($appro);// recuperation des approvisionnement des id dans le tableau
        }
        if ($this->security->isGranted('ROLE_LABORATOIRE')) {

            $response = $this->render('commande/admin/dashbord_laboratoire.html.twig', [
                'laboratoire' => $this->getUser(),
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
        else if ($this->security->isGranted('ROLE_CLIENT')) {

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
            $top = $repository->topmensuel($this->getUser());//recperation mon top
            $vente = $repository->achatmensuel($this->getUser()->getId());
            $promotion = $promotionRepository->Courante();
            $nouveaute = $produitRepository->nouveaute();
            $avoir = $avoirRepository->findby(['client' => $this->getUser()]);
            $dette = $commandeRepository->findBy(['payer' => false, 'user' => $this->getUser()]);
            $reclamation = $reclamationRepository->findBy(['pharmacie' => $this->getUser()->getPharmacie(), 'cloture' => null]);




            $response = $this->render('commande/dashbord.html.twig', [
                'top' => $top,
                'arrivage'=> $approvisionnements,
                'vente' => $vente,
                'promotion' => $promotion,
                'nouveaute' => $nouveaute,
                'avoir' => $avoir,
                'dette' => $dette,
                'reclamation' => $reclamation,
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
        else if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_SUPERVISEUR')) {

            $response = $this->render('commande/admin/dashbord.html.twig', [
                'commande' => $commandeRepository->findBy(['suivi' => false]),
                'livraison' => $commandeRepository->findBy(['livraison' => false, 'suivi' => true]),
                'vente' => $repository->ventemensuel(),
                'promotion' => $promotionRepository->Courante(),
                'produit' => $produitRepository->findBY(['stock' => 0]),
                'stock' => $produitRepository->surveil(),
                'reclamation' => $reclamationRepository->findBy(['cloture' => null]),
                'arrivage'=> $approvisionnements,
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
        else if ($this->security->isGranted('ROLE_FINANCE')) {

            $response = $this->render('commande/admin/dashbord_finance.html.twig', [
                'vente' => $repository->ventemensuel(),
                'promotion' => $promotionRepository->Courante(),
                'commande' => $commandeRepository->findBy(['credit' => false, 'suivi' => false]),
                'avoir' => $avoirRepository->findAll(),
                'reclamation' => $reclamationRepository->findBy(['cloture' => null]),
                'credit' => $commandeRepository->findBy(['credit' => true, 'suivi' => false,]),
                'commande_credit' => $commandeRepository->findBy(['credit' => true, 'suivi' => true, 'payer' => false]),
                'produit' => $produitRepository->findBY(['stock' => 0]),
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
        else if ($this->security->isGranted('ROLE_CAISSIER')) {

            $response = $this->render('commande/admin/dashbord_caisse.html.twig', [
                'vente' => $repository->ventemensuel(),
                'promotion' => $promotionRepository->Courante(),
                'commande' => $commandeRepository->findBy(['credit' => false, 'suivi' => false]),
                'avoir' => $avoirRepository->findAll(),
                'reclamation' => $reclamationRepository->findBy(['cloture' => null]),
                'credit' => $commandeRepository->findBy(['credit' => true, 'suivi' => false,]),
                'commande_credit' => $commandeRepository->findBy(['credit' => true, 'suivi' => true, 'payer' => false]),
                'produit' => $produitRepository->findBY(['stock' => 0]),
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
        else if ($this->security->isGranted('ROLE_STOCK')) {

            $response = $this->render('commande/admin/dashbord_stock.html.twig', [
                'produit' => $produitRepository->findBY(['stock' => 0]),
                'stock' => $produitRepository->surveil(),
                'livraison' => $commandeRepository->findBy(['suivi' => true, 'livraison' => false]),
//                'vente' => $repository->ventemensuel(),
                'promotion' => $promotionRepository->Courante(),
                'reclamation' => $reclamationRepository->findBy(['cloture' => null]),
                'reste' => $livrerRepository->findBy(['reste' => true]),
                'produit_stock' => $stockRepository->stock(),
                'peremption' => $stockRepository->peremption(),
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
        elseif ($this->security->isGranted('ROLE_LIVREUR')) {

            $response = $this->render('commande/admin/dashbord_livreur.html.twig', [
                'deja' => $livrerRepository->findBy(['livreur' => $this->getUser()->getId(), 'livrer' => true]),
                'attente' => $livrerRepository->findBy(['livreur' => $this->getUser()->getId(), 'livrer' => false]),
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
        elseif ($this->security->isGranted('ROLE_RH')) {

            $response = $this->render('dashboard/rh.html.twig', [
                'user' => $this->getUser(),
//                'deja' => $livrerRepository->findBy(['livreur' => $this->getUser()->getId(), 'livrer' => true]),
//                'attente' => $livrerRepository->findBy(['livreur' => $this->getUser()->getId(), 'livrer' => false]),
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
        elseif ($this->security->isGranted('ROLE_EMPLOYER')) {

            $response = $this->render('security/security/admin/profile.html.twig', [
                'user' => $this->getUser(),
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
        else {
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

    #[Route("/add/", name :"add") ]
    public function add(Request $request, ProduitRepository $produitRepository, SessionInterface $session, PromotionRepository $promotionRepository)
    {
        // On récupère le panier actuel
        $panier = $session->get("panier", []);
        if( $request->isXmlHttpRequest() )
        {// traitement de la requete ajax
            $id = $request->get('prod');// recuperation de id produit
            $quantite = $request->get('quantite');// recuperation de la quantite commamde
            $reduction = 0;
            if(empty($panier[$id])){//verification existance produit dans le panier
                $produit = $produitRepository->find($id); // recuperation de id produit dans la db
                if(!empty($produit->getPromotion())) {// verification promo reduction
                if(!empty($produit->getPromotion()->getReduction())) {
                    $reduction = $produit->getPromotion()->getReduction();
                }
                }
                if($produit->getMincommande() <= $quantite) {// verification quantite minimum
                    $produit->setQuantite($quantite);

                    $panier[$id] = [// placement produit et quantite dans le panier
                        "produit" => $produit,
                        "promotion" => $reduction,
                    ];

                    // On sauvegarde dans la session
                    $session->set("panier", $panier);

                    $res['id'] = 'ok';
                    $res['panier'] = count($panier);
                }
            }else{
                $res['id'] = 'no';
            }

            $response = new Response();
            $response->headers->set('content-type','application/json');
            $re = json_encode($res);
            $response->setContent($re);
            return $response;
        }

    }

    #[Route("/addinPanier/", name :"addin") ]
    public function addPanier(Request $request, ProduitRepository $produitRepository, SessionInterface $session, PromotionRepository $promotionRepository)
    {
        // On récupère le panier actuel
        // $panier = $session->get("panier", []);
        if( $request->isXmlHttpRequest() )
        {// traitement de la requete ajax
        $panier = [];
            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]);;
            $id = $request->get('prod');// recuperation de id produit
            $quantite = $request->get('quantite');// recuperation de la quantite commamde
            $reduction = 0;
            // if(empty($panier[$id])){//verification existance produit dans le panier
                $produit = $produitRepository->find($id); // recuperation de id produit dans la db
                if(!empty($produit->getPromotion())) {// verification promo reduction
                if(!empty($produit->getPromotion()->getReduction())) {
                    $reduction = $produit->getPromotion()->getReduction();
                }
                }
                if($produit->getMincommande() <= $quantite) {// verification quantite minimum
                   $pan = new Panier();
                   $pan->setProduit($produit);
                    $this->getUser()->getTuteur() === null ? $pan->setClient($this->getUser()) : $pan->setClient($this->getUser()->getTuteur());
                   $pan->setQuantite($quantite);
                   $reduction != 0 ? $pan->setReduction($reduction) : null;
                   $this->entityManager->persist($pan);
                   $this->entityManager->flush();


                    $res['id'] = 'ok';
                    $res['panier'] = count($panier)+1;
                }
            // }else{
            //     $res['id'] = 'no';
            // }

            $response = new Response();
            $response->headers->set('content-type','application/json');
            $re = json_encode($res);
            $response->setContent($re);
            return $response;
        }

    }


    #[Route("/addextranet/", name :"add_extranet") ]
    public function addextranet(Request $request, ProduitRepository $produitRepository, SessionInterface $session, PromotionRepository $promotionRepository)
    {
        // On récupère le panier actuel
        $panier = $session->get("panier", []);
        if( $request->isXmlHttpRequest() )
        {// traitement de la requete ajax
            $id = $request->get('prod');// recuperation de id produit
            $quantite = $request->get('quantite');// recuperation de la quantite commamde
            $reduction = 0;
            if(empty($panier[$id])){//verification existance produit dans le panier
                $produit = $produitRepository->find($id); // recuperation de id produit dans la db
                if(!empty($produit->getPromotion())) {// verification promo reduction
                if(!empty($produit->getPromotion()->getReduction())) {
                    $reduction = $produit->getPromotion()->getReduction();
                }
                }
                if($produit->getMincommande() <= $quantite) {// verification quantite minimum
                    $produit->setQuantite($quantite);

                    $panier[$id] = [// placement produit et quantite dans le panier
                        "produit" => $produit,
                        "promotion" => $reduction,
                    ];

                    // On sauvegarde dans la session
                    $session->set("panier", $panier);

                    $res['id'] = 'ok';
                    $res['ref'] = $produit->getReference();
                    $res['designation'] = $produit->getDesigantion();
                    $res['fabriquant'] = $produit->getFabriquant();
                    $res['quantite'] = $produit->getQuantite();
                }else{
                    $res['id'] = 'no';
                }
            }else{
                $res['id'] = 'no';
            }

            $response = new Response();
            $response->headers->set('content-type','application/json');
            $re = json_encode($res);
            $response->setContent($re);
            return $response;
        }

    }

    #[Route("/edit", name :"edit") ]
    public function edit(Request $request, SessionInterface $session)
    {

        if( $request->isXmlHttpRequest() )
        {// traitement de la requete ajax
            $id = $request->get('prod');// recuperation de id produit
            $panier = $session->get("panier", []);
            $quantite = $request->get('quantite');// recuperation de la quantite commamde
            if(!empty($panier[$id])){//verification existance produit dans le panier

                $produit = $panier[$id]['produit'];
                $produit->setQuantite($quantite);
                $panier[$id]['produit'] = $produit;

                    // On sauvegarde dans la session
                    $session->set("panier", $panier);

                    $res['id'] = 'ok';
                    $res['panier'] = $quantite;

            }else{
                $res['id'] = 'no';
            }

            $response = new Response();
            $response->headers->set('content-type','application/json');
            $re = json_encode($res);
            $response->setContent($re);
            return $response;
        }

    }



    #[Route("/editinPanier/", name :"editin") ]
    public function editPanier(Request $request, ProduitRepository $produitRepository, SessionInterface $session, PromotionRepository $promotionRepository)
    {
        // On récupère le panier actuel
        // $panier = $session->get("panier", []);
        if( $request->isXmlHttpRequest() )
        {// traitement de la requete ajax
            $id = $request->get('prod');// recuperation de id produit
            $quantite = $request->get('quantite');// recuperation de la quantite commamde
            $this->getUser()->getTuteur() === null ? $client = $this->getUser()->getId() : $client = $this->getUser()->getTuteur()->getId();
            $panier = $this->entityManager->getRepository(Panier::class)->findOneBy(['client' => $client, 'produit' => $id]);
            $panier->setQuantite($quantite);
          
            $this->entityManager->persist($panier);
            $this->entityManager->flush();


            $res['id'] = 'ok';
               
            $response = new Response();
            $response->headers->set('content-type','application/json');
            $re = json_encode($res);
            $response->setContent($re);
            return $response;
        }

    }


    #[Route("/colisagePanier/", name :"colisage") ]
    public function colisagePanier(Request $request, ProduitRepository $produitRepository, SessionInterface $session, PromotionRepository $promotionRepository)
    {
        // On récupère le panier actuel
        // $panier = $session->get("panier", []);
        if( $request->isXmlHttpRequest() )
        {// traitement de la requete ajax
            $id = $request->get('prod');// recuperation de id produit
            // $quantite = $request->get('quantite');// recuperation de la quantite commamde
            $this->getUser()->getTuteur() === null ? $client = $this->getUser()->getId() : $client = $this->getUser()->getTuteur()->getId();
            $panier = $this->entityManager->getRepository(Panier::class)->findOneBy(['client' => $client, 'produit' => $id]);
            $panier->bascule();
          
            $this->entityManager->persist($panier);
            $this->entityManager->flush();


            $res['id'] = 'ok';
               
            $response = new Response();
            $response->headers->set('content-type','application/json');
            $re = json_encode($res);
            $response->setContent($re);
            return $response;
        }

    }

    #[Route("/delete/{id}", name :"delete") ]
    public function delete(Produit $Produit, SessionInterface $session)
    {
        // On récupère le panier actuel
        $panier = $session->get("panier", []);
        $id = $Produit->getId();

        if(!empty($panier[$id])){
            unset($panier[$id]);
        }

        // On sauvegarde dans la session
        $session->set("panier", $panier);

        return $this->redirectToRoute("commande_panier_panier");
    }

    #[Route("/deleteIn/{id}", name :"deletein") ]
    public function deletein(Produit $Produit, SessionInterface $session)
    {
        // On récupère le panier actuel
       
        $this->getUser()->getTuteur() === null ? $client = $this->getUser()->getId() : $client = $this->getUser()->getTuteur()->getId();
        $panier = $this->entityManager->getRepository(Panier::class)->findOneBy(['client' => $client, 'produit' => $Produit->getId()]);
        $this->entityManager->remove($panier);
        $this->entityManager->flush();

        return $this->redirectToRoute("commande_panier_panier");
    }

    #[Route("/edit_extranet", name :"edit_extranet") ]
    public function edit_extranet(Request $request, SessionInterface $session)
    {

        if( $request->isXmlHttpRequest() )
        {// traitement de la requete ajax
            $id = $request->get('prod');// recuperation de id produit
            $panier = $session->get("panier", []);
            $quantite = $request->get('quantite');// recuperation de la quantite commamde
            if(!empty($panier[$id])){//verification existance produit dans le panier

                $produit = $panier[$id]['produit'];
                $produit->setQuantite($quantite);
                $panier[$id]['produit'] = $produit;

                    // On sauvegarde dans la session
                    $session->set("panier", $panier);

                    $res['id'] = 'ok';
                    $res['panier'] = $quantite;

            }else{
                $res['id'] = 'no';
            }

            $response = new Response();
            $response->headers->set('content-type','application/json');
            $re = json_encode($res);
            $response->setContent($re);
            return $response;
        }

    }

    #[Route("/delete_extranet/", name :"delete_extranet") ]
    public function delete_extranet(SessionInterface $session, Request $request)
    {
        // On récupère le panier actuel
        $panier = $session->get("panier", []);
        $id = $this->entityManager->getRepository(Produit::class)->find( $id = $request->get('prod'))->getId();

        if(!empty($panier[$id])){
            unset($panier[$id]);
        }

        // On sauvegarde dans la session
        $session->set("panier", $panier);

        $res['id'] = 'ok';
        $response = new Response();
        $response->headers->set('content-type','application/json');
        $re = json_encode($res);
        $response->setContent($re);
        return $response;
    }

    #[Route("/delete", name :"delete_all") ]
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

                  
    #[Route("/deleteIN", name :"delete_allin") ]
    public function deleteAllin(SessionInterface $session)
    {
        $this->getUser()->getTuteur() === null ? $client = $this->getUser()->getId() : $client = $this->getUser()->getTuteur()->getId();
        $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $client]);
        foreach($panier as $pan){
            $this->entityManager->remove($pan);
        }
        $this->entityManager->flush();

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
