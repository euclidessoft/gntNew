<?php

namespace App\Controller;

use App\Entity\Calendrier;
use App\Entity\Employe;
use App\Entity\PosteEmploye;
use App\Form\EmployeType;
use App\Repository\DepartementRepository;
use App\Repository\PosteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\SecurityBundle\Security;


#[Route("/{_locale}/Employe") ]
class EmployeController extends AbstractController
{ 
      public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

    #[Route("/", name :"employe_index", methods : ["GET"]) ]
    public function index(EntityManagerInterface $entityManager)
    {
        if ($this->security->isGranted('ROLE_RH')) {
            $employe = $entityManager->getRepository(Employe::class)->findAll();
            return $this->render('employe/index.html.twig', [
                'employes' => $employe,
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


    #[Route("/manage", name :"employe_manage", methods : ["GET"]) ]
    public function manage(EntityManagerInterface $entityManager)
    {
        if ($this->security->isGranted('ROLE_RH')) {
            $employe = $entityManager->getRepository(Employe::class)->findAll();
            return $this->render('employe/manage.html.twig', [
                'employes' => $employe,
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


    #[Route("/new", name :"employe_new", methods : ["GET","POST"]) ]
    public function new(Request $request, UserPasswordHasherInterface $encoder, EntityManagerInterface $entityManager): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {

            $employe = new Employe();
            $form = $this->createForm(EmployeType::class, $employe);
            $form->remove('password');

            if ($this->security->isGranted('ROLE_ADMIN'))
                    $form->remove('niveau2');
            else  $form->remove('fonction');

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
//                $entityManager = $this->getDoctrine()->getManager();

                $poste = $employe->getPoste();
                if ($poste->getType() == true) {
                    //on cherche si le poste est deja attribue
                    $userposte = $entityManager->getRepository(PosteEmploye::class)->findOneBy(['poste' => $poste, 'datefin' => null]);
                    if ($userposte) {
                        $this->addFlash('notice', 'Ce poste est unique et est déjà attribué à un employé.');
                        return $this->redirectToRoute('employe_new');
                    }
                }
                $posteEmploye = new PosteEmploye();
                $hashpass = $encoder->hashPassword($employe, 'GNTPharma');
                $employe->setPassword($hashpass);
                
                if ($this->security->isGranted('ROLE_ADMIN'))
                    $fonction = $employe->getFonction();
                else  $fonction = $employe->getNiveau2();

                $employe->setUsername($employe->getNom());
                switch ($fonction) {
                    
                    case 'ADMINISTRATEUR': {
                         if ($this->security->isGranted('ROLE_ADMIN'))
                            $employe->setRoles(['ROLE_ADMIN']);
                         else  $employe->setRoles(['ROLE_EMPLOYER']);
                            break;
                        }  
                    case 'SUPERVISEUR': {
                         if ($this->security->isGranted('ROLE_ADMIN')) 
                            $employe->setRoles(['ROLE_SUPERVISEUR']);
                        else  $employe->setRoles(['ROLE_EMPLOYER']);
                            break;
                        }
                    case 'CAISSIERE': {
                            $employe->setRoles(['ROLE_CAISSIER']);
                            break;
                        }
                    case 'FINANCE': {
                            $employe->setRoles(['ROLE_FINANCE']);
                            break;
                        }
                    case 'RH': {
                            $employe->setRoles(['ROLE_RH']);
                            break;
                        }
                    case 'EMPLOYE': {
                            $employe->setRoles(['ROLE_EMPLOYER']);
                            break;
                        }
                    case 'STOCK': {
                            $employe->setRoles(['ROLE_STOCK']);
                            break;
                        }
                    case 'LIVREUR': {
                            $employe->setRoles(['ROLE_LIVREUR']);
                            $employe->setLivreur(true);
                            break;
                        }
                }
                $employe->setStatus(false);
                $employe->setHireDate($employe->getHireDate());
                $employe->setNombreJoursConges(30);

                $posteEmploye->setDatedebut(new \DateTime());
                $posteEmploye->setDatefin(null);
                $posteEmploye->setPoste($employe->getPoste());
                $posteEmploye->setEmploye($employe);

                //Calcul de la date debut de conges
                $nbreJoursConges = $employe->getNombreJoursConges();
                $dateDebutConges = (clone $employe->getHireDate())->modify('+11 months');
                $dateFinConges = (clone $dateDebutConges)->modify('+' . $nbreJoursConges . ' days');
                $calendrier = new Calendrier();
                $calendrier->setEmploye($employe);
                $calendrier->setDateDebut($dateDebutConges);
                $calendrier->setDateFin($dateFinConges);
                //            dd($calendrier,$dateDebutConges,$dateFinConges,$nbreJoursConges);


                $entityManager->persist($posteEmploye);
                $entityManager->persist($employe);
                $entityManager->persist($calendrier);
                $entityManager->flush();

                $this->addFlash('notice', 'Employé créé avec succès');
                return $this->redirectToRoute("employe_index");
            }
            return $this->render('employe/new.html.twig', [
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

    #[Route("/{id}/edit", name :"employe_edit", methods : ["POST","GET"]) ]
    public function edit(Request $request, Employe $employe, EntityManagerInterface $entityManager): Response
    {
        if ($this->security->isGranted('ROLE_SUPERVISEUR')) {
            $form = $this->createForm(EmployeType::class, $employe);
            $form->remove('password');

            if (!$this->security->isGranted('ROLE_ADMIN')) {
                 $form->remove('fonction');
                 $form->remove('niveau2');
                 $form->remove('poste');
                 $form->remove('hiredate');
             }else  $form->remove('niveau2');

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                 if ($this->security->isGranted('ROLE_ADMIN'))
                    $fonction = $employe->getFonction();
                else  $fonction = $employe->getNiveau2();

                $employe->setUsername($employe->getNom());
                switch ($fonction) {
                    
                    case 'ADMINISTRATEUR': {
                         if ($this->security->isGranted('ROLE_ADMIN'))
                            $employe->setRoles(['ROLE_ADMIN']);
                         else  $employe->setRoles(['ROLE_EMPLOYER']);
                            break;
                        }  
                    case 'SUPERVISEUR': {
                         if ($this->security->isGranted('ROLE_ADMIN')) 
                            $employe->setRoles(['ROLE_SUPERVISEUR']);
                        else  $employe->setRoles(['ROLE_EMPLOYER']);
                            break;
                        }
                    case 'FINANCE': {
                            $employe->setRoles(['ROLE_FINANCE']);
                            break;
                        }
                    case 'RH': {
                            $employe->setRoles(['ROLE_RH']);
                            break;
                        }
                    case 'EMPLOYE': {
                            $employe->setRoles(['ROLE_EMPLOYER']);
                            break;
                        }
                    case 'STOCK': {
                            $employe->setRoles(['ROLE_STOCK']);
                            break;
                        }
                    case 'LIVREUR': {
                            $employe->setRoles(['ROLE_LIVREUR']);
                            $employe->setLivreur(true);
                            break;
                        }
                }
                $entityManager->flush();
                $this->addFlash('notice', 'Employé modifié avec succès');
                return $this->redirectToRoute('employe_index', [], Response::HTTP_SEE_OTHER);
            }
            return $this->render('employe/edit.html.twig', [
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



    #[Route("/{id}/toggle-status", name :"employe_toggle_status", methods : ["POST"]) ]
    public function toggleStatus(Request $request, Employe $employe, EntityManagerInterface $entityManager): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            //verification du token csrf
            if (!$this->isCsrfTokenValid('toggle' . $employe->getId(), $request->request->get('_token'))) {
                $this->addFlash('notice', 'Token CSRF invalide');
                return $this->redirectToRoute('employe_index');
            }

            if ($employe->getStatus()) {
                $employe->setStatus(false);
                $employe->setEnabled(false);
                $this->addFlash('notice', 'Utilisateur désativé');
            } else {
                $employe->setStatus(true);
                $employe->setEnabled(true);
                $this->addFlash('notice', 'Utilisateur activé');
            }

            $entityManager->persist($employe);
            $entityManager->flush();
            return $this->redirectToRoute('employe_index');
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

    #[Route("/config", name :"employe_congif", methods : ["GET"]) ]
    public function config()
    {
        if ($this->security->isGranted('ROLE_RH')) {
            return $this->render('employe/config.html.twig');
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

    #[Route("/Show/{id}", name :"employe_show", methods : ["GET"]) ]
    public function show(Employe $employe): Response
    {
        return $this->render('employe/show.html.twig', [
            'employe' => $employe,
        ]);
    }
    
}
