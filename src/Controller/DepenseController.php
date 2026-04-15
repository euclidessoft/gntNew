<?php

namespace App\Controller;

use App\Entity\Credit;
use App\Entity\Debit;
use App\Entity\Depense;
use App\Entity\Ecriture;
use App\Form\DepenseType;
use App\Repository\DepenseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Complement\Solde as Solde;

#[Route("/{_locale}/depense") ]
class DepenseController extends AbstractController
{
    public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

    #[Route("/", name :"depense_index", methods : ["GET"]) ]
    public function index(DepenseRepository $depenseRepository): Response
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {
            return $this->render('depense/index.html.twig', [
                'depenses' => $depenseRepository->findAll(),
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

    #[Route("/new", name :"depense_new", methods : ["GET","POST"]) ]
    public function new(Request $request, Solde $solde): Response
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {
            $depense = new Depense();
            $debit = new Debit();
            $ecriture = new Ecriture();
            $form = $this->createForm(DepenseType::class, $depense);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
//                $entityManager = $this->getDoctrine()->getManager();
                $depense->setUser($this->getUser());
                $montant = 0;
                if ($depense->getType() == 'Espece') {
                    $montant = $solde->montantcaisse($this->entityManager, 571);
                    $depense->setCompte($depense->getCategorie()->getCompte());

                    $debit->setType('Espece');
                    $debit->setCompte(571);

                    $ecriture->setType('Espece');
                    $ecriture->setComptecredit($depense->getCategorie()->getCompte());
                    $ecriture->setLibellecomptecredit($depense->getCategorie()->getNom());
                    $ecriture->setComptedebit(571);
                    $ecriture->setLibellecomptedebit('Caisse');
                } else {
                    $montant = $solde->montantbanque($this->entityManager, $depense->getBanque()->getCompte());
                    $depense->setType('Banque');
                    $depense->setCompte($depense->getCategorie()->getCompte());

                    $debit->setType('Banque');
                    $debit->setCompte($depense->getBanque()->getCompte());

                    $ecriture->setType('Banque');
                    $ecriture->setComptecredit($depense->getCategorie()->getCompte());
                    $ecriture->setLibellecomptecredit($depense->getCategorie()->getNom());
                    $ecriture->setComptedebit($depense->getBanque()->getCompte());
                    $ecriture->setLibellecomptedebit($depense->getBanque()->getNom());
                }
                if ($depense->getMontant() <= $montant) {
                    $debit->setDepense($depense);
                    $debit->setMontant($depense->getMontant());

                    $ecriture->setDebit($debit);
                    $ecriture->setSolde(-$depense->getMontant());
                    $ecriture->setMontant($depense->getMontant());
                    $ecriture->setLibelle($depense->getLibelle());

                    $this->entityManager->persist($depense);
                    $this->entityManager->persist($debit);
                    $this->entityManager->persist($ecriture);
                    $this->entityManager->flush();

                    return $this->redirectToRoute('depense_index', [], Response::HTTP_SEE_OTHER);
                } else {
                    $this->addFlash('notice', 'Montant non disponible');
                }
            }

            return $this->render('depense/new.html.twig', [
                'depense' => $depense,
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

    #[Route("/{id}", name :"depense_show", methods : ["GET"]) ]
    public function show(Depense $depense): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            return $this->render('depense/show.html.twig', [
                'depense' => $depense,
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

    #[Route("/{id}/edit", name :"depense_edit", methods : ["GET","POST"]) ]
    public function edit(Request $request, Depense $depense): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $form = $this->createForm(DepenseType::class, $depense);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->entityManager->flush();

                return $this->redirectToRoute('depense_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('depense/edit.html.twig', [
                'depense' => $depense,
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

    #[Route("/{id}", name :"depense_delete", methods : ["POST"]) ]
    public function delete(Request $request, Depense $depense): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            if ($this->isCsrfTokenValid('delete' . $depense->getId(), $request->request->get('_token'))) {
//                $entityManager = $this->getDoctrine()->getManager();
                $this->entityManager->remove($depense);
                $this->entityManager->flush();
            }

            return $this->redirectToRoute('depense_index', [], Response::HTTP_SEE_OTHER);
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
