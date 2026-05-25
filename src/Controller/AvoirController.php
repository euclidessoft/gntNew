<?php

namespace App\Controller;

use App\Entity\Avoir;
use App\Entity\Panier;
use App\Entity\AvoirReste;
use App\Entity\Commande;
use App\Entity\Retour;
use App\Entity\Livrer;
use App\Entity\LivrerReste;
use App\Entity\Reclamation;
use App\Entity\RetourProduit;
use App\Entity\ReclamationProduit;
use App\Form\AvoirType;
use App\Repository\AvoirRepository;
use App\Repository\ReclamationProduitRepository;
use App\Repository\AvoirResteRepository;
use App\Repository\CommandeProduitRepository;
use App\Repository\CommandeRepository;
use App\Repository\LivrerProduitRepository;
use App\Repository\LivrerRepository;
use App\Repository\LivrerResteRepository;
use App\Repository\ProduitRepository;
use App\Repository\ReclamationRepository;
use App\Repository\RetourProduitRepository;
use App\Repository\RetourRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use App\Service\PdfService;

#[Route("/{_locale}/Avoir") ]
class AvoirController extends AbstractController
{
      public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }
    
    #[Route("/", name :"avoir_index", methods : ["GET"]) ]
    public function index(AvoirRepository $avoirRepository, SessionInterface $session): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            
            $response = $this->render('avoir/admin/index.html.twig', [
                'avoirs' => $avoirRepository->findAll(),

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
        } elseif ($this->security->isGranted('ROLE_CLIENT_ADMIN')) {
            
            $response = $this->render('avoir/index.html.twig', [
                'avoirs' => $avoirRepository->findby(['client' => $this->getUser()]),
                'panier' => $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]),
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

    #[Route("/Choix_Avoir", name :"avoir_choix", methods : ["GET"]) ]
    public function choix(AvoirRepository $avoirRepository): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
           
            $response = $this->render('avoir/admin/choix.html.twig');
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

    #[Route("/Reste", name :"avoir_reste", methods : ["GET"]) ]
    public function reste(LivrerRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
           
            $response = $this->render('avoir/admin/reste.html.twig', [
                'livrers' => $repository->findBy(['reste' => true]),
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

    #[Route("/Reclamation", name :"avoir_reclamation", methods : ["GET"]) ]
    public function reclamation(ReclamationRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
           
            $response = $this->render('avoir/admin/reclamation.html.twig', [
                'reclamations' => $repository->findBy(['cloture' => null]),
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

    
    #[Route("/Retour/", name :"avoir_retour", methods : ["GET"]) ]
    public function retour(RetourRepository $repository, SessionInterface $session): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

            $response = $this->render('avoir/admin/avoirretour.html.twig', [
                'retours' => $repository->findBy(['avoir' => null]),
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



    #[Route("/new/reste/{id}", name :"avoir_new_reste", methods : ["GET","POST"]) ]
    public function newreste(Commande $commande, LivrerResteRepository $livrerResteRepository, CommandeProduitRepository $comprodrepository, ProduitRepository $repository, SessionInterface $session): Response
    {// traitement livraison
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $session->remove("livraison");
            $commandeproduits = $livrerResteRepository->findBy(['commande' => $commande]);
            $listcommande = [];
            foreach ($commandeproduits as $commandeproduit) {
                $stock = $repository->find($commandeproduit->getProduit()->getId())->getStock();
                $commandeproduit->setStock($stock);
                $listcommande[] = $commandeproduit;
            }
            $session->set("traitement", []);
            $response = $this->render('avoir/admin/new_reste.html.twig', [
                'commandes' => $listcommande,
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

//     #[Route("/new/reclamation/{id}/", name :"avoir_new_reclamation", methods : ["GET","POST"]) ]
//     public function newreclamation(Reclamation $reclamation, Request $request, LivrerResteRepository $livrerResteRepository, ProduitRepository $repository, LivrerProduitRepository $livrerProduitRepository, SessionInterface $session): Response
//     {// traitement livraison
//         if ($this->security->isGranted('ROLE_FINANCE')) {
//             $commande = $reclamation->getCommande();
//             $em = $this->entityManager;
//             $avoir = new Avoir($commande->getUser(),$this->getUser(), $commande);
//             $avoir->setReclamation($reclamation);
//             $form = $this->createForm(AvoirType::class, $avoir);
//             $form->handleRequest($request);

//             if ($form->isSubmitted() && $form->isValid()) {
//                 $reclamation->setCloture(new \Datetime());
//                 $reclamation->setUsercloture($this->getUser());
//                 $em->persist($avoir);
//                 $em->persist($reclamation);
//                 $em->flush();


//                 return $this->redirectToRoute('avoir_index', [], Response::HTTP_SEE_OTHER);
//             }

// //            return $this->render('avoir/admin/edit.html.twig', [
// //                'avoir' => $avoir,
// //                'form' => $form->createView(),
// //            ]);
//             $commandeproduits = $livrerResteRepository->findBy(['commande' => $commande]);
//             $listcommande = [];
//             foreach ($commandeproduits as $commandeproduit) {
//                 $stock = $repository->find($commandeproduit->getProduit()->getId())->getStock();
//                 $commandeproduit->setStock($stock);
//                 $listcommande[] = $commandeproduit;
//             }
//             $session->set("traitement", []);
//             $response = $this->render('avoir/admin/new_reclamation.html.twig', [
//                 'commandes' => $listcommande,
//                 'commandereference' => $commande,
//                 'reclamation' => $reclamation,
//                 'form' => $form->createView(),
//             ]);
//             $response->setSharedMaxAge(0);
//             $response->headers->addCacheControlDirective('no-cache', true);
//             $response->headers->addCacheControlDirective('no-store', true);
//             $response->headers->addCacheControlDirective('must-revalidate', true);
//             $response->setCache([
//                 'max_age' => 0,
//                 'private' => true,
//             ]);
//             return $response;
//         } else {
//             $response = $this->redirectToRoute('security_logout');
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
//     }

 #[Route("/new/reclamation/", name :"avoir_new_reclamation", methods : ["GET","POST"]) ]
    public function newreclamation( Request $request, ProduitRepository $repository): Response
    {// traitement livraison
        if ($this->security->isGranted('ROLE_FINANCE')) {
            
            $em = $this->entityManager;
            $reclamation = $em->getRepository(Reclamation::class)->find($request->get('reclamation'));
            $commande = $reclamation->getCommande();
            $reclamationproduits = $em->getRepository(ReclamationProduit::class)->findBy(['commande' => $commande]);
            $montant = 0;
            foreach($reclamationproduits as $reclamationproduit){
                    $montant = $montant + $reclamationproduit->getPrix() * $reclamationproduit->getQuantite();
            }
            $avoir = new Avoir($commande->getUser(),$this->getUser(), $commande);
            $avoir->setReclamation($reclamation);
            $avoir->setMontant($montant);

                $reclamation->setCloture(new \Datetime());
                $reclamation->setUsercloture($this->getUser());
                $em->persist($avoir);
                $em->persist($reclamation);
                $em->flush();

        $this->addFlash('notice', 'avoir créer avec succès');

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

      #[Route("/new/retour/", name :"avoir_new_retour", methods : ["POST"]) ]
    public function retour_avoir(Request $request): Response
    {

        if ($this->security->isGranted('ROLE_FINANCE')) {



            $em = $this->entityManager;
            $retour = $em->getRepository(Retour::class)->find($request->get('retour'));
            $RetourProduits = $em->getRepository(RetourProduit::class)->find($retour->getId());
            $montant = 0;
            foreach($RetourProduits as $RetourProduit){
                $RetourProduit->setValider(true);
                $RetourProduit->setRembourser(true);
                $RetourProduit->setAvoir(true);
                $em->persist($RetourProduit);
                $em->flush();
                $montant = $momtant + $RetourProduit->getQuantite() * $RetourProduit->getPrix();
            }
            $avoir = new Avoir($retour->getCommande()->getUser(), $this->getUser(), $retour->getCommande());
            $avoir->setMontant($montant);
            $avoir->setRetour($retour);
            $em->persist($avoir);

            $retour->setAvoir($avoir);
            $em->persist($retour);
            $em->flush();

             $this->addFlash('notice', 'avoir créer avec succès');
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


    #[Route("/validerNewreste/", name :"avoir_valider_reste") ]
    public function validernewreste(Request $request, LivrerResteRepository $livrerResteRepository)
    {
        // On récupère le panier actuel

        if ($request->isXmlHttpRequest()) {// traitement de la requete ajax
            $em = $this->entityManager;
            $produits = explode(";", $request->get('prod'));// recuperation des produit
            $com = $request->get('commande');// recuperation de la commamde
            $commande = $em->getRepository(Commande::class)->find($com);
            $avoir = $em->getRepository(Avoir::class)->findOneBy(['commande' => $commande]);

            if ($avoir == null) {
                $avoir = new Avoir($commande->getUser(), $this->getUser(), $commande);
            }

            $montantavoir = 0;
            $livraison = 0;
            foreach ($produits as $prod) {
                if ($prod != 0) {
                    $reste = $livrerResteRepository->findOneBy(['commande' => $com, 'produit' => $prod]);
                    $livraison = $reste->getLivrer()->getId();
                    $avoirresete = new AvoirReste($reste->getLivrer(), $commande, $reste->getProduit(), $reste->getQuantite(), $reste->getQuantitelivre(), $reste->getClient(), $this->getUser(), $avoir);
                    $montantavoir = $montantavoir + (($reste->getQuantite() - $reste->getQuantitelivre()) * $reste->getSession());
                    $em->persist($avoirresete);
                    $em->remove($reste);
                }
            }
            $avoir->setMontant($avoir->getMontant() + $montantavoir);
            $em->persist($avoir);
            $em->flush();
            // verification epuisement reste a livrer
            $livrerreste = $livrerResteRepository->findOneBy(['livrer' => $livraison]);
            if (empty($livrerreste)) {// suppression reste a livrer
                $livrer = $em->getRepository(Livrer::class)->find($livraison);
                $livrer->setReste(false);
                $em->persist($livrer);
                $em->flush();
            }
            //fin verif
            $this->addFlash('notice', 'Avoir créé avec succès');
            $res['id'] = 'ok';


            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
            return $response;
        }

    }



//    public function new(Request $request, LivrerReste $reste): Response
//    {
//        if ($this->security->isGranted('ROLE_FINANCE')) {
//            $avoir = new Avoir();
//            $form = $this->createForm(AvoirType::class, $avoir);
//            $form->handleRequest($request);
//
//            if ($form->isSubmitted() && $form->isValid()) {
//                $entityManager = $this->getDoctrine()->getManager();
//                $avoir->setAdmin($this->getUser());
//                $avoir->setClient($this->getUser());
//                $entityManager->persist($avoir);
//                $entityManager->flush();
//
//                return $this->redirectToRoute('avoir_index', [], Response::HTTP_SEE_OTHER);
//            }
//
//            return $this->render('avoir/admin/new.html.twig', [
//                'avoir' => $avoir,
//                'form' => $form->createView(),
//            ]);
//        } else {
//            $response = $this->redirectToRoute('security_logout');
//            $response->setSharedMaxAge(0);
//            $response->headers->addCacheControlDirective('no-cache', true);
//            $response->headers->addCacheControlDirective('no-store', true);
//            $response->headers->addCacheControlDirective('must-revalidate', true);
//            $response->setCache([
//                'max_age' => 0,
//                'private' => true,
//            ]);
//            return $response;
//        }
//    }

     #[Route("/Officine/", name :"avoir_officine", methods : ["GET"]) ]
    public function officine(AvoirRepository $avoirRepository, SessionInterface $session): Response
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {
             $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]);;
           
            
            $response = $this->render('officine/avoir.html.twig', [
                'avoirs' => $avoirRepository->findby(['client' => $this->getUser()]),
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

    #[Route("/{id}", name :"avoir_show", methods : ["GET"]) ]
    public function show(Avoir $avoir, ReclamationProduitRepository $reclamationRepository, RetourProduitRepository $retourRepository): Response
    {
        if($avoir->getReclamation() !== null){
            $commandeproduits = $reclamationRepository->findBy(['reclamation' => $avoir->getReclamation()->getId()]);
        }else{
             $commandeproduits = $retourRepository->findBy(['retour' => $avoir->getretour()->getId()]);
        }
        if ($this->security->isGranted('ROLE_FINANCE')) {
           
            $response = $this->render('avoir/admin/show.html.twig', [
                'avoir' => $avoir,
                'commandeproduits' => $commandeproduits,
                // 'details' => $avoirResteRepository->findBy(['avoir' => $avoir])
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
            
            $response = $this->render('avoir/show.html.twig', [
                'avoir' => $avoir,
                'commandeproduits' => $commandeproduits,
                // 'details' => $avoirResteRepository->findBy(['avoir' => $avoir]),
                'panier' => $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]),
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

    
    #[Route("Pdf/{id}", name :"avoir_show_pdf", methods : ["GET"]) ]
    public function showpdf(Avoir $avoir, ReclamationProduitRepository $reclamationRepository, RetourProduitRepository $retourRepository, PdfService $pdfService): Response
    {
        if($avoir->getReclamation() !== null){
            $commandeproduits = $reclamationRepository->findBy(['reclamation' => $avoir->getReclamation()->getId()]);
        }else{
             $commandeproduits = $retourRepository->findBy(['retour' => $avoir->getretour()->getId()]);
        }
        if ($this->security->isGranted('ROLE_FINANCE')) {
      
        return $pdfService->streamPdf(
            'avoir/admin/showpdf.html.twig', [
                'avoir' => $avoir,
                'commandeproduits' => $commandeproduits,
                // 'details' => $avoirResteRepository->findBy(['avoir' => $avoir])
            ],
            sprintf('avoir-%s.pdf',$avoir->getId())
        );
           
        } elseif ($this->security->isGranted('ROLE_CLIENT')) {
        
         return $pdfService->streamPdf(
           'avoir/showpdf.html.twig', [
                'avoir' => $avoir,
                'commandeproduits' => $commandeproduits,
                // 'details' => $avoirResteRepository->findBy(['avoir' => $avoir]),
            
                ],
            sprintf('avoir-%s.pdf',$avoir->getId())
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

    #[Route("/Print/{id}", name :"avoir_show_print", methods : ["GET"]) ]
    public function showprint(Avoir $avoir, ReclamationProduitRepository $reclamationRepository, RetourProduitRepository $retourRepository): Response
    {
        if($avoir->getReclamation() !== null){
            $commandeproduits = $reclamationRepository->findBy(['reclamation' => $avoir->getReclamation()->getId()]);
        }else{
             $commandeproduits = $retourRepository->findBy(['retour' => $avoir->getretour()->getId()]);
        }
        if ($this->security->isGranted('ROLE_FINANCE')) {
           
            $response = $this->render('avoir/admin/show_print.html.twig', [
                'avoir' => $avoir,
                'commandeproduits' => $commandeproduits,
                // 'details' => $avoirResteRepository->findBy(['avoir' => $avoir])
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
            
            $response = $this->render('avoir/show_print.html.twig', [
                'avoir' => $avoir,
                'commandeproduits' => $commandeproduits,
                // 'details' => $avoirResteRepository->findBy(['avoir' => $avoir]),
                'panier' => $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]),
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

    // #[Route("/{id}/edit", name :"avoir_edit", methods : ["GET","POST"]) ]
    // public function edit(Request $request, Avoir $avoir): Response
    // {
    //     $form = $this->createForm(AvoirType::class, $avoir);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $this->entityManager->flush();

    //         return $this->redirectToRoute('avoir_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->render('avoir/admin/edit.html.twig', [
    //         'avoir' => $avoir,
    //         'form' => $form->createView(),
    //     ]);
    // }

    // #[Route("/{id}", name :"avoir_delete", methods : ["POST"]) ]
    // public function delete(Request $request, Avoir $avoir): Response
    // {
    //     if ($this->isCsrfTokenValid('delete' . $avoir->getId(), $request->request->get('_token'))) {
    //         $entityManager = $this->entityManager;
    //         $entityManager->remove($avoir);
    //         $entityManager->flush();
    //     }

    //     return $this->redirectToRoute('avoir_index', [], Response::HTTP_SEE_OTHER);
    // }
   
}
