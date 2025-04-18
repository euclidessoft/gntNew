<?php

namespace App\Controller;

use App\Entity\EmployeFormation;
use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/{_locale}/Formation") ]
class FormationController extends AbstractController
{
       public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }
    #[Route("/", name :"formation_index", methods : ["GET"]) ]
    public function index(FormationRepository $formationRepository): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            return $this->render('formation/index.html.twig', [
                'formations' => $formationRepository->findAll(),
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

    #[Route("/new", name :"formation_new", methods : ["GET","POST"]) ]
    public function new(Request $request): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            $formation = new Formation();
            $form = $this->createForm(FormationType::class, $formation);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($formation);
                $entityManager->flush();

                return $this->redirectToRoute('formation_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('formation/new.html.twig', [
                'formation' => $formation,
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

    #[Route("/{id}", name :"formation_show", methods : ["GET"]) ]
    public function show(Formation $formation): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            return $this->render('formation/show.html.twig', [
                'formation' => $formation,
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

    #[Route("/{id}/edit", name :"formation_edit", methods : ["GET","POST"]) ]
    public function edit(Request $request, Formation $formation): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            $form = $this->createForm(FormationType::class, $formation);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute('formation_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('formation/edit.html.twig', [
                'formation' => $formation,
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

    #[Route("/{id}", name :"formation_delete", methods : ["POST"]) ]
    public function delete(Request $request, Formation $formation): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            if ($this->isCsrfTokenValid('delete' . $formation->getId(), $request->request->get('_token'))) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($formation);
                $entityManager->flush();
            }

            return $this->redirectToRoute('formation_index', [], Response::HTTP_SEE_OTHER);
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
