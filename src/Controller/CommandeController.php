<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\CommandeProduit;
use App\Entity\Credit;
use App\Entity\Ecriture;
use App\Entity\Paiement;
use App\Entity\User;
use App\Entity\Versement;
use App\Form\CommandeType;
use App\Form\PaiementFormType;
use App\Form\VersementType;
use App\Repository\CommandeProduitRepository;
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


#[Route("/{_locale}/Commande_Panier", name :"commande_panier_") ]
class CommandeController extends AbstractController
{
      public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

    #[Route("/", name :"panier") ]
    public function index(SessionInterface $session, ProduitRepository $produitRepository)
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {

            $panier = $session->get("panier", []);
            $dataPanier = [];
            $total = 0;

            foreach ($panier as $commande) {
//                $product = $produitRepository->find($id);
                $dataPanier[] = [
                    "produit" => $commande['produit'],
                    "promotion" => $commande['promotion']
                ];
                //    var_dump($dataPanier);
//                $total += $product->getPrix() * $quantite;
            }

            $response = $this->render('commande/index.html.twig', [
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
    public function valider(SessionInterface $session, ProduitRepository $produitRepository, CommandeProduitRepository $repository)
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {

            $panier = $session->get("panier", []);
            $dataPanier = [];
//            $em = $this->getDoctrine()->getManager();
            $commande = new Commande();

            if (count($panier) >= 1) {

                $commande->setUser($this->getUser());
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
                    if (!empty($produit->getPromotion())) {//traitement de la promotion avec reduction
                        if (!empty($produit->getPromotion()->getReduction())) {
                            $reductionproduit = $product['produit']->getQuantite() * $produit->getPrix() * $produit->getPromotion()->getReduction() / 100;

                            $reduction = $reduction + $reductionproduit;
                        }
                    }

                    $montant = $montant + $product['produit']->getQuantite() * $produit->getPrix();

                    $commandeproduit = new CommandeProduit($produit, $commande, $produit->getPrix(), $produit->getPrixpublic(), $product['produit']->getQuantite());
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
                $montant = $montant + $tva - $reduction;
                $commande->setMontant(round($montant,2));
                $commande->setTva(round($tva,2));
                $commande->setReduction(round($reduction,2));
                $this->entityManager->persist($commande);
                $this->entityManager->flush();
                $session->remove("panier");
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

    #[Route("/valider_extranet", name :"valider_extranet") ]
    public function validerextranet(SessionInterface $session, ProduitRepository $produitRepository, CommandeProduitRepository $repository)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

            $panier = $session->get("panier", []);
            $dataPanier = [];
            $em = $this->entityManager;
            $commande = new Commande();
            $commande->setAdmin($this->getUser());

            if (count($panier) >= 1) {
                $client = $em->getRepository(User::class)->find($session->get('client'));
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
                    if (!empty($produit->getPromotion())) {//traitement de la promotion avec reduction
                        if (!empty($produit->getPromotion()->getReduction())) {
                            $reductionproduit = $product['produit']->getQuantite() * $produit->getPrix() * $produit->getPromotion()->getReduction() / 100;

                            $reduction = $reduction + $reductionproduit;
                        }
                    }

                    $montant = $montant + $product['produit']->getQuantite() * $produit->getPrix();

                    $commandeproduit = new CommandeProduit($produit, $commande, $produit->getPrix(), $produit->getPrixpublic(), $product['produit']->getQuantite());
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
                $montant = $montant + $tva - $reduction;
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
            $panier = $session->get("panier", []);

            $response = $this->render('commande/suivi.html.twig', [
                'commandes' => $repository->findBy(['user' => $this->getUser()->getId(), 'suivi' => false]),
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

    #[Route("/VosCommandes_extranet/", name :"suivi_extranet") ]
    public function voscommandeextranet(SessionInterface $session, CommandeRepository $repository)
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {
            $panier = $session->get("panier", []);

            $response = $this->render('commande/extranet.html.twig', [
                'commandes' => $repository->extranet($this->getUser()->getId()),
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
        if ($this->security->isGranted('ROLE_FINANCE')) {
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
            $panier = $session->get("panier", []);

            $response = $this->render('commande/history.html.twig', [
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

    #[Route("/Paiement_commande/{commande}", name :"paiement_client") ]
    public function paiementClient(SessionInterface $session, CommandeProduitRepository $repository, Commande $commande, PaiementRepository $paiementRepository)
    {
        if ($this->security->isGranted('ROLE_CLIENT') && $commande->getUser() == $this->getUser()) {
            $panier = $session->get("panier", []);

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

            $panier = $session->get("panier", []);
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

    #[Route("/ConfirmationPaiement_extranet/", name :"confirm_paiement_extranet") ]
    public function confirmextranet(SessionInterface $session)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

            $panier = $session->get("panier", []);
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

            $panier = $session->get("panier", []);
            $session->set("credit", 'credit');
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

    #[Route("/ConfirmationCredit_extranet/", name :"confirm_credit_client_extranet") ]
    public function confirmcreditextranet(SessionInterface $session)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

            $panier = $session->get("panier", []);
            $session->set("credit", 'credit');
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
            $panier = $session->get("panier", []);
            $session->remove('credit');


            $response = $this->render('commande/traitement.html.twig', [
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
            $panier = $session->get("panier", []);
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
    public function paiement(Request $request, SessionInterface $session, CommandeProduitRepository $repository, Commande $commande)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
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
                    $commande->setSuivi(true);
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

    #[Route("/SuiviCredit/{commande}", name :"paiement_credit") ]
    public function paiementcredit(Request $request, SessionInterface $session, CommandeProduitRepository $repository, Commande $commande)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

            $versement = new Versement();
            $credit = new Credit();
            $ecriture = new Ecriture();
            $form = $this->createForm(VersementType::class, $versement);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $entityManager = $this->entityManager;
                if ($versement->getMontant() <= ($commande->getMontant() - $commande->getVersement())) {
                    $commande->setVersement($commande->getVersement() + $versement->getMontant());// MAJ versement
                    if ($commande->getVersement() == $commande->getMontant()) {

                        $commande->setPayer(true);
                        $credit->setTva($commande->getTva());
                         if($commande->getTva != 0){
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
    public function details(SessionInterface $session, CommandeProduitRepository $repository, Commande $commande, PaiementRepository $paiementRepository)
    {
        if ($this->security->isGranted('ROLE_CLIENT') && $commande->getUser() == $this->getUser()) {
            $panier = $session->get("panier", []);

            $response = $this->render('commande/details.html.twig', [
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


            $response = $this->render('commande/admin/details.html.twig', [
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

    #[Route("/Pint_Details_commande/{commande}", name :"print_Detail") ]
    public function printdetails(SessionInterface $session, CommandeProduitRepository $repository, Commande $commande, PaiementRepository $paiementRepository)
    {
        if ($this->security->isGranted('ROLE_CLIENT') && $commande->getUser() == $this->getUser()) {
            $panier = $session->get("panier", []);

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


            $panier = $session->get("panier", []);

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
        if ($this->security->isGranted('ROLE_FINANCE')) {
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

}
