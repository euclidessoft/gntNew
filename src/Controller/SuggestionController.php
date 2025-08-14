<?php

namespace App\Controller;

use App\Entity\Suggestion;
use App\Entity\Panier;
use App\Form\SuggestionType;
use App\Repository\SuggestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/{_locale}/suggestion") ]
class SuggestionController extends AbstractController
{
       public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

#[Route("/", name :"suggestion_index", methods : ["GET"]) ]
    public function index(SuggestionRepository $suggestionRepository, SessionInterface $session): Response
    {

        if ($this->security->isGranted('ROLE_CLIENT')) {

            $response = $this->render('suggestion/index.html.twig', [
                'suggestions' => $suggestionRepository->findBy(['client' => $this->getUser()]),
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
        } elseif ($this->security->isGranted('ROLE_BACK')) {

            $response = $this->render('suggestion/admin/index.html.twig', [
                'suggestions' => $suggestionRepository->findAll(),
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

    #[Route("/new", name :"suggestion_new", methods : ["GET","POST"]) ]
    public function new(Request $request, SessionInterface $session): Response
    {
        $suggestion = new Suggestion();
        $form = $this->createForm(SuggestionType::class, $suggestion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;
            $entityManager->persist($suggestion);
            $entityManager->flush();

            return $this->redirectToRoute('suggestion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('suggestion/new.html.twig', [
            'suggestion' => $suggestion,
            'form' => $form->createView(),
            'panier' => $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]),
        ]);
    }

    #[Route("/{id}", name :"suggestion_show", methods : ["GET"]) ]
    public function show(Suggestion $suggestion): Response
    {
        return $this->render('suggestion/show.html.twig', [
            'suggestion' => $suggestion,
        ]);
    }

    #[Route("/{id}/edit", name :"suggestion_edit", methods : ["GET","POST"]) ]
    public function edit(Request $request, Suggestion $suggestion): Response
    {
        $form = $this->createForm(SuggestionType::class, $suggestion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('suggestion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('suggestion/edit.html.twig', [
            'suggestion' => $suggestion,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name :"suggestion_delete", methods : ["POST"]) ]
    public function delete(Request $request, Suggestion $suggestion): Response
    {
        if ($this->isCsrfTokenValid('delete'.$suggestion->getId(), $request->request->get('_token'))) {
            $entityManager = $this->entityManager;
            $entityManager->remove($suggestion);
            $entityManager->flush();
        }

        return $this->redirectToRoute('suggestion_index', [], Response::HTTP_SEE_OTHER);
    }
}
