<?php

namespace App\Controller;

use App\Entity\CritereEvaluation;
use App\Entity\Employe;
use App\Entity\Evaluation;
use App\Entity\EvaluationDetail;
use App\Form\EvaluationType;
use App\Repository\EvaluationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Component\Security\Core\Security;
use Symfony\Bundle\SecurityBundle\Security;

#[Route("/{_locale}/Evaluation") ]
class EvaluationController extends AbstractController
{ 
      public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

    #[Route("/", name :"evaluation_index", methods : ["GET"]) ]
    public function index(EvaluationRepository $evaluationRepository): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            return $this->render('evaluation/index.html.twig', [
                'evaluations' => $evaluationRepository->findAll(),
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

    #[Route("/new", name :"evaluation_new", methods : ["GET","POST"]) ]
    public function new(Request $request): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            $criteres = $this->getDoctrine()->getRepository(CritereEvaluation::class)->findAll();
            $employes = $this->getDoctrine()->getRepository(Employe::class)->findAll();

            $evaluation = new Evaluation();
            $form = $this->createForm(EvaluationType::class, $evaluation);
            $form->handleRequest($request);
            if ($request->isMethod('POST')) {
                $entityManager = $this->getDoctrine()->getManager();
                $employeSelect = $request->request->get('employe');
                $dateEvaluation = new \DateTime($request->request->get('dateEvaluation'));
                $notes = $request->request->get('notes');
                $commentaires = $request->request->get('commentaires');
                $employe = $entityManager->getRepository(Employe::class)->find($employeSelect);

                $evaluation->setDateEvaluation($dateEvaluation);
                $evaluation->setEmploye($employe);
                $totalNotes = 0;
                $nbNotes = 0;

                foreach ($notes as $critereId => $note) {
                    $critere = $entityManager->getRepository(CritereEvaluation::class)->find($critereId);
                    $evaluationDetail = new EvaluationDetail();
                    $evaluationDetail->setCritereEvaluation($critere);
                    $evaluationDetail->setNote((int)$note);
                    $evaluationDetail->setEvaluation($evaluation);
                    if (isset($commentaires[$critereId])) {
                        $evaluationDetail->setCommentaire($commentaires[$critereId]);
                    }
                    $evaluation->addEvaluationDetail($evaluationDetail);
                    $entityManager->persist($evaluationDetail);

                    $totalNotes += (int)$note;
                    $nbNotes++;
                }

                $moyenne = ($nbNotes > 0) ? ($totalNotes / $nbNotes) : 0;
                $evaluation->setMoyenne($moyenne);
                $evaluation->addEvaluationDetail($evaluationDetail);
                $entityManager->persist($evaluation);
                $entityManager->flush();

                return $this->redirectToRoute('evaluation_index', [], Response::HTTP_SEE_OTHER);
            }
            return $this->render('evaluation/evaluation.html.twig', [
                'employes' => $employes,
                'form' => $form->createView(),
                'criteres' => $criteres,
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

    #[Route("/Suivi", name :"evaluation_suivi", methods : ["GET"]) ]
    public function suivi(Security $security): Response
    {
        if ($this->security->isGranted('ROLE_EMPLOYER')) {
            $employe = $security->getUser();
            $evaluations = $this->getDoctrine()->getRepository(Evaluation::class)->findBy(['employe' => $employe]);
            return $this->render('evaluation/suivi.html.twig', [
                'evaluations' => $evaluations,
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

    #[Route("/{id}", name :"evaluation_show", methods : ["GET"]) ]
    public function show(Evaluation $evaluation): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            return $this->render('evaluation/show.html.twig', [
                'evaluation' => $evaluation,
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

    #[Route("/Suivi/{id}", name :"evaluation_suivi_show", methods : ["GET"]) ]
    public function suiviShow(Evaluation $evaluation): Response
    {
        if ($this->security->isGranted('ROLE_EMPLOYER')) {
            return $this->render('evaluation/suivishow.html.twig', [
                'evaluation' => $evaluation,
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

    #[Route("/{id}/edit", name :"evaluation_edit", methods : ["GET","POST"]) ]
    public function edit(Request $request, Evaluation $evaluation): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            $form = $this->createForm(EvaluationType::class, $evaluation);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute('evaluation_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('evaluation/edit.html.twig', [
                'evaluation' => $evaluation,
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

    #[Route("/{id}", name :"evaluation_delete", methods : ["POST"]) ]
    public function delete(Request $request, Evaluation $evaluation): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            if ($this->isCsrfTokenValid('delete' . $evaluation->getId(), $request->request->get('_token'))) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($evaluation);
                $entityManager->flush();
            }

            return $this->redirectToRoute('evaluation_index', [], Response::HTTP_SEE_OTHER);
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
