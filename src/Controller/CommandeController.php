<?php

namespace App\Controller;

use App\Complement\Promotion;
use App\Entity\Commande;
use App\Entity\Produit;
use App\Entity\Panier;
use App\Entity\CommandeProduit;
use App\Entity\Credit;
use App\Entity\Ecriture;
use App\Entity\Paiement;
use App\Entity\User;
use App\Entity\Client;
use App\Entity\Releve;
use App\Entity\Versement;
use App\Form\CommandeType;
use App\Form\PaiementFormType;
use App\Form\VersementType;
use App\Repository\AvoirRepository;
use App\Repository\ReleveRepository;
use App\Repository\CommandeProduitRepository;
use App\Repository\LivrerProduitRepository;
use App\Repository\CommandeRepository;
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
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Service\PdfService;


#[Route("/{_locale}/Commande_Panier", name :"commande_panier_") ]
class CommandeController extends AbstractController
{
      public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

    #[Route("/", name :"panier") ]
    public function index(Request $request, SessionInterface $session, ProduitRepository $produitRepository)
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {
            $dataPanier = [];
            $total = 0;
            $notfound = 0;
             $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]);  
             foreach($panier as $commande){
                $commande->getProduit()->setQuantite($commande->getQuantite());
                $dataPanier[] = [
                    "produit" => $commande->getProduit(),
                    "promotion" => $commande->getReduction(),
                    "colisage" => $commande->isColisage(),
                ];
            }

            // $sheet = $session->get('sheet',[]);
            // if(count($sheet) > 0){// commande par importation
            //     foreach($sheet as $commande){
            //         if (!preg_match('/^[0-9]+$/', $commande[0]) || !preg_match('/^[0-9]+$/', $commande[1])) {
            //                 $notfound +=1;
            //                 continue;
            //             }
            //         $produit = $this->entityManager->getRepository(Produit::class)->findOneby(['reference' => $commande[0]]);
            //        if( $produit != null){ 
            //         $produit->setQuantite($commande[1]);
            //         $dataPanier[] = [
            //             "produit" => $produit,
            //             "promotion" => 0,
            //         ];
            //        }else{
            //         $notfound += 1;
            //        }
            //     }
            //     $session->set('sheetchoix',$sheet);//creation d'une session pour le choix paiement
            //     $session->remove('sheet');// suppression session chargement
            //     $notfound !== 0 ? $this->addFlash('notice', $notfound . " CIP  produit(s) non trouvé(s) ou quantité(s) erronée(s)") : null;
            // }

            $response = $this->render('commande/index.html.twig', [
                // 'produits' => $produitRepository->findAll(),
                'panier' => $dataPanier,
                // 'sheet' => $sheet,
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

    #[Route("/Extranet", name :"extranet") ]
    public function extranet(SessionInterface $session, StockRepository $produitRepository, Request $request)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

            $panier = $session->get("panier", []);
            $dataPanier = [];
            $total = 0;
            foreach ($panier as $commande) {
//                $product = $produitRepository->find($id);
                $dataPanier[] = [
                    "produit" => $commande['produit']
                ];
//                $total += $product->getPrix() * $quantite;
            }
            $commande = new Commande();

            $form = $this->createForm(CommandeType::class, $commande);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                $session->set('extranet', $commande->getUser()->getId());
                $session->set('prelevement', $commande->getUser()->isPrelevement());
                $this->redirectToRoute('commande_panier_choix_paiement_extranet', ['commande' => 0]);
            }


            $response = $this->render('commande/admin/index.html.twig', [
                'produits' => $produitRepository->stock(),
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


    #[Route("/client_extranet/", name :"client_extranet") ]
    public function add(Request $request, SessionInterface $session)
    {
        if ($request->isXmlHttpRequest()) {// traitement de la requete ajax
            $session->set('client', $request->get('client'));// recuperation de id produit


            $res['id'] = 'ok';


            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
            return $response;
        }

    }

    #[Route("/valider", name :"valider") ]
    public function valider(Request $request, SessionInterface $session, ProduitRepository $produitRepository, CommandeProduitRepository $repository, Promotion $promo)
    {
        if ($this->security->isGranted('ROLE_CLIENT') && $this->isCsrfTokenValid('delete', $request->request->get('_token'))) {
             $sheet = $session->get('sheetvalider',[]);
             $panier = [];
              $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]);;
           
            if(count($sheet) > 0){// commande par importation
                foreach($sheet as $commandeimp){
                    $produit = $this->entityManager->getRepository(Produit::class)->findOneby(['reference' => $commandeimp[0]]);
                   if( $produit != null){ 
                    $produit->setQuantite($commandeimp[1]);
                     $pan = new Panier();
                     $pan->setProduit($produit);
                     $pan->setQuantite($commandeimp[1]);
                     $panier[] = $pan;
                    // $dataPanier[] = [
                    //     "produit" => $produit,
                    //     "promotion" => 0,
                    // ];
                   }else{
                    continue;
                   }
                }
                //$panier = $dataPanier;
                $session->remove('sheetvalider');

            }
           //$dataPanier = [];
//            $em = $this->getDoctrine()->getManager();
            $commande = new Commande();
            $commande->setNumerofacture(count($this->getUser()->getCommandes()) + 1);
            
            if (count($panier) >= 1) {

                
            if($this->getUser()->getTuteur() === null)
                $commande->setUser($this->getUser());
            else{
                $commande->setUser($this->getUser()->getTuteur());
                $commande->setPharmaemploye($this->getUser());
            }

                if ($session->get("credit")) {
                    $commande->setCredit(true);
                    $session->remove('credit');
                }
                $montant = 0;
                $reduction = 0;
                $tva = 0;
                foreach ($panier as $product) {
                    $reductionproduit = 0;
                    $ug = 0;
                    $produit = $produitRepository->find($product->getProduit()->getId());
                    if($product->isColisage()){
                        $product->setQuantite($product->getQuantite() * $produit->getColisage());
                    }
                    if (!empty($produit->getPromotion())) {//traitement de la promotion avec reduction
                        if (!empty($produit->getPromotion()->getReduction())) {
                            $reductionproduit = $product->getQuantite() * $produit->getPrix() * $produit->getPromotion()->getReduction() / 100;

                            $reduction = $reduction + $reductionproduit;
                        }
                        if ($produit->getPromotion()->getPremier() !== null) {
                            $ug = $promo->ug($produit, $product->getQuantite());
                        }
                    }

                    $montant = $montant + $product->getQuantite() * $produit->getPrix();

                    $commandeproduit = new CommandeProduit($produit, $commande, $produit->getPrix(), $produit->getPrixpublic(), $product->getQuantite());
                    $commandeproduit->setUg($ug);
                    if($product->getQuantite() > $produit->getStock()){
                        $commandeproduit->setExtranet(true);
                        $commande->setExtranet(true);

                    }
                    if($produit->getTva()){
                        $tvaproduit = (($product->getQuantite() * $produit->getPrix()) - $reductionproduit) * 0.1925;
                         $tva = $tva + $tvaproduit;
                        $commandeproduit->setTva($tvaproduit);  
                    }
                    else $commandeproduit->setTva(0);
                    $commandeproduit->setPght($produit->getPght());
                    if (!empty($produit->getPromotion())) {

                        $commandeproduit->setPromotion($produit->getPromotion());
                    }
                    $this->entityManager->persist($commandeproduit);
                }
                 if($this->getUser()->isPrelevement()){
                    $acompte = $montant * 0.02;
                    $montant = $montant + $acompte + $tva - $reduction;
                    // $montant = $montant + $tva - $reduction;
                    $commande->setAcompte(round($acompte,2));
                }else{
                    $montant = $montant + $tva - $reduction; 
                }
                $commande->setMontant(round($montant,2));
                $commande->setTva(round($tva,2));
                $commande->setReduction(round($reduction,2));
                $this->entityManager->persist($commande);
                foreach($panier as $pan){// suppresssion des panier fictif
                $this->entityManager->remove($pan);
                }
                $this->entityManager->flush();
                 
                $response = $this->redirectToRoute('commande_panier_imprimer', [
                    'commande' => $commande->getId(),
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
    
    
    #[Route("/LienDayCommandeCaisse", name :"day_commande_lien_caisse") ]
    public function liendaybrouyardaisse(Request $request)
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {
            $date1 = $request->get('date1');
            $client = $request->get('client');
            $lien = $this->generateUrl('commande_panier_day_commande_caisse', ['client' => $client,'jour' => $date1]);
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

    #[Route("/DayCommandeCaisse/{client}/{jour}", name :"day_commande_caisse") ]
    public function daycommandecaisse($client, $jour, CommandeRepository $repository)
    {
         if ($this->security->isGranted('ROLE_CAISSIER')) {
            $clt = $this->entityManager->getRepository(Client::class)->find($client);

            $response = $this->render('commande/admin/resultatrecherche.html.twig', [
                'commandes' => $repository->clientjournalier($client, $jour),
                'client' => $clt,
                'jour' => $jour,
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

    
    #[Route("/LienDaysCommandeCaisse", name :"days_commande_lien_caisse") ]
    public function liendaysbrouyardcaisse(Request $request)
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {
            $date1 = $request->get('date1');
            $date2 = $request->get('date2');
            $client = $request->get('client');
            $lien = $this->generateUrl('commande_panier_days_commande_caisse', ['client' => $client, 'date1' => $date1, 'date2' => $date2]);
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


    #[Route("/DaysCommandeCaisse/{client}/{date1}/{date2}", name :"days_commande_caisse") ]
    public function daysbrouyardcaisse(Request $request, $client, $date1, $date2, CommandeRepository $repository)
    {
         if ($this->security->isGranted('ROLE_CAISSIER')) {
            $clt = $this->entityManager->getRepository(Client::class)->find($client);

            $response = $this->render('commande/admin/resultatrechercheplage.html.twig', [
                'commandes' => $repository->plage($client, $date1, $date2),
                'client' => $clt,
                'date1' => $date1,
                'date2' => $date2,
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

    
    #[Route("/Modificationvalider/{commande}", name :"modification_valider") ]
    public function modificationvalider(Commande $commande, Request $request, SessionInterface $session, ProduitRepository $produitRepository, CommandeProduitRepository $repository, Promotion $promo)
    {
        if ($this->security->isGranted('ROLE_STOCK') && $this->isCsrfTokenValid('delete'.$commande->getId(), $request->request->get('_token'))) {
             $panier = $session->get('confirmation',[]);
           
            $commandeproduits = $repository->findBy(['commande' => $commande]);
            
            if (count($commandeproduits) >= 1) {

                
          
                $montant = 0;
                $reduction = 0;
                $tva = 0;
                foreach ($commandeproduits as $product) {
                    $product->setExtranet(false);
                    $ug = 0;
                    $produit = $product->getProduit();

                    if( $product->getQuantite() == $panier[$produit->getid()]){
                         $montant = $montant + $panier[$produit->getid()] * $produit->getPrix();
                        continue;
                    }else  if( $product->getQuantite() >  $panier[$produit->getid()]){
                    $reductionproduit = 0;

                    
                    if (!empty($produit->getPromotion())) {//traitement de la promotion avec reduction
                        if (!empty($produit->getPromotion()->getReduction())) {
                            $reductionproduit = $panier[$produit->getid()] * $produit->getPrix() * $produit->getPromotion()->getReduction() / 100;

                            $reduction = $reduction + $reductionproduit;
                        }
                        if ($produit->getPromotion()->getPremier() !== null) {
                            $ug = $promo->ug($produit, $panier[$produit->getid()]);
                            $product->setUg($ug);
                        }
                    }

                    $montant = $montant + $panier[$produit->getid()] * $produit->getPrix();
                    
                    
                    $product->setQuantitecommande($product->getQuantite());
                    $product->setQuantite($panier[$produit->getid()]);

                        if($produit->getTva()){
                        $tvaproduit = (($panier[$produit->getid()] * $produit->getPrix()) - $reductionproduit) * 0.1925;
                         $tva = $tva + $tvaproduit;
                        $product->setTva($tvaproduit);  
                    }
                    $this->entityManager->persist($product);
                }
                }
                 if($commande->getUser()->isPrelevement()){
                    $acompte = $montant * 0.02;
                    $montant = $montant + $acompte + $tva - $reduction;
                    // $montant = $montant + $tva - $reduction;
                    $commande->setAcompte(round($acompte,2));
                }else{
                    $montant = $montant + $tva - $reduction; 
                }
                $commande->setMontant(round($montant,2));
                $commande->setTva(round($tva,2));
                $commande->setReduction(round($reduction,2));
                $commande->setExtranet(false);
                $this->entityManager->persist($commande);
                
                $this->entityManager->flush();
                 
                $response = $this->redirectToRoute('commande_panier_imprimer_extranet', [
                    'commande' => $commande->getId(),
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


    #[Route("/valider_extranet_promo", name :"valider_extranet_promo") ]
    public function validerextranetpromo(Request $request, SessionInterface $session, ProduitRepository $produitRepository, CommandeProduitRepository $repository, Promotion $promo)
    {
        if ($this->security->isGranted('ROLE_FINANCE')  && $this->isCsrfTokenValid('delete', $request->request->get('_token'))) {

             $panier= [];
            if($session->get("sheetvalider") !== null ){ 
                $panier = $session->get("sheetvalider", []);
                $session->remove("sheetvalider");
            }
            else $panier = $session->get("panier", []);
            $dataPanier = [];
            $em = $this->entityManager;
            $commande = new Commande();
            $commande->setAdmin($this->getUser());

            if (count($panier) >= 1) {
                $client = $em->getRepository(User::class)->find($session->get('client'));

            $commande->setNumerofacture(count($client->getCommandes()) + 1);
                $session->remove('client');
                $commande->setUser($client);
                if ($session->get("credit")) {
                    $commande->setCredit(true);
                    $session->remove('credit');
                }
                $montant = 0;
                $reduction = 0;
                $tva = 0;
                foreach ($panier as $product) {
                    $reductionproduit = 0;
                    $ug = 0;
                    $produit = $produitRepository->find($product['produit']->getId());
                    if (!empty($produit->getPromotion())) {//traitement de la promotion avec reduction
                        if (!empty($produit->getPromotion()->getReduction())) {
                            $reductionproduit = $product['produit']->getQuantite() * $produit->getPrix() * $produit->getPromotion()->getReduction() / 100;

                            $reduction = $reduction + $reductionproduit;
                        }
                        if ($produit->getPromotion()->getPremier() !== null) {
                            $ug = $promo->ug($produit, $product['produit']->getQuantite());
                        }
                    }

                    $montant = $montant + $product['produit']->getQuantite() * $produit->getPrix();

                    $commandeproduit = new CommandeProduit($produit, $commande, $produit->getPrix(), $produit->getPrixpublic(), $product['produit']->getQuantite());
                    $commandeproduit->setUg($ug);
                    if($produit->getTva()){
                        $tvaproduit = (($product['produit']->getQuantite() * $produit->getPrix()) - $reductionproduit) * 0.1925;
                         $tva = $tva + $tvaproduit;
                        $commandeproduit->setTva($tvaproduit);  
                    }
                    else $commandeproduit->setTva(0);
                    $commandeproduit->setPght($produit->getPght());
                    if (!empty($produit->getPromotion())) {

                        $commandeproduit->setPromotion($produit->getPromotion());
                    }
                    $this->entityManager->persist($commandeproduit);
                }
                if($client->isPrelevement()){
                    $acompte = $montant * 0.02;
                    $montant = $montant + $acompte + $tva - $reduction;
                    // $montant = $montant + $tva - $reduction;
                    $commande->setAcompte(round($acompte,2));
                }else{
                    $montant = $montant + $tva - $reduction; 
                }
                $session->remove('prelevement');
                $commande->setMontant(round($montant,2));
                $commande->setTva(round($tva,2));
                $commande->setReduction(round($reduction,2));
                $em->persist($commande);
                $em->flush();
                $session->remove("panier");
                $response = $this->redirectToRoute('commande_panier_imprimer_extranet_promo', [
                    'commande' => $commande->getId(),
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

     #[Route("/valider_extranet", name :"valider_extranet") ]
    public function validerextranet(Request $request, SessionInterface $session, ProduitRepository $produitRepository, CommandeProduitRepository $repository)
    {
        if ($this->security->isGranted('ROLE_FINANCE') && $this->isCsrfTokenValid('delete', $request->request->get('_token'))) {
             $panier= [];
            if($session->get("sheetvalider") !== null ){ 
                $panier = $session->get("sheetvalider", []);
                $session->remove("sheetvalider");
            }
            else $panier = $session->get("panier", []);
            $dataPanier = [];
            $em = $this->entityManager;
            $commande = new Commande();
            $commande->setAdmin($this->getUser());

            if (count($panier) >= 1) {
                $client = $em->getRepository(User::class)->find($session->get('client'));

            $commande->setNumerofacture(count($client->getCommandes()) + 1);
                $session->remove('client');
                $commande->setUser($client);
                if ($session->get("credit")) {
                    $commande->setCredit(true);
                    $session->remove('credit');
                }
                $montant = 0;
                $reduction = 0;
                $tva = 0;
                foreach ($panier as $product) {
                    $reductionproduit = 0;
                    $produit = $produitRepository->find($product['produit']->getId());
                    // if (!empty($produit->getPromotion())) {//traitement de la promotion avec reduction
                    //     if (!empty($produit->getPromotion()->getReduction())) {
                    //         $reductionproduit = $product['produit']->getQuantite() * $produit->getPrix() * $produit->getPromotion()->getReduction() / 100;

                    //         $reduction = $reduction + $reductionproduit;
                    //     }
                    // }

                    $montant = $montant + $product['produit']->getQuantite() * $produit->getPrix();

                    $commandeproduit = new CommandeProduit($produit, $commande, $produit->getPrix(), $produit->getPrixpublic(), $product['produit']->getQuantite());
                    if($produit->getTva()){
                        $tvaproduit = (($product['produit']->getQuantite() * $produit->getPrix()) - $reductionproduit) * 0.1925;
                         $tva = $tva + $tvaproduit;
                        $commandeproduit->setTva($tvaproduit);  
                    }
                    else $commandeproduit->setTva(0);
                    $commandeproduit->setPght($produit->getPght());
                    // if (!empty($produit->getPromotion())) {

                    //     $commandeproduit->setPromotion($produit->getPromotion());
                    // }
                    $this->entityManager->persist($commandeproduit);
                }
                if($client->isPrelevement()){
                    $acompte = $montant * 0.02;
                    $montant = $montant + $acompte + $tva - $reduction;
                    // $montant = $montant + $tva - $reduction;
                    $commande->setAcompte(round($acompte,2));
                }else{
                    $montant = $montant + $tva - $reduction; 
                }
                $session->remove('prelevement');
                $commande->setMontant(round($montant,2));
                $commande->setTva(round($tva,2));
                $commande->setReduction(round($reduction,2));
                $em->persist($commande);
                $em->flush();
                $session->remove("panier");
                $response = $this->redirectToRoute('commande_panier_imprimer_extranet', [
                    'commande' => $commande->getId(),
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


    #[Route("/VosCommandes/", name :"suivi") ]
    public function voscommande(SessionInterface $session, CommandeRepository $repository)
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {
           $panier =[];
            if($this->getUser()->getTuteur() === null){
                 $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]);
            $commandes = $repository->findBy(['user' => $this->getUser()->getId(), 'suivi' => false]);
            }else{
                 $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]);
            $commandes = $repository->findBy(['user' => $this->getUser()->getTuteur()->getId(), 'suivi' => false]);
             }
            $response = $this->render('commande/suivi.html.twig', [
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
        } else if ($this->security->isGranted('ROLE_CAISSIER')) {
//            $panier = $session->get("panier", []);

            $response = $this->render('commande/admin/suivi.html.twig', [
                'commandes' => $repository->findBy(['suivi' => false]),
//                'panier' => $panier,
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }

    

    #[Route("/Commandes_lient/{client}", name :"commande_client") ]
    public function commandeclient(Client $client, CommandeRepository $repository)
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {

            $response = $this->render('commande/admin/commandesclient.html.twig', [
                'commandes' => $repository->journalier($client->getId()),
                'client' => $client,
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

    

    #[Route("/Commandes_Recherche/{client}", name :"commande_recherche") ]
    public function recherche(Client $client, CommandeRepository $repository)
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {

            $response = $this->render('commande/admin/recherche.html.twig', [
                'commandes' => $repository->journalier($client->getId()),
                'client' => $client,
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }

    #[Route("/VosCommandes_extranet/", name :"suivi_extranet") ]
    public function voscommandeextranet(SessionInterface $session, CommandeRepository $repository)
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {
            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]);;
             $this->getUser()->getTuteur() === null ? 
            $commandes = $repository->findBy(['user' => $this->getUser()->getId()/*, 'extranet' => true*/]) :
            $commandes = $repository->findBy(['user' => $this->getUser()->getTuteur()->getId()/*, 'extranet' => true*/]);
           
            $response = $this->render('commande/extranet.html.twig', [
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
        } else if ($this->security->isGranted('ROLE_FINANCE')) {
//            $panier = $session->get("panier", []);

            $response = $this->render('commande/admin/suivi.html.twig', [
                'commandes' => $repository->findBy(['suivi' => false]),
//                'panier' => $panier,
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }

    #[Route("/Historique/", name :"history") ]
    public function history(SessionInterface $session, CommandeRepository $repository)
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {
//            $panier = $session->get("panier", []);

            $response = $this->render('commande/admin/history.html.twig', [
                'commandes' => $repository->findBy(['suivi' => true, 'payer' => true]),
//                'panier' => $panier,
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
            $this->getUser()->getTuteur() === null ? 
            $commandes = $repository->findBy(['user' => $this->getUser()->getId(), 'suivi' => true, 'payer' =>true]) :
            $commandes = $repository->findBy(['user' => $this->getUser()->getTuteur()->getId(), 'suivi' => true, 'payer' => true]);
            
            $response = $this->render('commande/history.html.twig', [
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }

    #[Route("/Credit/", name :"credit") ]
    public function credit(SessionInterface $session, CommandeRepository $repository)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
//            $panier = $session->get("panier", []);

            $response = $this->render('commande/admin/credit.html.twig', [
                'commandes' => $repository->findBy(['paiement' => null, 'credit' => true, 'suivi' => true, 'payer' => false]),
//                'panier' => $panier,
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
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]);;
           
            $response = $this->render('officine/credit.html.twig', [
                'commandes' => $repository->findBy(['user' => $this->getUser()->getId(), 'paiement' => null, 'credit' => true, 'suivi' => true, 'payer' => false]),
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

    #[Route("/ValidationCredit/", name :"validation_credit") ]
    public function validationcredit(SessionInterface $session, CommandeRepository $repository)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
//            $panier = $session->get("panier", []);

            $response = $this->render('commande/admin/validation.html.twig', [
                'commandes' => $repository->findBy(['suivi' => false, 'credit' => true]),
//                'panier' => $panier,
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
            $panier = $session->get("panier", []);

            $response = $this->render('commande/credit.html.twig', [
                'commandes' => $repository->findBy(['user' => $this->getUser()->getId(), 'suivi' => true]),
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

    #[Route("/Historique_admin/", name :"history_admin") ]
    public function histo_admin(SessionInterface $session, CommandeRepository $repository)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            //$panier = $session->get("panier", []);

            $response = $this->render('commande/history_admin.html.twig', [
                'commandes' => $repository->findBy(['user' => $this->getUser()->getId()]),
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }

    

    #[Route("/Palmares_Credit/{client}", name :"palmares_credit") ]
    public function creditclient(Client $client, SessionInterface $session, CommandeRepository $repository)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            
            $commandes = $repository->findBy(['user' => $client->getId(), 'paiement' => null, 'credit' => true, 'suivi' => true, 'payer' => false]);
            $montant = 0;
            foreach($commandes as $commande){
                $montant += $commande->getMontant();
            }
            $response = $this->render('vente/credit.html.twig', [
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

    #[Route("/Palmares_Paye/{client}", name :"palmares_payer") ]
    public function payeclient(Client $client, SessionInterface $session, CommandeRepository $repository)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            
             $commandes = $repository->findBy(['user' => $client->getId(), 'suivi' => true, 'payer' => true]);
            $montant = 0;
            foreach($commandes as $commande){
                $montant += $commande->getMontant();
            }
            $response = $this->render('vente/payer.html.twig', [
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

    #[Route("/Paiement_commande/{commande}", name :"paiement_client") ]
    public function paiementClient(SessionInterface $session, CommandeProduitRepository $repository, Commande $commande, PaiementRepository $paiementRepository)
    {
        if ($this->security->isGranted('ROLE_CLIENT') && $commande->getUser()->getPharmacie() == $this->getUser()->getPharmacie()) {
            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]);;

            $response = $this->render('commande/payment.html.twig', [
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                'commande' => $commande,
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }

    #[Route("/ConfirmationPaiement/", name :"confirm_paiement_client") ]
    public function confirm(SessionInterface $session)
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {
             $dataPanier = [];
            $total = 0;
             $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]);  
             foreach($panier as $commande){
                $commande->getProduit()->setQuantite($commande->getQuantite());
                $dataPanier[] = [
                    "produit" => $commande->getProduit(),
                    "promotion" => $commande->getReduction(),
                    "colisage" => $commande->isColisage(),
                ];
            }
            $sheet = $session->get('sheetconfirm',[]);
            if(count($sheet) > 0){// commande par importation
                foreach($sheet as $commande){
                    $produit = $this->entityManager->getRepository(Produit::class)->findOneby(['reference' => $commande[0]]);
                   if( $produit != null){ 
                    $produit->setQuantite($commande[1]);
                    $dataPanier[] = [
                        "produit" => $produit,
                        "promotion" => 0,
                    ];
                   }else{
                    continue;
                   }
                }
                $session->set('sheetvalider', $sheet);
                $session->remove('sheetconfirm');

            }
             if(count($dataPanier) == 0){
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
            }
            $response = $this->render('commande/confirm.html.twig', [
//                'produits' => $produitRepository->findAll(),
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

    #[Route("/ConfirmationPaiement_extranet_promo/", name :"confirm_paiement_extranet_promo") ]
    public function confirmextranetpromo(SessionInterface $session)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

            $panier= [];
            if($session->get("sheetchoix") !== null ){ 
                $panier = $session->get("sheetchoix", []);
                $session->set("sheetvalider", $panier);
                $session->remove("sheetchoix");
            }
            else $panier = $session->get("panier", []);
            $dataPanier = [];
            $total = 0;
             $compte = $session->get('compte');
             if($compte > 0){
                $this->addFlash('notice', $compte. " CIP produits non trouvé(s) ou quantité(s) erronée(s)");
                $session->remove('compte');
            }

            foreach ($panier as $commande) {
//                $product = $produitRepository->find($id);
                $dataPanier[] = [
                    "produit" => $commande['produit'],
                    "promotion" => $commande['promotion']
                ];
//                $total += $product->getPrix() * $quantite;
            }

            $response = $this->render('commande/admin/confirmpromo.html.twig', [
//                'produits' => $produitRepository->findAll(),
                'panier' => $dataPanier,
                'prelevement' => $session->get('prelevement'),
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

     #[Route("/ConfirmationPaiement_extranet/", name :"confirm_paiement_extranet") ]
    public function confirmextranet(SessionInterface $session)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

            $panier= [];
            if($session->get("sheetchoix") !== null ){ 
                $panier = $session->get("sheetchoix", []);
                $session->set("sheetvalider", $panier);
                $session->remove("sheetchoix");
            }
            else $panier = $session->get("panier", []);
            $dataPanier = [];
            $total = 0;
             $compte = $session->get('compte');
             if($compte > 0){
                $this->addFlash('notice', $compte. " CIP produits non trouvé(s) ou quantité(s) erronée(s)");$session->remove('compte');
            }

            foreach ($panier as $commande) {
//                $product = $produitRepository->find($id);
                $dataPanier[] = [
                    "produit" => $commande['produit'],
                    "promotion" => $commande['promotion']
                ];
//                $total += $product->getPrix() * $quantite;
            }

            $response = $this->render('commande/admin/confirm.html.twig', [
//                'produits' => $produitRepository->findAll(),
                'panier' => $dataPanier,
                'prelevement' => $session->get('prelevement'),
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

    #[Route("/ConfirmationCredit/", name :"confirm_credit_client") ]
    public function confirmcredit(SessionInterface $session)
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {
 $dataPanier = [];
            $total = 0;
            $sheet = $session->get('sheetconfirm',[]);
            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]);
           
              foreach($panier as $commande){
                $commande->getProduit()->setQuantite($commande->getQuantite());
                $dataPanier[] = [
                    "produit" => $commande->getProduit(),
                    "promotion" => $commande->getReduction(),
                    "colisage" => $commande->isColisage(),
                ];
            }
            if(count($sheet) > 0){// commande par importation
                foreach($sheet as $commande){
                    $produit = $this->entityManager->getRepository(Produit::class)->findOneby(['reference' => $commande[0]]);
                   if( $produit != null){ 
                    $produit->setQuantite($commande[1]);
                    $dataPanier[] = [
                        "produit" => $produit,
                        "promotion" => 0,
                    ];
                   }else{
                    continue;
                   }
                }
                $session->set('sheetvalider', $sheet);
                $session->remove('sheetconfirm');

            }
            if(count($dataPanier) == 0){
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
            }
             $session->set("credit", 'credit');
            $response = $this->render('commande/confirm.html.twig', [
//                'produits' => $produitRepository->findAll(),
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

    #[Route("/ConfirmationCredit_extranet_promo/", name :"confirm_credit_client_extranet_promo") ]
    public function confirmcreditextranetpromo(SessionInterface $session)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

           $panier= [];
             if($session->get("sheetchoix") !== null ){ 
                $panier = $session->get("sheetchoix", []);
                $session->remove("sheetchoix");
                $session->set("sheetvalider", $panier);
            }
            else $panier = $session->get("panier", []);
            $session->set("credit", 'credit');
            $compte = $session->get('compte');
             if($compte > 0){
                $this->addFlash('notice', $compte. " CIP produits non trouvé(s) ou quantité(s) erronée(s)");$session->remove('compte');
            }
            $dataPanier = [];
            $total = 0;

            foreach ($panier as $commande) {
//                $product = $produitRepository->find($id);
                $dataPanier[] = [
                    "produit" => $commande['produit'],
                    "promotion" => $commande['promotion']
                ];
//                $total += $product->getPrix() * $quantite;
            }

            $response = $this->render('commande/admin/confirmpromo.html.twig', [
//                'produits' => $produitRepository->findAll(),
                'panier' => $dataPanier,
                'prelevement' => $session->get('prelevement'),
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

      #[Route("/ConfirmationCredit_extranet/", name :"confirm_credit_client_extranet") ]
    public function confirmcreditextranet(SessionInterface $session)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

           $panier= [];
             if($session->get("sheetchoix") !== null ){ 
                $panier = $session->get("sheetchoix", []);
                $session->remove("sheetchoix");
                $session->set("sheetvalider", $panier);
            }
            else $panier = $session->get("panier", []);
            $session->set("credit", 'credit');
            $compte = $session->get('compte');
            if($compte > 0){
                $this->addFlash('notice', $compte. " CIP produits non trouvé(s) ou quantité(s) erronée(s)");$session->remove('compte');
            }
            $dataPanier = [];
            $total = 0;

            foreach ($panier as $commande) {
//                $product = $produitRepository->find($id);
                $dataPanier[] = [
                    "produit" => $commande['produit'],
                    "promotion" => $commande['promotion']
                ];
//                $total += $product->getPrix() * $quantite;
            }

            $response = $this->render('commande/admin/confirm.html.twig', [
//                'produits' => $produitRepository->findAll(),
                'panier' => $dataPanier,
                'prelevement' => $session->get('prelevement'),
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

    #[Route("/Choix_Paiement/{commande}", name :"choix_paiement") ]
    public function paiementChoix(SessionInterface $session, CommandeProduitRepository $repository, $commande, PaiementRepository $paiementRepository)
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {
         $dataPanier = [];
          $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]);  
             foreach($panier as $commande){
                $commande->getProduit()->setQuantite($commande->getQuantite());
                $dataPanier[] = [
                    "produit" => $commande->getProduit(),
                    "promotion" => $commande->getReduction(),
                    "colisage" => $commande->isColisage(),
                ];
            }
             $sheet = $session->get('sheetchoix',[]);
            if(count($sheet) > 0){// commande par importation
                foreach($sheet as $commandeimp){
                    $produit = $this->entityManager->getRepository(Produit::class)->findOneby(['reference' => $commandeimp[0]]);
                   if( $produit != null){ 
                    $produit->setQuantite($commandeimp[1]);
                    $dataPanier[] = [
                        "produit" => $produit,
                        "promotion" => 0,
                    ];
                   }else{
                    continue;
                   }
                }
                $panier = $dataPanier;
                $session->set('sheetconfirm', $sheet);
                $session->remove('sheetchoix');

            }
            $session->remove('credit');
             if(count($dataPanier) == 0 && count($panier) == 0){
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
            }


            $response = $this->render('commande/traitement.html.twig', [
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                'commande' => $commande,
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }

    #[Route("/Choix_Paiement_extranet_promo/{commande}", name :"choix_paiement_extranet_promo") ]
    public function paiementChoixextranetpromo(SessionInterface $session, CommandeProduitRepository $repository, $commande, PaiementRepository $paiementRepository)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $panier= [];
            if($session->get("sheet") !== null ){ 
                $panier = $session->get("sheet", []);
                $session->remove("sheet");
                $session->set("sheetchoix", $panier);
            }
            else $panier = $session->get("panier", []);
            $session->remove('credit');


            $response = $this->render('commande/admin/traitementpromo.html.twig', [
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                'commande' => $this->entityManager->getRepository(Commande::class)->find($commande),
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }

     #[Route("/Choix_Paiement_extranet/{commande}", name :"choix_paiement_extranet") ]
    public function paiementChoixextranet(SessionInterface $session, CommandeProduitRepository $repository, $commande, PaiementRepository $paiementRepository)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
             $panier= [];
            if($session->get("sheet") !== null ){ 
                $panier = $session->get("sheet", []);
                $session->remove("sheet");
                $session->set("sheetchoix", $panier);
            }
            else $panier = $session->get("panier", []);
            $session->remove('credit');


            $response = $this->render('commande/admin/traitement.html.twig', [
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                'commande' => $this->entityManager->getRepository(Commande::class)->find($commande),
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }


    #[Route("/Suivi/{commande}", name :"paiement") ]
    public function paiement(Request $request, SessionInterface $session, CommandeProduitRepository $repository, Commande $commande, LivrerProduitRepository $livrerRepository)
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {
            if ($commande->getSuivi()) {
                $this->addFlash('notice', 'Paiement déjà effectué');

                $response = $this->redirectToRoute('commande_panier_history', [], Response::HTTP_SEE_OTHER);
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

            $paiement = new Paiement();
            $credit = new Credit();
            $ecriture = new Ecriture();
            $form = $this->createForm(PaiementFormType::class, $paiement);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $entityManager = $this->entityManager;
                if (($paiement->getMontant() >= $commande->getMontant()) || $commande->getCredit()) {
                    $paiement->setUser($this->getUser());
                    $paiement->setCommande($commande);
                    $paiement->setClient($commande->getUser());
                    $commande->setSuivi(true);
                    $commande->setTraitement(new \Datetime());
                    $commande->setPayer(true);
                    $commande->setPaiement($paiement);
                    if($paiement->getType() == 'Espece'){

                        $credit->setType('Espece');
                        $credit->setCompte(571);

                        $ecriture->setType('Espece');
                        $ecriture->setComptecredit(571);
                        $ecriture->setLibellecomptecredit("Caisse");
                    }else{
                        $credit->setType('Banque');
                        $credit->setCompte($paiement->getBanque()->getCompte());

                        $ecriture->setType('Banque');
                        $ecriture->setComptecredit($paiement->getBanque()->getCompte());
                        $ecriture->setLibellecomptecredit($paiement->getBanque()->getNom());


                    }

//
                    $credit->setPaiement($paiement);// ecriture comptable
                    $credit->setMontant($paiement->getMontant());

                    $ecriture->setSolde($paiement->getMontant());
                    $ecriture->setCredit($credit);
                    $ecriture->setMontant($paiement->getMontant());
                    $ecriture->setLibelle('Vente de médicaments');
                    $ecriture->setComptedebit($commande->getUser()->getCompte());
                    $ecriture->setLibellecomptedebit("Compte Client");

                    $entityManager->persist($commande);
                    $entityManager->persist($paiement);
                    $entityManager->persist($credit);
                    $entityManager->persist($ecriture);

                     if($commande->getTva() != 0){
                         $tva = new Ecriture();
                        $tva->setComptecredit("443100");
                        $tva->setLibellecomptecredit("TVA");
                        $tva->setComptedebit("447210");
                        $tva->setLibellecomptedebit('TVA');
                        $tva->setSolde(0);
                        $tva->setMontant($commande->getTva());
                        $tva->setLibelle("TVA sur Vente de médicaments");
                        $entityManager->persist($tva);
                    }
                    $entityManager->flush();
                    $this->addFlash('notice', 'Paiement effectué avec succés');

                    $response = $this->redirectToRoute('commande_panier_history', [], Response::HTTP_SEE_OTHER);
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
                    $this->addFlash('danger', 'Vérifier!!! Montant inferieur a la facture...');
                    $response = $this->redirectToRoute('commande_panier_paiement', ['commande' => $commande->getId()], Response::HTTP_SEE_OTHER);
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
            $panier = $session->get("panier", []);

            $response = $this->render('commande/admin/paiement.html.twig', [
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                 'livrerproduits' => $livrerRepository->findBy(['commande' => $commande]),
                'commande' => $commande,
                'panier' => $panier,
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }



    #[Route("/ModifierCommande/{commande}", name :"commande_edit") ]
    public function redefinition(Request $request, SessionInterface $session, CommandeProduitRepository $repository, Commande $commande)
    {
        if ($this->security->isGranted('ROLE_STOCK')) {
            if ($commande->getSuivi()) {
                $this->addFlash('notice', 'commande déjà traitée');

                $response = $this->redirectToRoute('commande_panier_history', [], Response::HTTP_SEE_OTHER);
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
           

            $response = $this->render('commande/admin/editcommande.html.twig', [
                'commande' => $commande,
                'panier' => $repository->findBy(['commande' => $commande]),
                'prelevement' => $commande->getUser()->isPrelevement(),
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }

    
     #[Route("/ConfirmationModification/{commande}", name :"confirm_modification") ]
    public function redefinitionconfirm(Request $request, SessionInterface $session, CommandeProduitRepository $repository, Commande $commande)
    {
        if ($this->security->isGranted('ROLE_STOCK')) {
             $session->set('confirmation',$request->request->all());
            $response = $this->render('commande/admin/redefinitionconfirm.html.twig', [
                'commande' => $commande,
                'panier' => $repository->findBy(['commande' => $commande]),
                'prelevement' => $commande->getUser()->isPrelevement(),
                'donnees' =>  $request->request->all(),
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




    #[Route("/SuiviCredit/{commande}", name :"paiement_credit") ]
    public function paiementcredit(Request $request, SessionInterface $session, CommandeProduitRepository $repository, Commande $commande, LivrerProduitRepository $livrerRepository)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

            $versement = new Versement();
            $credit = new Credit();
            $ecriture = new Ecriture();
            $form = $this->createForm(VersementType::class, $versement);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $escompte = 0;
               
                null !== $request->request->get('escompte') ? $escompte = $request->request->get('escompte') : null;
                
                $escompte = round($commande->getMontant() * $escompte / 100, 2);

                $entityManager = $this->entityManager;
                // dd(($commande->getMontant() - $commande->getVersement()));
                if ($versement->getMontant() < ($commande->getMontant() - $commande->getVersement())+1) {
                    $commande->setVersement($commande->getVersement() + $versement->getMontant());// MAJ versement
                    if ($commande->getVersement() == $commande->getMontant()- $escompte) {

                        $commande->setPayer(true);
                        $commande->setEscompte($request->request->get('escompte'));
                        $credit->setTva($commande->getTva());
                         if($commande->getTva() != 0){
                             $tva = new Ecriture();
                            $tva->setComptecredit("443100");
                            $tva->setLibellecomptecredit("TVA");
                            $tva->setComptedebit("447210");
                            $tva->setLibellecomptedebit('TVA');
                            $tva->setSolde(0);
                            $tva->setMontant($commande->getTva());
                            $tva->setLibelle("TVA sur Vente de médicaments");
                            $entityManager->persist($tva);
                         }

                    }
                    $versement->setUser($this->getUser());
                    $versement->setCommande($commande);
                    $versement->setClient($commande->getUser());
                    if($versement->getType() == 'Espece'){

                        $credit->setType('Espece');
                        $credit->setCompte(571);

                        $ecriture->setType('Espece');
                        $ecriture->setComptecredit(571);
                        $ecriture->setLibellecomptecredit("Caisse");
                    }else{
                        $credit->setType('Banque');
                        $credit->setCompte($versement->getBanque()->getCompte());

                        $ecriture->setType('Banque');
                        $ecriture->setComptecredit($versement->getBanque()->getCompte());
                        $ecriture->setLibelleComptecredit($versement->getBanque()->getNom());


                    }
//                    $commande->setSuivi(true);
//                    $commande->setPaiement($versement);
//                    $entityManager->persist($commande);
                    $credit->setVersement($versement);// ecriture comptable
                    $credit->setMontant($versement->getMontant());

                    $ecriture->setSolde($versement->getMontant());
                    $ecriture->setCredit($credit);
                    $ecriture->setMontant($versement->getMontant());
                    $ecriture->setLibelle('Vente de médicaments');
                    $ecriture->setComptedebit($versement->getCommande()->getUser()->getCompte());
                    $ecriture->setLibellecomptedebit("Compte Client");

                    $entityManager->persist($versement);
                    $entityManager->persist($credit);
                    $entityManager->persist($ecriture);
                    $entityManager->flush();
                    $this->addFlash('notice', 'Réglement effectué avec succés');

                    $response = $this->redirectToRoute('commande_panier_paiement_credit', ['commande' => $commande->getId()], Response::HTTP_SEE_OTHER);
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
                    $this->addFlash('danger', 'Vérifier le montant saisi');
                    $response = $this->redirectToRoute('commande_panier_paiement_credit', ['commande' => $commande->getId()], Response::HTTP_SEE_OTHER);
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
            $panier = $session->get("panier", []);

            $response = $this->render('commande/admin/paiementcredit.html.twig', [
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                 'livrerproduits' => $livrerRepository->findBy(['commande' => $commande]),
                'commande' => $commande,
                'panier' => $panier,
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }

    #[Route("/ValiderCredit/{commande}", name :"valider_credit") ]
    public function validercredit(Request $request, SessionInterface $session, CommandeProduitRepository $repository, Commande $commande)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            if ($commande->getSuivi()) {
                $this->addFlash('notice', 'Paiement déjà effectué');

                $response = $this->redirectToRoute('commande_panier_credit', [], Response::HTTP_SEE_OTHER);
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
            $em = $this->entityManager;

            $ecriture = new Ecriture();

            $ecriture->setComptecredit($commande->getUser()->getCompte());
            $ecriture->setLibellecomptecredit("Vente à crédit");
            $ecriture->setComptedebit("7011");
            $ecriture->setLibellecomptedebit("Vente de marchandise");
            $ecriture->setSolde(0);
            $ecriture->setMontant($commande->getMontant());
            $ecriture->setLibelle("Vente à crédit ". $commande->getUser()->getNom());


            $commande->setSuivi(true);
            $commande->setTraitement(new \Datetime());
            $em->persist($ecriture);
            $em->persist($commande);
            $em->flush();
            $response = $this->redirectToRoute('commande_panier_credit', [], Response::HTTP_SEE_OTHER);
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }


    #[Route("/Details_commande/{commande}", name :"Detail") ]
    public function details(SessionInterface $session, CommandeProduitRepository $repository, Commande $commande, PaiementRepository $paiementRepository, LivrerProduitRepository $livrerRepository)
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {
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
            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]);

            $response = $this->render('commande/details.html.twig', [
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                'livrerproduits' => $livrerRepository->findBy(['commande' => $commande]),
                'commande' => $commande,
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
        } elseif ($this->security->isGranted('ROLE_CAISSIER')) {


            $response = $this->render('commande/admin/details.html.twig', [
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                  'livrerproduits' => $livrerRepository->findBy(['commande' => $commande]),
                'commande' => $commande,
                'paiement' => $paiementRepository->findOneBy(['commande' => $commande]),
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }

    #[Route("/Details_commande_extranet/{commande}", name :"Detail_extranet") ]
    public function detailsextranet(SessionInterface $session, CommandeProduitRepository $repository, Commande $commande, PaiementRepository $paiementRepository, LivrerProduitRepository $livrerRepository)
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {
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
            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]);

            $response = $this->render('commande/detailextranet.html.twig', [
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                'livrerproduits' => $livrerRepository->findBy(['commande' => $commande]),
                'commande' => $commande,
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }

     #[Route("/Bon_commande/{commande}", name :"bon") ]
    public function bon(SessionInterface $session, CommandeProduitRepository $repository, Commande $commande, PaiementRepository $paiementRepository)
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {
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
            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]);

            $response = $this->render('commande/bon.html.twig', [
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                'commande' => $commande,
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
        } elseif ($this->security->isGranted('ROLE_FINANCE')) {


            $response = $this->render('commande/admin/bon.html.twig', [
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                'commande' => $commande,
                'paiement' => $paiementRepository->findOneBy(['commande' => $commande]),
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
    

     #[Route("/Bon_commande_pdf/{commande}", name :"bon_pdf") ]
    public function bonpdf(SessionInterface $session, CommandeProduitRepository $repository, Commande $commande, PaiementRepository $paiementRepository, PdfService $pdfService)
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {
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
            
          return $pdfService->streamPdf(
           'commande/bonpdf.html.twig', [
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                'commande' => $commande,
            ],
            sprintf('bon-%s.pdf',$commande->getId()."-".$commande->getNumerofacture())
        );
        } elseif ($this->security->isGranted('ROLE_FINANCE')) {


          
        
         return $pdfService->streamPdf(
          'commande/admin/bonpdf.html.twig', [
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                'commande' => $commande,
                'paiement' => $paiementRepository->findOneBy(['commande' => $commande]),
            ],
            sprintf('bon-%s.pdf',$commande->getId()."-".$commande->getNumerofacture())
        );
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

    #[Route("/Pint_Details_commande/{commande}", name :"print_Detail") ]
    public function printdetails(SessionInterface $session, CommandeProduitRepository $repository, Commande $commande, PaiementRepository $paiementRepository)
    {
        if ($this->security->isGranted('ROLE_CLIENT') && $commande->getUser()->getPharmacie() == $this->getUser()->getPharmacie()) {
            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]);

            $response = $this->render('commande/details_print.html.twig', [
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                'commande' => $commande,
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
        } elseif ($this->security->isGranted('ROLE_FINANCE')) {

            $response = $this->render('commande/admin/details_print.html.twig', [
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                'commande' => $commande,
                'paiement' => $paiementRepository->findOneBy(['commande' => $commande]),
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }

    
    #[Route("/Pint_Details_commande_pdf/{commande}", name :"print_Detail_pdf") ]
    public function pdfprintdetails(SessionInterface $session, CommandeProduitRepository $repository, Commande $commande, PaiementRepository $paiementRepository, PdfService $pdfService)
    {
        if ($this->security->isGranted('ROLE_CLIENT') && $commande->getUser()->getPharmacie() == $this->getUser()->getPharmacie()) {
          

         return $pdfService->streamPdf(
            'commande/admin/detailspdf.html.twig',
            [
               'commandeproduits' => $repository->findBy(['commande' => $commande]),
                'commande' => $commande,
                ],
            sprintf('facture-%s.pdf',$commande->getId()."-".$commande->getNumerofacture())
        );

        
            
        } elseif ($this->security->isGranted('ROLE_FINANCE')) {

       

         return $pdfService->streamPdf(
            'commande/admin/detailspdf.html.twig',
            [
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                'commande' => $commande,
                'paiement' => $paiementRepository->findOneBy(['commande' => $commande])
                ],
            sprintf('facture-%s.pdf',$commande->getId()."-".$commande->getNumerofacture())
        );
            // $response = $this->render('commande/admin/details_print.html.twig', [
            //     'commandeproduits' => $repository->findBy(['commande' => $commande]),
            //     'commande' => $commande,
            //     'paiement' => $paiementRepository->findOneBy(['commande' => $commande]),
            // ]);
            // $response->setSharedMaxAge(0);
            // $response->headers->addCacheControlDirective('no-cache', true);
            // $response->headers->addCacheControlDirective('no-store', true);
            // $response->headers->addCacheControlDirective('must-revalidate', true);
            // $response->setCache([
            //     'max_age' => 0,
            //     'private' => true,
            // ]);
            // return $response;
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }

    
    #[Route("/Pint_Bon_commande/{commande}", name :"print_Bon") ]
    public function printbon(SessionInterface $session, CommandeProduitRepository $repository, Commande $commande, PaiementRepository $paiementRepository)
    {
        if ($this->security->isGranted('ROLE_CLIENT') && $commande->getUser()->getPharmacie() == $this->getUser()->getPharmacie()) {
            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]);

            $response = $this->render('commande/bon_print.html.twig', [
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                'commande' => $commande,
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
        } elseif ($this->security->isGranted('ROLE_FINANCE')) {


            $response = $this->render('commande/admin/bon_print.html.twig', [
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                'commande' => $commande,
                'paiement' => $paiementRepository->findOneBy(['commande' => $commande]),
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }

    #[Route("/Suivi/{user}", name :"suivvre") ]
    public function suivre(SessionInterface $session, User $user, CommandeRepository $repository)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

            $panier = $session->get("panier", []);

            
            $response = $this->render('commande/suivi.html.twig', [
                'commandes' => $repository->findBy(['suivi' => false]),
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }

    #[Route("/Imprimer/{commande}", name :"imprimer") ]
    public function imprimer(Request $request, SessionInterface $session, CommandeProduitRepository $repository, Commande $commande)
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {


            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]);

            $response = $this->render('commande/confirm_print.html.twig', [
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                'commande' => $commande,
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }

    #[Route("/Imprimer_extranet_promo/{commande}", name :"imprimer_extranet_promo") ]
    public function imprimerextranetpromo(Request $request, SessionInterface $session, CommandeProduitRepository $repository, Commande $commande)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

            $session->get("sheet") !== null ? $panier = $session->get("sheet", []):
            $panier = $session->get("panier", []);

            $response = $this->render('commande/admin/confirmpromo_print.html.twig', [
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                'commande' => $commande,
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }

    
    #[Route("/Imprimer_extranet/{commande}", name :"imprimer_extranet") ]
    public function imprimerextranet(Request $request, SessionInterface $session, CommandeProduitRepository $repository, Commande $commande)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {


            $panier = $session->get("panier", []);

            $response = $this->render('commande/admin/confirm_print.html.twig', [
                'commandeproduits' => $repository->findBy(['commande' => $commande]),
                'commande' => $commande,
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }

     #[Route("/HistoriqueAdminCommande/", name :"all_commande") ]
    public function allcommande(SessionInterface $session, CommandeRepository $repository)
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {
            //$panier = $session->get("panier", []);
            $commande = $repository->findAll();
            $response = $this->render('commande/admin/allcommande.html.twig', [
                'commande' => $commande,
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }



    #[Route("/{id}", name :"delete", methods : ["POST"]) ]
    public function delete(Request $request, Commande $commande, CommandeProduitRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            if ($this->isCsrfTokenValid('delete' . $commande->getId(), $request->request->get('_token')) && !$commande->getSuivi()) {
                $commandeproduits = $repository->findBy(['commande' => $commande->getId()]);
                foreach($commandeproduits as $commandeproduit){
                    $this->entityManager->remove($commandeproduit);

                }
                $this->entityManager->remove($commande);
                $this->entityManager->flush();
                $this->addFlash('notice','Annulation reussie');
            }
            $response = $this->redirectToRoute('commande_panier_suivi', [], Response::HTTP_SEE_OTHER);
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

    #[Route("/Officine/", name :"officine") ]
    public function officine(ReleveRepository $repository)
    {
        if ($this->security->isGranted('ROLE_CLIENT_ADMIN')) {
            
            // $commandes = $repository->findBy(['suivi' =>true, 'user' => $this->getUser()->getId()],['date' =>"DESC"]);
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]); 
            
            //  $date = date('d');
            // if($date <= 15){
            //    $commandes = $repository->premiertranche($user);
            // }else{
            //    $commandes = $repository->deuxiemetranche($user); 
            // }
            $response = $this->render('officine/index.html.twig', [
                // 'commandes' => $this->Quinzaine($commandes),
                'releves' => $repository->findBy(['client' => $this->getUser()->getId()]),
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

    #[Route("/Releve", name :"releve") ]
    public function releve(CommandeRepository $repository)
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {
            
            $commandes = $repository->findBy(['suivi' => true, 'credit' => true],['traitement' =>"DESC"]);
            
            $response = $this->render('commande/admin/releve.html.twig', [
                'commandes' => $this->Quinzaine($commandes),
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

    #[Route("/ReleveClient/{client}", name :"releve_client") ]
    public function releveclient(Client $client, ReleveRepository $repository)
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {
            
            // $commandes = $repository->findBy(['suivi' => true, 'credit' => true, 'user' => $client->getId()],['traitement' =>"DESC"]);
            
            $response = $this->render('officine/admin/index.html.twig', [
                 'releves' => $repository->findBy(['client' => $client->getId()]),
                // 'commandes' => $this->Quinzaine($commandes),
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
    
    
     #[Route("/Releve_Quinzaine/{releve}", name :"releve_quinzaine") ]
    public function relevequinzaine(Releve $releve)
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {

        // $client = $this->entityManager->getRepository(Client::class)->find($client);
        // $releve = $repo->find($releve);
        
         if($releve != null){

          $commandes = json_decode($releve->getCommandes(), true) ;
         $avantage = json_decode($releve->getAvantage(), true);
         }else{
            $commandes = [];
            $avantage = [];
         }            
           
             
            $response = $this->render('commande/admin/quinze.html.twig', [
                'releve' => $releve,
                'user' => $releve->getClient(),
                'commandes' => $commandes,
                'avantage' => $avantage,
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

    
    
     #[Route("/Releve_Quinzaine_pdf/{releve}", name :"releve_quinzaine_pdf") ]
    public function relevequinzainepdf(Releve $releve, PdfService $pdfService)    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {

         if($releve != null){

          $commandes = json_decode($releve->getCommandes(), true) ;
         $avantage = json_decode($releve->getAvantage(), true);
         }else{
            $commandes = [];
            $avantage = [];
         }                      
          return $pdfService->streamPdf(
            'commande/admin/quinzepdf.html.twig', [
                'releve' => $releve,
                'user' => $releve->getClient(),
                'commandes' => $commandes,
                'avantage' => $avantage,
            ],
            sprintf('releve-%s.pdf',$releve->getId())
        );
             
            // $response = $this->render('commande/admin/quinze.html.twig', [
            //     'releve' => $releve,
            //     'quinzaine' => "Deuxieme",
            //     'mois' => $mois,
            //     'user' => $client,
            //     'commandes' => $commandes,
            //     'avantage' => $avantage,
            // ]);
            // $response->setSharedMaxAge(0);
            // $response->headers->addCacheControlDirective('no-cache', true);
            // $response->headers->addCacheControlDirective('no-store', true);
            // $response->headers->addCacheControlDirective('must-revalidate', true);
            // $response->setCache([
            //     'max_age' => 0,
            //     'private' => true,
            // ]);
            // return $response;
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
    
    #[Route("/Commande/Premier/{mois}", name :"commande_premier") ]
    public function commandepremier($mois, ReleveRepository $repository)
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {
            
            $commandes = $repository->findBy(['periode' => $mois, 'quinzaine' => 1]);
            // dd($commandes);
            $response = $this->render('commande/admin/quinzaine.html.twig', [
                'releves' => $commandes,
                'mois' => $mois,
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

    
    
    #[Route("/Commande/Deuxieme/{mois}", name :"commande_deuxieme") ]
    public function commandedeuxieme($mois, ReleveRepository $repository)
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {
            
            $commandes = $repository->findBy(['periode' => $mois, 'quinzaine' => 2]);
            // dd($commandes);
            $response = $this->render('commande/admin/deuxiemequinzaine.html.twig', [
                'releves' => $commandes,
                'mois' => $mois,
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

    #[Route("/Officine_Quinzaine/{releve}", name :"officine_quinzaine") ]
    public function officinequinzaine(Releve $releve)
    {
        if ($this->security->isGranted('ROLE_CLIENT_ADMIN')) {
            
            // $commandes = $repository->deuxiemetranche($this->getUser()->getId(), $mois);
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]); 
            // $releve = $repository->findOneBy(['client' => $this->getUser()->getId(), 'periode' => $mois, 'quinzaine' => 2]);
            
         if($releve != null){

          $commandes = json_decode($releve->getCommandes(), true) ;
         $avantage = json_decode($releve->getAvantage(), true);
         }else{
            $commandes = [];
            $avantage = [];
         }            
           
             
             
            $response = $this->render('officine/quinze.html.twig', [
                // 'commandes' => $commandes,
                'releve' => $releve,
                'panier' => $panier,
                'commandes' => $commandes,
                'avantage' => $avantage,
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

    
    #[Route("/Officine_Quinzaine_pdf/{releve}", name :"officine_quinzaine_pdf") ]
    public function officinequinzainepdf(Releve $releve, PdfService $pdfService)
    {
        if ($this->security->isGranted('ROLE_CLIENT_ADMIN')){
                               
       
            
            // $commandes = $repository->deuxiemetranche($this->getUser()->getId(), $mois);
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]); 
            // $releve = $repository->findOneBy(['client' => $this->getUser()->getId(), 'periode' => $mois, 'quinzaine' => 2]);
            
         if($releve != null){

          $commandes = json_decode($releve->getCommandes(), true) ;
         $avantage = json_decode($releve->getAvantage(), true);
         }else{
            $commandes = [];
            $avantage = [];
         }            
         
        return $pdfService->streamPdf(
           'officine/quinzepdf.html.twig', [
                // 'commandes' => $commandes,
                'releve' => $releve,
                'panier' => $panier,
                'commandes' => $commandes,
                'avantage' => $avantage,
            ],
            sprintf('releve-%s.pdf',$releve->getId())
        );
             
             
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

     #[Route("/Client/Employe/{id}", name :"pharmauser") ]
    public function pharmauser(SessionInterface $session, CommandeRepository $repository, Client $client)
    {
        if ($this->security->isGranted('ROLE_CLIENT_ADMIN')) {
            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]);;
           
           
            $commandes = $repository->findBy(['pharmaemploye' => $client->getid()]);
            $response = $this->render('client/passee.html.twig', [
                'client' => $client,
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


        /* // On "fabrique" les données

         return $this->render('produit/index.html.twig', compact("dataPanier", "total"));*/
    }


    public function Quinzaine($commandes)
    {
        $result = [];

        foreach ($commandes as $c) {
            $date = $c->getTraitement();

            $annee = $date->format('Y');
            $mois = $date->format('m');
            $jour = (int)$date->format('d');

            $quinzaine = ($jour <= 15) ? 'Q1' : 'Q2';

            $key = $annee . '-' . $mois;

            if (!isset($result[$key])) {
                $result[$key] = [
                    'Q1' => 0,
                    'Q2' => 0
                ];
            }

            $result[$key][$quinzaine] += $c->getMontant();
        }

        return $result;
    }


   

}
