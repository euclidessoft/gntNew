<?php

namespace App\Controller;

use App\Entity\TypeSanction;
use App\Form\TypeSanctionType;
use App\Repository\TypeSanctionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/{_locale}/Typesanction") ]
class TypeSanctionController extends AbstractController
{
       public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

#[Route("/", name :"type_sanction_index", methods : ["GET"]) ]
    public function index(TypeSanctionRepository $typeSanctionRepository): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            return $this->render('type_sanction/index.html.twig', [
                'type_sanctions' => $typeSanctionRepository->findAll(),
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

    #[Route("/new", name :"type_sanction_new", methods : ["GET","POST"]) ]
    public function new(Request $request): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            $typeSanction = new TypeSanction();
            $form = $this->createForm(TypeSanctionType::class, $typeSanction);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($typeSanction);
                $entityManager->flush();

                return $this->redirectToRoute('type_sanction_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('type_sanction/new.html.twig', [
                'type_sanction' => $typeSanction,
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

    #[Route("/{id}", name :"type_sanction_show", methods : ["GET"]) ]
    public function show(TypeSanction $typeSanction): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            return $this->render('type_sanction/show.html.twig', [
                'type_sanction' => $typeSanction,
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

    #[Route("/{id}/edit", name :"type_sanction_edit", methods : ["GET","POST"]) ]
    public function edit(Request $request, TypeSanction $typeSanction): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            $form = $this->createForm(TypeSanctionType::class, $typeSanction);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute('type_sanction_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('type_sanction/edit.html.twig', [
                'type_sanction' => $typeSanction,
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

    #[Route("/{id}", name :"type_sanction_delete", methods : ["POST"]) ]
    public function delete(Request $request, TypeSanction $typeSanction): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            if ($this->isCsrfTokenValid('delete' . $typeSanction->getId(), $request->request->get('_token'))) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($typeSanction);
                $entityManager->flush();
            }

            return $this->redirectToRoute('type_sanction_index', [], Response::HTTP_SEE_OTHER);
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
