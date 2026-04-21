<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Form\CommandeType;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Component\Security\Core\Security;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;


#[Route("{_locale}/Importation") ]
class ImportationController extends AbstractController
{
      public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

    #[Route("/", name :"importation", methods : ["POST"]) ]
    public function importation(Request $request, SessionInterface $session)
    {
        $file = $request->files->get('import');

        if (!$file) {
            return new Response('Aucun fichier envoyé');
            //dd($request);
        }

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet()->toArray();
            $notfound = 0;     
            $panier = [];
            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]);;
           
            if(count($sheet) > 0){// commande par importation
                foreach($sheet as $commande){
                    if (!preg_match('/^[0-9]+$/', $commande[0]) || !preg_match('/^[0-9]+$/', $commande[1])) {
                            $this->addFlash('notice', $Commande[0] . " produit non trouvé ou quantité erronée");
                            continue;
                        }
                    $produit = $this->entityManager->getRepository(Produit::class)->findOneby(['reference' => $commande[0]]);
                    if($produit !== null){
                        foreach($panier as $prod){
                            if($prod->getProduit()->getReference() == $commande[0]  ){
                                $this->addFlash('notice', $commande[0]. "ce produit existe deja dans le paneir");
                                goto panier;
                            }
                        }
                    }else{
                        $this->addFlash('notice', $Commande[0] . " produit non trouvé");
                        continue;
                    }
               
            $quantite = $commande[1];// recuperation de la quantite commamde
            $reduction = 0;
            // if(empty($panier[$id])){//verification existance produit dans le panier
                //$produit = $produitRepository->find($id); // recuperation de id produit dans la db
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


                    // $res['id'] = 'ok';
                    // $res['panier'] = count($panier)+1;
                }
                panier:
                }
            }
            
            $this->entityManager->flush();
             $notfound !== 0 ? $this->addFlash('notice', $notfound . " CIP  produit(s) non trouvé(s) ou quantité(s) erronée(s)") : null;
            $response = $this->redirectToRoute("commande_panier_panier");
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;

        } catch (\Exception $e) {
            return new Response('Erreur : ' . $e->getMessage());
        }
    }
    
    #[Route("/admin", name :"adminimportation", methods : ["POST"]) ]
    public function importationadmin(Request $request, SessionInterface $session)
    {
        $file = $request->files->get('import');

        if (!$file) {
            return new Response('Aucun fichier envoyé');
            //dd($request);
        }

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet()->toArray();
            $session->set('sheet', $sheet);

            $response = $this->redirectToRoute("commande_panier_panier");
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;

        } catch (\Exception $e) {
            return new Response('Erreur : ' . $e->getMessage());
        }
    }

     
    #[Route("/ImportPromo", name :"importpromo") ]
    public function importpromo(SessionInterface $session, Request $request, ProduitRepository $produitRepository)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

           
            $commande = new Commande();

            $form = $this->createForm(CommandeType::class, $commande);
            $form->add('import', FileType::class, [
                        'label' => 'Importer un fichier Excel',
                        'mapped' => false, //  n'existe pas dans l'entité
                        'required' => true,
                    ]);
            $form->handleRequest($request);

             if ($form->isSubmitted()) {
                 // if ($request->isMethod('POST')) {
                 
                $session->set('client', $commande->getUser()->getId());
                $session->set('extranet', $commande->getUser()->getId());
                $session->set('prelevement', $commande->getUser()->isPrelevement());
                $file = $request->files->get('commande')['import'];
               
                if (!$file) {
                    return new Response('Aucun fichier envoyé');
                   // dd($request);
                }

                try {
                    $spreadsheet = IOFactory::load($file->getPathname());
                    $sheet = $spreadsheet->getActiveSheet()->toArray();
                    $res = [];
                    $panier = [];
                    $compt = 0;
                    foreach($sheet as $commande){
                        $id = $commande[0];
                        $quantite = $commande[1];
                        if (!preg_match('/^[0-9]+$/', $id) || !preg_match('/^[0-9]+$/', $quantite)) {
                            $compt +=1;
                            continue;
                        }
                        $reduction = null;
                        $produit = $produitRepository->findOneby(['reference' => $id]); // recuperation de id produit dans la db
                       
                        if($produit !== null){
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
                            //$session->set("panier", $panier);

                            // $res['id'] = 'ok';
                            // $res['ref'] = $produit->getReference();
                            // $res['designation'] = $produit->getDesigantion();
                            // $res['fabriquant'] = $produit->getFabriquant();
                            // $res['quantite'] = $produit->getQuantite();
                        }
                       }else{
                            $compt +=1;
                        }
                    }
                    $session->set('sheet', $panier);
                    $session->set('compte', $compt);
                
                
                    $response = $this->redirectToRoute('commande_panier_choix_paiement_extranet_promo', ['commande' => 0]);
                    $response->setSharedMaxAge(0);
                    $response->headers->addCacheControlDirective('no-cache', true);
                    $response->headers->addCacheControlDirective('no-store', true);
                    $response->headers->addCacheControlDirective('must-revalidate', true);
                    $response->setCache([
                        'max_age' => 0,
                        'private' => true,
                    ]);
                    return $response;

                } catch (\Exception $e) {
                    return new Response('Erreur : ' . $e->getMessage());
                }
            }


            $response = $this->render('commande/admin/importpromo.html.twig', [
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
    
    
    #[Route("/ImportSansPromo", name :"importsanspromo") ]
    public function importsanspromo(SessionInterface $session, Request $request, ProduitRepository $produitRepository)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

           
            $commande = new Commande();

            $form = $this->createForm(CommandeType::class, $commande);
            $form->add('import', FileType::class, [
                        'label' => 'Importer un fichier Excel',
                        'mapped' => false, //  n'existe pas dans l'entité
                        'required' => true,
                    ]);
            $form->handleRequest($request);

             if ($form->isSubmitted()) {
                 // if ($request->isMethod('POST')) {
                 
                $session->set('client', $commande->getUser()->getId());
                $session->set('extranet', $commande->getUser()->getId());
                $session->set('prelevement', $commande->getUser()->isPrelevement());
                $file = $request->files->get('commande')['import'];
               
                if (!$file) {
                    return new Response('Aucun fichier envoyé');
                   // dd($request);
                }

                try {
                    $spreadsheet = IOFactory::load($file->getPathname());
                    $sheet = $spreadsheet->getActiveSheet()->toArray();
                    $res = [];
                    $panier = [];
                    $compt = 0;
                    foreach($sheet as $commande){
                        $id = $commande[0];
                        $quantite = $commande[1];
                        if (!preg_match('/^[0-9]+$/', $id) || !preg_match('/^[0-9]+$/', $quantite)) {
                            $compt +=1;
                            continue;
                        }
                        $reduction = null;
                        $produit = $produitRepository->findOneby(['reference' => $id]); // recuperation de id produit dans la db
                       
                        if($produit !== null){
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
                            //$session->set("panier", $panier);

                            // $res['id'] = 'ok';
                            // $res['ref'] = $produit->getReference();
                            // $res['designation'] = $produit->getDesigantion();
                            // $res['fabriquant'] = $produit->getFabriquant();
                            // $res['quantite'] = $produit->getQuantite();
                        }
                       }else{
                            $compt +=1;
                        }
                    }
                    $session->set('sheet', $panier);
                    $session->set('compte', $compt);
                
                
                    $response = $this->redirectToRoute('commande_panier_choix_paiement_extranet', ['commande' => 0]);
                    $response->setSharedMaxAge(0);
                    $response->headers->addCacheControlDirective('no-cache', true);
                    $response->headers->addCacheControlDirective('no-store', true);
                    $response->headers->addCacheControlDirective('must-revalidate', true);
                    $response->setCache([
                        'max_age' => 0,
                        'private' => true,
                    ]);
                    return $response;

                } catch (\Exception $e) {
                    return new Response('Erreur : ' . $e->getMessage());
                }
            }


            $response = $this->render('commande/admin/importsanspromo.html.twig', [
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
}