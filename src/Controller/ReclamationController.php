<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Panier;
use App\Entity\User;
use App\Form\ReclamationType;
use App\Repository\LivrerProduitRepository;
use App\Repository\LivrerRepository;
use App\Repository\ReclamationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

#[Route("/{_locale}/Commande_Reclamation") ]
class ReclamationController extends AbstractController
{
    public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

#[Route("/{user}", name :"reclamation_index", methods : ["GET"]) ]
    public function index(SessionInterface $session, ReclamationRepository $reclamationRepository, User $user): Response
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
            $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]);
            $dataPanier = [];

            $response = $this->render('reclamation/index.html.twig', [
                'reclamations' => $reclamationRepository->findBy(['user' => $user, 'cloture' => null]),
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
            $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]);
            $dataPanier = [];

            $response = $this->render('reclamation/cloturer.html.twig', [
                'reclamations' => $reclamationRepository->findBy(['user' =>$user->getId(), 'status' => true]),
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

    #[Route("/new/{user}", name :"reclamation_new", methods : ["GET","POST"]) ]
    public function new(SessionInterface $session, Request $request, User $user): Response
    {
        if ($this->security->isGranted('ROLE_CLIENT')) {
            $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]);
            $dataPanier = [];

              foreach($panier as $commande){
                $commande->getProduit()->setQuantite($commande->getQuantite());
                $dataPanier[] = [
                    "produit" => $commande->getProduit(),
                    "promotion" => $commande->getReduction(),
                ];
            }
            $reclamation = new Reclamation();
            $reclamation->setUser($user);
            $form = $this->createForm(ReclamationType::class, $reclamation, ['id' => $this->getUser()->getId()]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->entityManager;
                $entityManager->persist($reclamation);
                $entityManager->flush();


                $response = $this->redirectToRoute('reclamation_index', ['user' => $user->getId()], Response::HTTP_SEE_OTHER);
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

            $response = $this->render('reclamation/new.html.twig', [
                'reclamation' => $reclamation,
                'form' => $form->createView(),
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

    #[Route("/Traiter/{reclamation}", name :"reclamation_cloturer", methods : ["GET","POST"]) ]
    public function cloturer(SessionInterface $session, Request $request, Reclamation $reclamation): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {
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

    #[Route("/{id}/{user}", name :"reclamation_show", methods : ["GET"]) ]
    public function show(Reclamation $reclamation, LivrerProduitRepository $livrerProduitRepository, SessionInterface $session): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {



            $commandeproduits = $livrerProduitRepository->findBy(['commande' => $reclamation->getCommande()]);
            return $this->render('reclamation/admin/show.html.twig', [
                'reclamation' => $reclamation,
                'commandes' => $commandeproduits,
            ]);
        } else if ($this->security->isGranted('ROLE_CLIENT')) {
            $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]);
            $dataPanier = [];

              foreach($panier as $commande){
                $commande->getProduit()->setQuantite($commande->getQuantite());
                $dataPanier[] = [
                    "produit" => $commande->getProduit(),
                    "promotion" => $commande->getReduction(),
                ];
            }



            $commandeproduits = $livrerProduitRepository->findBy(['commande' => $reclamation->getCommande()]);
            return $this->render('reclamation/show.html.twig', [
                'reclamation' => $reclamation,
                'commandes' => $commandeproduits,
                'panier' => $dataPanier,
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
