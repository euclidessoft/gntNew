<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Entity\Panier;
use App\Form\ReponseType;
use App\Repository\ReponseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

#[Route("/reponse") ]
class ReponseController extends AbstractController
{

    public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }


#[Route("/", name :"reponse_index", methods : ["GET"]) ]
    public function index(ReponseRepository $reponseRepository): Response
    {
        return $this->render('reponse/index.html.twig', [
            'reponses' => $reponseRepository->findAll(),
        ]);
    }

    #[Route("/new/{reclamation}", name :"reponse_new", methods : ["GET","POST"]) ]
    public function new(Reclamation $reclamation, Request $request, SessionInterface $session): Response
    {
        if ($this->security->isGranted('ROLE_BACK')) {

            $reponse = new Reponse($this->getUser(), $reclamation);
            $form = $this->createForm(ReponseType::class, $reponse);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->entityManager;
                $entityManager->persist($reponse);
                $entityManager->flush();

                return $this->redirectToRoute('reclamation_show', ['id' => $reclamation->getId(), 'user'=> $this->getUser()->getId()], Response::HTTP_SEE_OTHER);
            }

            return $this->render('reponse/admin/new.html.twig', [
                'reponse' => $reponse,
                'form' => $form->createView(),
            ]);

        }
        else if ($this->security->isGranted('ROLE_CLIENT')) {

            $this->getUser()->getTuteur() === null ?
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]) :
             $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getTuteur()->getId()]); 
       
            $reponse = new Reponse($this->getUser(), $reclamation);
            $form = $this->createForm(ReponseType::class, $reponse);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->entityManager;
                $entityManager->persist($reponse);
                $entityManager->flush();

                return $this->redirectToRoute('reclamation_show', ['id' => $reclamation->getId(), 'user'=> $this->getUser()->getId()], Response::HTTP_SEE_OTHER);
            }

            return $this->render('reponse/new.html.twig', [
                'reponse' => $reponse,
                'form' => $form->createView(),
                'panier' => $panier,
            ]);
        }
        else{
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

    #[Route("/{id}", name :"reponse_show", methods : ["GET"]) ]
    public function show(Reponse $reponse): Response
    {
        return $this->render('reponse/show.html.twig', [
            'reponse' => $reponse,
        ]);
    }

    #[Route("/{id}/edit", name :"reponse_edit", methods : ["GET","POST"]) ]
    public function edit(Request $request, Reponse $reponse): Response
    {
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('reponse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reponse/edit.html.twig', [
            'reponse' => $reponse,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name :"reponse_delete", methods : ["POST"]) ]
    public function delete(Request $request, Reponse $reponse): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reponse->getId(), $request->request->get('_token'))) {
            $entityManager = $this->entityManager;
            $entityManager->remove($reponse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('reponse_index', [], Response::HTTP_SEE_OTHER);
    }
}
