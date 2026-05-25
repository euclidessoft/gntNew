<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Pharmacie;
use App\Entity\Panier;
use App\Form\ClientType;
use App\Form\ClientEmployerType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use App\Service\PdfService;

#[Route("/{_locale}/Client") ]
class ClientController extends AbstractController
{
    public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

    #[Route("/", name :"client_index") ]
    public function index(EntityManagerInterface $entityManager): Response
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {
            $client = $entityManager->getRepository(Client::class)->findBy(['client' =>true]);
            return $this->render('client/index.html.twig', [
                'client' => $client,
            ]);
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

     #[Route("_pdf/", name :"client_index_pdf") ]
    public function indexpdf(EntityManagerInterface $entityManager, PdfService $pdfService): Response
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {
            
            $client = $entityManager->getRepository(Client::class)->findBy(['client' =>true]);
           
             
         return $pdfService->streamPdf(
            'client/indexpdf.html.twig', [
                'client' => $client,
            ],
            sprintf('client-%s.pdf', 1)
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

     #[Route("/Users", name :"client_users") ]
    public function users(EntityManagerInterface $entityManager): Response
    {
        if ($this->security->isGranted('ROLE_CLIENT_ADMIN')) {
              $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]);
            $dataPanier = [];

              foreach($panier as $commande){
                $commande->getProduit()->setQuantite($commande->getQuantite());
                $dataPanier[] = [
                    "produit" => $commande->getProduit(),
                    "promotion" => $commande->getReduction(),
                ];
            }

            $client = $entityManager->getRepository(Client::class)
                                        ->findBy(['pharmacie' => $this->getUser()->getPharmacie()]);
            return $this->render('client/users.html.twig', [
                'client' => $client,
                'panier' => $dataPanier,
            ]);
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


    #[Route("/new", name :"client_new", methods : ["GET","POST"]) ]
    public function new(Request $request, UserPasswordHasherInterface $encoder, TokenGeneratorInterface $tokenGenerator): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $client = new Client();
            $form = $this->createForm(ClientType::class, $client);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->entityManager;
                
                // creation de la pharmacie
                $pharmacie = new Pharmacie();
                $pharmacie->setNom($client->getNom()." ".$client->getPrenom());
                $entityManager->persist($pharmacie);
                $entityManager->flush();
                //fin

                $hashpass = $encoder->hashPassword($client, $client->getPassword());
                

                $client->setPharmacie($pharmacie);
                $client->setPassword($hashpass);
                $client->setUsername($client->getNom());
                $client->setRoles(["ROLE_CLIENT_ADMIN"]);
                $client->setClient(true);
                $client->setFonction('Client');
                $token = $tokenGenerator->generateToken();
                $client->setResetToken($token);

                $entityManager->persist($client);
                $entityManager->flush();
                $compte = '411' . str_pad($client->getId() + 1, 4, '0', STR_PAD_LEFT);
                $client->setCompte($compte);

                $entityManager->persist($client);
                $entityManager->flush();

                $this->addFlash('notice', "Compte client crée avec succée");
                return $this->redirectToRoute("client_index");
            }


            return $this->render('client/new.html.twig', [
                'form' => $form->createView(),
                'client' => $client
            ]);
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

    

    #[Route("/Registration", name :"client_register", methods : ["GET","POST"]) ]
    public function registration(Request $request, UserPasswordHasherInterface $encoder, TokenGeneratorInterface $tokenGenerator): Response
    {
        if ($this->security->isGranted('ROLE_CLIENT_ADMIN')) {

             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]);
            $dataPanier = [];

              foreach($panier as $commande){
                $commande->getProduit()->setQuantite($commande->getQuantite());
                $dataPanier[] = [
                    "produit" => $commande->getProduit(),
                    "promotion" => $commande->getReduction(),
                ];
            }

            $client = new Client();
            $form = $this->createForm(ClientEmployerType::class, $client);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->entityManager;
                
                // creation de la pharmacie
                $pharmacie = $entityManager->getRepository(Pharmacie::class)
                                                    ->find($this->getUser()->getPharmacie()->getId());

                $hashpass = $encoder->hashPassword($client, $client->getPassword());
                

                $client->setPharmacie($pharmacie);
                $client->setCompte(null);
                $client->setPassword($hashpass);
                $client->setUsername($client->getNom());
                $client->setRoles(["ROLE_CLIENT"]);
                $client->setTuteur($this->getUser());// definition superieur
                $client->setClient(false); // definition du type de compte client 
                $client->setFonction('Emplyer-Client');
                $token = $tokenGenerator->generateToken();
                $client->setResetToken($token);

                $entityManager->persist($client);
                $entityManager->flush();
                // $compte = '411' . str_pad($client->getId() + 1, 4, '0', STR_PAD_LEFT);
                // $client->setCompte($compte);

                $entityManager->persist($client);
                $entityManager->flush();

                $this->addFlash('notice', "Compte client crée avec succée");
                return $this->redirectToRoute("client_users");
            }


            return $this->render('client/register.html.twig', [
                'form' => $form->createView(),
                'client' => $client,
                'panier' => $dataPanier,
            ]);
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

    #[Route("/{id}/edit", name :"client_edit", methods : ["GET","POST"]) ]
    public function edit(Request $request, Client $client): Response
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {

            $form = $this->createForm(ClientType::class, $client);
            
            $form->remove('password');
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->entityManager->flush();
                $this->addFlash('notice', 'Client modifié avec succès');
                return $this->redirectToRoute('client_index', [], Response::HTTP_SEE_OTHER);
            }
            return $this->render('client/edit.html.twig', [
                'form' => $form->createView(),
            ]);
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
    
    #[Route("/Show/{id}", name :"client_show", methods : ["GET"]) ]
    public function show(Client $client): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {
                $response = $this->render('client/show.html.twig', [
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
    
}
