<?php

namespace App\Controller;

use App\Entity\Employe;
use App\Entity\HeureSuplementaire;
use App\Entity\Mois;
use App\Entity\Paie;
use App\Entity\Prime;
use App\Entity\Calendrier;
use App\Entity\Accompte;
use App\Entity\PrimePerformance;
use App\Entity\Sanction;
use App\Form\FiltreBulletinType;
use App\Form\PaieType;
use App\Repository\HeureSuplementaireRepository;
use App\Repository\PaieRepository;
use App\Repository\PrimeRepository;
use App\Repository\RetenueRepository;
use App\Service\PaieService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route("/{_locale}/Paie") ]
class PaieController extends AbstractController
{

    private $paieService;
    public function __construct(PaieService $paieService, private Security $security, private EntityManagerInterface $entityManager)
    {
        $this->paieService = $paieService;
    }

    #[Route("/", name :"paie_index") ]
    public function index(): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            $entityManager = $this->entityManager;
            $employes = $entityManager->getRepository(Employe::class)->findBy(['status' => true]);
            $paies = [];
            $startOfMonth = new \DateTime('01-' . date('m') . '-' . date('Y'));
            $endOfMonth = new \DateTime('last day of this month');
            foreach ($employes as $employe) {
                //On verifie si le bulletin est deja enregistrer
                $bulletinExist = $entityManager->getRepository(Paie::class)->findByDate($employe->getId(), $startOfMonth, $endOfMonth);
                if (!$bulletinExist) {
                    //Recuperations des Primes et Heure Supplementaire
                    // $primes = $entityManager->getRepository(Prime::class)->findByDateRange($employe->getId(), $startOfMonth, $endOfMonth);
                    // $heureSup = $entityManager->getRepository(HeureSuplementaire::class)->findByDateRange($employe->getId(), $startOfMonth, $endOfMonth);
                    // $sanctions = $entityManager->getRepository(Sanction::class)->findByDateRange($employe->getId(), $startOfMonth, $endOfMonth);
                    $salaireDeBase = $employe->getPoste()->getSalaire();

                    $paies[] = [
                        'employe' => $employe,
                        'salaireBase' => $salaireDeBase,
                        // 'salaireBrut' => $salaireDeBase,
                        // 'salaireNet' => $salaireDeBase ,
                        // 'prime' => $primes,
                        // 'heureSup' => $heureSup,
                    ];
                }
            }
            if(count($paies) == 0){
                $this->addFlash('notice', 'Tous les bulletins sont déjà validés');
                $response = $this->redirectToRoute('paie_historique_mois_en_cours');
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
         
            $response = $this->render('paie/admin/index.html.twig', [
                'paies' => $paies,
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


    #[Route("/Bulletin", name :"paie_bulletin", methods : ["GET"]) ]
    public function bulletin(): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            $bulletins = $this->paieService->bulletin();

            $nbrjoursmois = new \DateTime();
           
                
          
            $response = $this->render('paie/admin/bulletin.html.twig', [
                'bulletins' => $bulletins,
                'mois' => $this->entityManager ->getRepository(Mois::class)->find(date('m')),
                'nbrjoursmois' => $nbrjoursmois->format('t'),
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


    #[Route("/Imprimer/Bulletin", name :"print_bulletin") ]
    public function printBulletin(PaieRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            $bulletins = $repository->findBy(['mois' => date('m'), 'payer' => false]);
         
            $response = $this->render('paie/admin/bulletin_print.html.twig', [
                'bulletins' => $bulletins,
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

    #[Route("/new", name :"paie_new", methods : ["POST","GET"]) ]
    public function new(Request $request, PrimeRepository $primeRepository, HeureSuplementaireRepository $heureSuplementaireRepository, RetenueRepository $retenueRepository): Response
    {// validation de tous les buletins de salaire
        if ($this->security->isGranted('ROLE_RH')) {
            $entityManager = $this->entityManager;
            $startOfMonth = new \DateTime('01-' . date('m') . '-' . date('Y'));
            $endOfMonth = new \DateTime('last day of this month');
            $mois = $entityManager->getRepository(Mois::class)->find(date('m'));

        $employes = $entityManager->getRepository(Employe::class)->findBy(['status' => true]);
        foreach ($employes as $employe) {
            $paieExistante = $entityManager->getRepository(Paie::class)->findByDate($employe->getId(), $startOfMonth, $endOfMonth);
            if ($paieExistante) {
                continue;
            }
             $conge = $entityManager->getRepository(Calendrier::class)->findoneBy(['employe' => $employe->getId()],['id' =>'DESC']);
            $transport = 0;
            $logement = 0;
            $allocationconge = 0;
            $ponction = 0;
            $totalprime = [];
            $montantprime = 0;

            /** enciennete */
            $now = new \DateTime();
           
            $interval = $now->diff($employe->getHireDate());

            $yearDiff = $interval->y;
            $monthDiff = $interval->m + 1; // +1 comme dans votre code original


            $paie = new Paie();
            $paie->setCategorie($employe->getCategorie());
            $paie->setEchelle($employe->getEchelle());
            $paie->setCnps($employe->getCnps());
            $paie->setBanque($employe->getBanque());
            $paie->setFonction($employe->getPoste()->getNom());
            $paie->setDepartement($employe->getPoste()->getDepartement()->getNom());
            if($conge !== null){
                $paie->setDebutConge($conge->getDateDebut());
                $paie->setFinConge($conge->getDateFin());
            }
            $paie->setAnciennete($yearDiff.' an(s) '.$monthDiff.' mois');
            $anciennete = 0;
            if ($yearDiff >= 2){
                $anciennete = (2 * $yearDiff) / 100;
            }
            $paie->setTauxenciennete($anciennete);
            $paie->setBaseenciennete(round($employe->getPoste()->getSalaire()));
            $prenciennete = $employe->getPoste()->getSalaire() * $anciennete;
            $paie->setCode(0);
            $paie->setCodeanciennete($yearDiff);
            $paie->setSalaireBase($employe->getPoste()->getSalaire());
            $paie->setEmploye($employe);
            $paie->setMois($mois);
            


            $primes = $entityManager->getRepository(Prime::class)->findBy(['employe' => $employe->getId()]);

                        
            
            $nbrjoursmois = new \DateTime();
            $ponction = round($employe->getPoste()->getSalaire() / $nbrjoursmois->format('t'));
            foreach ($primes as $prime) {
                // Vérifie si la description est "indemnité de transport" (en minuscules)
                if (strtolower($prime->getDescription()) === 'indemnité de transport' || strtolower($prime->getDescription()) === 'indemnite de transport') {
                    $transport = $prime->getMontant();
                }
                else if(strtolower($prime->getDescription()) === 'indemnité de logement' || strtolower($prime->getDescription()) === 'indemnite de logement') {
                    $logement = $prime->getMontant();
                }
                else if(strtolower($prime->getDescription()) === 'allocation de congé' || strtolower($prime->getDescription()) === 'allocation de conge') {
                    $allocationconge = $prime->getMontant();
                }

                // Vérifie si la prime est journalière (base == true)
                // if (!empty($prime->getBase()) && $prime->getBase() === true) {
                //     $ponction += $prime->getMontant() / $nbrjoursmois->format('t');
                    
                // }

                $totalprime[] = [ 'designation' => $prime->getDescription(), 
                                    'montant' => $prime->getMontant()
                                ];
                $montantprime += $prime->getMontant();
            }
            $paie->setIndemnite(json_encode($totalprime));


           $heureSups = $heureSuplementaireRepository->findBy(['employe' => $employe->getId(), 'paye' => false]);
            $nombreHeures = 0;
            $montantheureSup = 0;
            foreach ($heureSups as $heureSup) {
                // calcul nombre d'heure
                $nombreHeures = $nombreHeures + $heureSup->getDuree();
                $heureSup->setPaye(true);
                $entityManager->persist($heureSup);
            }
           $employe->getPoste()->getHeureSup() != null ? $paie->setBaseheureSup(round($employe->getPoste()->getHeureSup())) : $paie->setBaseheureSup(round($employe->getPoste()->getSalaire()/173.33)) ;
            $paie->setTauxheureSup($nombreHeures);
            
            $montantheureSup = $employe->getPoste()->getHeureSup() * $nombreHeures;


            $primeperformances = $entityManager->getRepository(PrimePerformance::class)->findBy(['employe' => $employe->getId(), 'paye' => false]);
            $totalPrimePerf = 0;
            foreach ($primeperformances as $primeP) {
                // calcul nombre d'heure
                $totalPrimePerf = $totalPrimePerf + $primeP->getMontant();
                $primeP->setPaye(true);
                $entityManager->persist($primeP);
            }
            $paie->setPerformance($totalPrimePerf);


            $sanctions = $entityManager->getRepository(Sanction::class)->findByDateRange($employe->getId(), $startOfMonth, $endOfMonth);

            $retenues = [];
            $nombreJours = 0;
            // $salaireJournalier = $employe->getPoste()->getSalaire() / 30; // Salaire journalier
            $montantRetenue = 0;
            foreach ($sanctions as $sanction) {
                // calcul nombre jours

                if (strtolower($sanction->getTypeSanction()) === 'ponction salariale') {
                    $nombreJours =  $nombreJours + $sanction->getNombreJours();
//                    $montantRetenue = $montantRetenue + $salaireJournalier * $nombreJours;
                } elseif (strtolower($sanction->getTypeSanction()) === 'mis à pied' || strtolower($sanction->getTypeSanction()) === 'mis a pied') {
                    $dateDebut = $sanction->getDateDebut();
                    $dateFin = $sanction->getDateFin();
                    $nombreJours = $nombreJours + $dateDebut->diff($dateFin)->days + 1;
//                    $montantRetenue = $montantRetenue + $salaireJournalier * $nombreJours;
                }

            }
            $paie->setJours($nbrjoursmois->format('t'));
            
            $paie->setBaseponction($ponction);
            $paie->setTauxponction($nombreJours);
            $paie->setBrut(round($employe->getPoste()->getSalaire() + $prenciennete + $montantprime + $montantheureSup + $totalPrimePerf));
            $paie->setBrutinter(round($employe->getPoste()->getSalaire() + $allocationconge + $prenciennete));

            $accompte= $entityManager->getRepository(Accompte::class)->findOneBy(['employe' => $employe->getId(), 'paye' => false, 'verser' => true], ['id' =>'DESC']);
            
            $totalAccompte= 0;
             // foreach ($accomptes as $accompte) {
            //     // calcul nombre d'heure
            //     $totalAccompte = $totalAccompte + $accompte->getMontant();
            // }
            if(!empty($accompte)){
                $totalAccompte = $accompte->getMontant();
                $accompte->setPaye(true);
                $entityManager->persist($accompte);

            }
            $paie->setAccompte($totalAccompte);
            
            /** logement fisc */
            $logementfisc = 0;
            if ($paie->getBrutinter() * 0.15 <= $logement) {
                $logementfisc = $paie->getBrutinter() * 0.15;
            } else {
                $logementfisc = $logement;
            }
            $paie->setLogementfisc(round($logementfisc));

            /** vehicule fisc */
            if ($paie->getBrutinter() * 0.1 <= $transport) {
                $vehiculefisc = $paie->getBrutinter() * 0.1;
            } else {
                $vehiculefisc = $transport;
            }

            $paie->setVehiculefisc(round($vehiculefisc));

            /** logement cnps */
            if ($paie->getBrutinter() * 0.15 <= $logement) {
                $logementcnps = $logement - ($paie->getBrutinter() * 0.15);
            } elseif ($paie->getBrutinter() * 0.15 >= $logement) {
                $logementcnps = 0;
            }

            $paie->setLogementcnps(round($logementcnps));

            /** vehicule cnps */
            if ($paie->getBrutinter() * 0.1 <= $transport) {
                $vehiculecnps = $transport - ($paie->getBrutinter() * 0.1);
            } elseif ($paie->getBrutinter() * 0.1 > $transport) {
                $vehiculecnps = 0;
            }

            $paie->setVehiculecnps(round($vehiculecnps));

            /** salaire brut taxe brutinter + transport + vehiculefisc */
            $paie->setBruttaxable($paie->getBrutinter() + $paie->getLogementfisc() + $paie->getVehiculefisc());
            $paie->setSalairecotisable($paie->getBrutinter() + $paie->getLogementcnps()  + $paie->getVehiculecnps());

            /** irpp */
            if($paie->getBruttaxable() < 62000) {
                $irpp = 0;
            } elseif ($paie->getBruttaxable() < 310000) {
                $irpp = ($paie->getBruttaxable() * 0.7 - $paie->getBruttaxable() * 0.028 - 41667) * 0.1;
            } elseif ($paie->getBruttaxable() < 429000) {
                $irpp = 16693 + ($paie->getBruttaxable() - 310000) * 0.7 * 0.15;
            } elseif ($paie->getBruttaxable() < 667000) {
                $irpp = 29188 + ($paie->getBruttaxable() - 429000) * 0.7 * 0.25;
            } elseif ($paie->getBruttaxable() > 667001) {
                $irpp = 70830 + ($paie->getBruttaxable() - 667000) * 0.7 * 0.35;
            }
            $irpp = round($irpp);
            $paie->setBaseirpp($paie->getBruttaxable());
            // $paie->setTauxirpp($irpp);
            $paie->setIrpp($irpp); 

            $paie->setBaseca($irpp);
            $paie->setTauxca(10);
            $ca = round($irpp * 0.1);
            $paie->setca($ca);
           
            /** dve local */
            if ($paie->getBruttaxable() < 62000) {
                $com = 0;
            } elseif ($paie->getBruttaxable() < 75001) {
                $com = 250;
            } elseif ($paie->getBruttaxable() < 100001) {
                $com = 500;
            } elseif ($paie->getBruttaxable() < 125001) {
                $com = 750;
            } elseif ($paie->getBruttaxable() < 150001) {
                $com = 1000;
            } elseif ($paie->getBruttaxable() < 200001) {
                $com = 1250;
            } elseif ($paie->getBruttaxable() < 250001) {
                $com = 1500;
            } elseif ($paie->getBruttaxable() < 300001) {
                $com = 2000;
            } elseif ($paie->getBruttaxable() < 500001) {
                $com = 2250;
            } elseif ($paie->getBruttaxable() > 500001) {
                $com = 2500;
            }
            $paie->setBaselocal($paie->getBruttaxable());
            $paie->setTauxlocal($com);
            $paie->setLocal($com);
           
            /** vcnps viel */
            if ($paie->getSalairecotisable() <= 750000) {
                $pv = $paie->getSalairecotisable() * 0.042;
            } else {
                $pv = 750000 * 0.042;
            }
            $pv = round($pv);
            $paie->setBasevieil($paie->getSalairecotisable());
            $paie->setTauxvieil(4.2);
            $paie->setVieil($pv);
            
            /** fonfoncier */
            if ($paie->getBruttaxable() <= 62000) {
                $foncier = 0;
            } else {
                $foncier = $paie->getBruttaxable() * 0.01;
            }
            $foncier = round($foncier);
            $paie->setBasefoncier($paie->getBruttaxable());
            $paie->setTauxfoncier(1);
            $paie->setFoncier($foncier);
            
            /** crtv */
            $CRTV = 0;
            if ($paie->getBruttaxable() <= 52000) {
                $CRTV = 0;
            } elseif ($paie->getBruttaxable() <= 100000) {
                $CRTV = 750;
            } elseif ($paie->getBruttaxable() <= 200000) {
                $CRTV = 1950;
            } elseif ($paie->getBruttaxable() < 300000) {
                $CRTV = 3250;
            } elseif ($paie->getBruttaxable() <= 400000) {
                $CRTV = 4550;
            } elseif ($paie->getBruttaxable() <= 500000) {
                $CRTV = 5850;
            } elseif ($paie->getBruttaxable() <= 600000) {
                $CRTV = 7150;
            } elseif ($paie->getBruttaxable() >= 700000) {
                $CRTV = 8450;
            }

            if ($paie->getBruttaxable() > 700000 && $paie->getBruttaxable() <= 800000) {
                $CRTV += 9750;
            } elseif ($paie->getBruttaxable() > 800000 && $paie->getBruttaxable() <= 900000) {
                $CRTV += 11050;
            } elseif ($paie->getBruttaxable() > 900000 && $paie->getBruttaxable() <= 1000000) {
                $CRTV += 12350;
            } elseif ($paie->getBruttaxable() > 1000000) {
                $CRTV += 13000;
            }
            // $paie->setTauxcrtv();
            $paie->setBasecrtv($paie->getBruttaxable());
            $paie->setCrtv($CRTV);

            /** allocation */
            if ($paie->getCode() == 1) {
                $allocation = 0;
            } else {
                if ($paie->getSalairecotisable() <= 750000) {
                    $allocation = $paie->getSalairecotisable() * 0.07;
                } else {
                    $allocation = 750000 * 0.07;
                }
            }
            $allocation = round($allocation);
            $paie->setAllocation($allocation);
            
            /** cp vieil */
            if ($paie->getCode() == 1) {
                $Vieillesse = 0;
            } else {
                if ($paie->getSalairecotisable() <= 750000) {
                    $Vieillesse = $paie->getSalairecotisable() * 0.042;  // 4.2% du salaire
                } else {
                    $Vieillesse = 750000 * 0.042;          // 4.2% plafonné à 750,000
                }
            }
            $Vieillesse = round($Vieillesse);
            $paie->setCpvieil($Vieillesse);

            /** trav */
            if ($paie->getCode() == 1) {
                $trav = 0;
            } else {
                $trav = $paie->getSalairecotisable() * 0.0175;
            }
            $trav = round($trav);
            $paie->setTav($trav);

            /** cp foncier */
            $credfonc = round($paie->getBrut() * 0.015);
            $paie->setCpfoncier($credfonc);

            /** fne */
            $fne = round($paie->getBrut() * 0.01);
            $paie->setFne($fne);

            $paie->setTotalchargepatronal($allocation + $Vieillesse + $trav + $credfonc + $fne);
            $paie->setTotalChargeEmploye($irpp + $ca + $com + $pv + $foncier + $CRTV + $ponction * $nombreJours);

            // a gere plutard
            $cotisationRetenue =  $CRTV + $foncier + $pv + $com + $ca + $irpp + $ponction * $nombreJours + $totalAccompte;
            $paie->setSalaireNet($paie->getBrut() -  $cotisationRetenue);

            // Enregistrement dans la table paie
            
            $entityManager->persist($paie);
            $entityManager->flush();
           
            }



            $this->addFlash('notice', 'Bulletins validés avec succès');
            // return $this->redirectToRoute('paie_historique');
        $response = $this->redirectToRoute('paie_historique_mois_en_cours');
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

    #[Route("/Historique", name :"paie_historique", methods : ["GET","POST"]) ]
    public function historique(Request $request, PaieRepository $paieRepository): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            $form = $this->createForm(FiltreBulletinType::class);
            $form->handleRequest($request);
            $paie = [];


            if ($form->isSubmitted() && $form->isValid()) {
                $filters = $form->getData();
                $paie = $paieRepository->findByFiltrer(
                    $filters['employe'] ?? null,
                    $filters['mois'] ?? null,
                    $filters['annee'] ?? null
                );
                return $this->render('paie/admin/historique.html.twig', [
                    'form' => $form->createView(),
                    'paie' => $paie,
                ]);
            }
            $paie = $paieRepository->findAll();
          
            $response = $this->render('paie/admin/historique.html.twig', [
                'form' => $form->createView(),
                'paie' => $paie,
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

    #[Route("/HistoriqueMois", name :"paie_historique_mois_en_cours", methods : ["GET"]) ]
    public function historiqueMonthCurent(PaieRepository $paieRepository): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
        $paie = $paieRepository->findPaieCurrentMonth();
           
            $response = $this->render('paie/admin/historique_mois_encours.html.twig', [
                'paie' => $paie,
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


    #[Route("/Historique/Bulletin", name :"paie_historique_bulletin", methods : ["GET"]) ]
    public function historiqueBulletin(PaieRepository $paieRepository): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            $paie = $paieRepository->findAll();
            $detailsRetenues = [];

            foreach ($paie as $singlePaie) {
                // Si 'detailsRetenues' existe et n'est pas vide
                $details = json_decode($singlePaie->getDetailsRetenues(), true);

                // Si le JSON est valide et contient des éléments
                if (is_array($details) && count($details) > 0) {
                    $detailsRetenues[] = $details;
                } else {
                    // Ajouter un tableau vide si aucune retenue
                    $detailsRetenues[] = [];
                }
            }

          
            $response = $this->render('paie/admin/historique_bulletin.html.twig', [
                'paie' => $paie,
                'detailsRetenues' => $detailsRetenues,
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

    #[Route("/Historique/{id}", name :"paie_historique_show", methods : ["GET"]) ]
    public function historiqueShow(Paie $paie): Response
    {
        if ($this->security->isGranted('ROLE_EMPLOYER')) {
              $indemnite = json_decode($paie->getIndemnite(), true);

            // dd($paie->getIndemnite());
          
            $response = $this->render('paie/admin/historique_show.html.twig', [
                'paie' => $paie,
                'indemnite' => $indemnite,
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

    #[Route("/Historique_print/{id}", name :"paie_historique_show_print", methods : ["GET"]) ]
    public function historiqueShow_print(Paie $paie): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
              $indemnite = json_decode($paie->getIndemnite(), true);

            // dd($paie->getIndemnite());
           
            $response = $this->render('paie/admin/historique_show_print.html.twig', [
                'paie' => $paie,
                'indemnite' => $indemnite,
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

    #[Route("Details_print/{id}", name :"paie_show_rint", methods : ["GET"]) ]
    public function show_print(int $id, PrimeRepository $primeRepository, HeureSuplementaireRepository $heureSuplementaireRepository): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            $entityManager = $this->entityManager;
            $startOfMonth = new \DateTime('01-' . date('m') . ('-') . date('Y'));
            $endOfMonth = new \DateTime('last day of this month');
            $employe = $entityManager->getRepository(Employe::class)->find($id);
            $mois = $entityManager->getRepository(Mois::class)->find(date('m'));

            // Vérifier si la paie du mois en cours est déjà validée
            // $paieExistante = $entityManager->getRepository(Paie::class)->findByDate($employe->getId(), $startOfMonth, $endOfMonth);
//            $primes = $entityManager->getRepository(Prime::class)->findByDateRange($employe->getId(), $startOfMonth, $endOfMonth);
//            $heureSup = $entityManager->getRepository(HeureSuplementaire::class)->findByDateRange($employe->getId(), $startOfMonth, $endOfMonth);

            $primes = $entityManager->getRepository(Prime::class)->findBy(['employe' => $employe->getId()]);

            $heureSups = $heureSuplementaireRepository->findByDateRange($employe->getId(), $startOfMonth, $endOfMonth);
            $nombreHeures = 0;
            foreach ($heureSups as $heureSup) {
                // calcul nombre d'heure
                $nombreHeures = $nombreHeures + $heureSup->getDuree();
            }


            $primeperformances = $entityManager->getRepository(PrimePerformance::class)->findByDateRange($employe->getId(), $startOfMonth, $endOfMonth);
            $totalPrimePerf = 0;
            foreach ($primeperformances as $primeP) {
                // calcul nombre d'heure
                $totalPrimePerf = $totalPrimePerf + $primeP->getMontant();
            }


            $sanctions = $entityManager->getRepository(Sanction::class)->findByDateRange($employe->getId(), $startOfMonth, $endOfMonth);

            $retenues = [];
            $nombreJours = 0;
            $salaireJournalier = $employe->getPoste()->getSalaire() / 30; // Salaire journalier
            $montantRetenue = 0;
            foreach ($sanctions as $sanction) {
                // calcul nombre jours

                if (strtolower($sanction->getTypeSanction()) === 'ponction salariale') {
                    $nombreJours =  $nombreJours + $sanction->getNombreJours();
//                    $montantRetenue = $montantRetenue + $salaireJournalier * $nombreJours;
                } elseif (strtolower($sanction->getTypeSanction()) === 'mis à pied') {
                    $dateDebut = $sanction->getDateDebut();
                    $dateFin = $sanction->getDateFin();
                    $nombreJours = $nombreJours + $dateDebut->diff($dateFin)->days + 1;
//                    $montantRetenue = $montantRetenue + $salaireJournalier * $nombreJours;
                }
            }

            $nbrjoursmois = new \DateTime();
           
            $response = $this->render('paie/admin/show_print.html.twig', [
                'employe' => $employe,
                'primes' => $primes,
                'nbrjoursmois' => $nbrjoursmois->format('t'),
                'nombreJours' => $nombreJours,
                'totalPrimePerf' => $totalPrimePerf,
                'heureSups' => $nombreHeures,
                'mois' => $mois,
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

    #[Route("Details/{id}", name :"paie_show", methods : ["GET"]) ]
    public function show(int $id, PrimeRepository $primeRepository, HeureSuplementaireRepository $heureSuplementaireRepository): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            $entityManager = $this->entityManager;
            $startOfMonth = new \DateTime('01-' . date('m') . ('-') . date('Y'));
            $endOfMonth = new \DateTime('last day of this month');
            $employe = $entityManager->getRepository(Employe::class)->find($id);
            $conge = $entityManager->getRepository(Calendrier::class)->findoneBy(['employe' => $employe->getId()],['id' =>'DESC']);
            $mois = $entityManager->getRepository(Mois::class)->find(date('m'));

            $now = new \Datetime();
            $interval = $now->diff($employe->getHireDate());

            $yearDiff = $interval->y;
            $monthDiff = $interval->m + 1; // +1 comme dans votre code original

            // Vérifier si la paie du mois en cours est déjà validée
            // $paieExistante = $entityManager->getRepository(Paie::class)->findByDate($employe->getId(), $startOfMonth, $endOfMonth);
//            $primes = $entityManager->getRepository(Prime::class)->findByDateRange($employe->getId(), $startOfMonth, $endOfMonth);
//            $heureSup = $entityManager->getRepository(HeureSuplementaire::class)->findByDateRange($employe->getId(), $startOfMonth, $endOfMonth);

            $primes = $entityManager->getRepository(Prime::class)->findBy(['employe' => $employe->getId()]);

            $heureSups = $heureSuplementaireRepository->findBy(['employe' => $employe->getId(), 'paye' => false]);
            $nombreHeures = 0;
            foreach ($heureSups as $heureSup) {
                // calcul nombre d'heure
                $nombreHeures = $nombreHeures + $heureSup->getDuree();
            }


            $primeperformances = $entityManager->getRepository(PrimePerformance::class)->findBy(['employe' => $employe->getId(), 'paye' => false]);
            $totalPrimePerf = 0;
            foreach ($primeperformances as $primeP) {
                // calcul nombre d'heure
                $totalPrimePerf = $totalPrimePerf + $primeP->getMontant();
            }

            $accompte= $entityManager->getRepository(Accompte::class)->findOneBy(['employe' => $employe->getId(), 'paye' => false, 'verser' => true], ['id' =>'DESC']);
            
            $totalAccompte= 0;
             // foreach ($accomptes as $accompte) {
            //     // calcul nombre d'heure
            //     $totalAccompte = $totalAccompte + $accompte->getMontant();
            // }
            if(!empty($accompte)){
                $totalAccompte = $accompte->getMontant();

            }
           


            $sanctions = $entityManager->getRepository(Sanction::class)->findByDateRange($employe->getId(), $startOfMonth, $endOfMonth);

            $retenues = [];
            $nombreJours = 0;
            $salaireJournalier = $employe->getPoste()->getSalaire() / 30; // Salaire journalier
            $montantRetenue = 0;
            foreach ($sanctions as $sanction) {
                // calcul nombre jours

                if (strtolower($sanction->getTypeSanction()) === 'ponction salariale') {
                    $nombreJours =  $nombreJours + $sanction->getNombreJours();
//                    $montantRetenue = $montantRetenue + $salaireJournalier * $nombreJours;
                } elseif (strtolower($sanction->getTypeSanction()) === 'mis à pied' || strtolower($sanction->getTypeSanction()) === 'mis a pied') {
                    $dateDebut = $sanction->getDateDebut();
                    $dateFin = $sanction->getDateFin();
                    $nombreJours = $nombreJours + $dateDebut->diff($dateFin)->days + 1;
//                    $montantRetenue = $montantRetenue + $salaireJournalier * $nombreJours;
                }
            }

            $nbrjoursmois = new \DateTime();
           
            $response = $this->render('paie/admin/show.html.twig', [
                'employe' => $employe,
                'primes' => $primes,
                'nbrjoursmois' => $nbrjoursmois->format('t'),
                'nombreJours' => $nombreJours,
                'totalPrimePerf' => $totalPrimePerf,
                'heureSups' => $nombreHeures,
                'mois' => $mois,
                'yearDiff'=>  $yearDiff,
                'monthDiff'=>  $monthDiff,
                'conge'=> $conge,
                'accompte'=> $totalAccompte,
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

    #[Route("/valider/{id}", name :"paie_valider", methods : ["POST","GET"]) ]
    public function valider(int $id, PrimeRepository $primeRepository, HeureSuplementaireRepository $heureSuplementaireRepository): Response
    {// validation d'un seul buletin de salaire
        if ($this->security->isGranted('ROLE_RH')) {
            $entityManager = $this->entityManager;
            $startOfMonth = new \DateTime('01-' . date('m') . ('-') . date('Y'));
            $endOfMonth = new \DateTime('last day of this month');
            $employe = $entityManager->getRepository(Employe::class)->find($id);
             $conge = $entityManager->getRepository(Calendrier::class)->findoneBy(['employe' => $employe->getId()],['id' =>'DESC']);
            $mois = $entityManager->getRepository(Mois::class)->find(date('m'));
            $transport = 0;
            $logement = 0;
            $ponction = 0;
            $totalprime = [];
            $montantprime = 0;
            $allocationconge = 0;

            /** enciennete */
            $now = new \DateTime();
           
            $interval = $now->diff($employe->getHireDate());

            $yearDiff = $interval->y;
            $monthDiff = $interval->m + 1; // +1 comme dans votre code original


            $paie = new Paie();
            $paie->setCategorie($employe->getCategorie());
            $paie->setEchelle($employe->getEchelle());
            $paie->setCnps($employe->getCnps());
            $paie->setBanque($employe->getBanque());
            $paie->setFonction($employe->getPoste()->getNom());
            $paie->setDepartement($employe->getPoste()->getDepartement()->getNom());
            if($conge !== null){
                $paie->setDebutConge($conge->getDateDebut());
                $paie->setFinConge($conge->getDateFin());
            }
            $paie->setAnciennete($yearDiff.' an(s) '.$monthDiff.' mois');
            $anciennete = 0;
            if ($yearDiff >= 2){
                $anciennete = (2 * $yearDiff) / 100;
            }
            $paie->setTauxenciennete($anciennete);
            $paie->setBaseenciennete(round($employe->getPoste()->getSalaire()));
            $prenciennete = $employe->getPoste()->getSalaire() * $anciennete;
            $paie->setCode(0);
            $paie->setCodeanciennete($yearDiff);
            $paie->setSalaireBase($employe->getPoste()->getSalaire());
            $paie->setEmploye($employe);
            $paie->setMois($mois);
            


            $primes = $entityManager->getRepository(Prime::class)->findBy(['employe' => $employe->getId()]);

                        
            
            $nbrjoursmois = new \DateTime();
            $ponction = round($employe->getPoste()->getSalaire() / $nbrjoursmois->format('t'));
            foreach ($primes as $prime) {
                // Vérifie si la description est "indemnité de transport" (en minuscules)
                if (strtolower($prime->getDescription()) === 'indemnité de transport' || strtolower($prime->getDescription()) === 'indemnite de transport') {
                    $transport = $prime->getMontant();
                }
                else if(strtolower($prime->getDescription()) === 'indemnité de logement' || strtolower($prime->getDescription()) === 'indemnite de logement') {
                    $logement = $prime->getMontant();
                }
                else if(strtolower($prime->getDescription()) === 'allocation de congé' || strtolower($prime->getDescription()) === 'allocation de conge') {
                    $allocationconge = $prime->getMontant();
                }

                // Vérifie si la prime est journalière (base == true)
                // if (!empty($prime->getBase()) && $prime->getBase() === true) {
                //     $ponction += $prime->getMontant() / $nbrjoursmois->format('t');
                    
                // }

                $totalprime[] = [ 'designation' => $prime->getDescription(), 
                                    'montant' => $prime->getMontant()
                                ];
                $montantprime += $prime->getMontant();
            }
            $paie->setIndemnite(json_encode($totalprime));


           $heureSups = $heureSuplementaireRepository->findBy(['employe' => $employe->getId(), 'paye' => false]);
            $nombreHeures = 0;
            $montantheureSup = 0;
            foreach ($heureSups as $heureSup) {
                // calcul nombre d'heure
                $nombreHeures = $nombreHeures + $heureSup->getDuree();
                $heureSup->setPaye(true);
                $entityManager->persist($heureSup);
            }
           $employe->getPoste()->getHeureSup() != null ? $paie->setBaseheureSup(round($employe->getPoste()->getHeureSup())) : $paie->setBaseheureSup(round($employe->getPoste()->getSalaire()/173.33)) ;
            $paie->setTauxheureSup($nombreHeures);
            
            $montantheureSup = $employe->getPoste()->getHeureSup() * $nombreHeures;


            $primeperformances = $entityManager->getRepository(PrimePerformance::class)->findBy(['employe' => $employe->getId(), 'paye' => false]);
            $totalPrimePerf = 0;
            foreach ($primeperformances as $primeP) {
                // calcul nombre d'heure
                $totalPrimePerf = $totalPrimePerf + $primeP->getMontant();
                $primeP->setPaye(true);
                $entityManager->persist($primeP);
            }
            $paie->setPerformance($totalPrimePerf);


            $sanctions = $entityManager->getRepository(Sanction::class)->findByDateRange($employe->getId(), $startOfMonth, $endOfMonth);

            $retenues = [];
            $nombreJours = 0;
            // $salaireJournalier = $employe->getPoste()->getSalaire() / 30; // Salaire journalier
            $montantRetenue = 0;
            foreach ($sanctions as $sanction) {
                // calcul nombre jours

                if (strtolower($sanction->getTypeSanction()) === 'ponction salariale') {
                    $nombreJours =  $nombreJours + $sanction->getNombreJours();
//                    $montantRetenue = $montantRetenue + $salaireJournalier * $nombreJours;
                } elseif (strtolower($sanction->getTypeSanction()) === 'mis à pied' || strtolower($sanction->getTypeSanction()) === 'mis a pied') {
                    $dateDebut = $sanction->getDateDebut();
                    $dateFin = $sanction->getDateFin();
                    $nombreJours = $nombreJours + $dateDebut->diff($dateFin)->days + 1;
//                    $montantRetenue = $montantRetenue + $salaireJournalier * $nombreJours;
                }

            }
            $paie->setJours($nbrjoursmois->format('t'));
            
            $paie->setBaseponction($ponction);
            $paie->setTauxponction($nombreJours);
            $paie->setBrut(round($employe->getPoste()->getSalaire() + $prenciennete + $montantprime + $montantheureSup + $totalPrimePerf));
            $paie->setBrutinter(round($employe->getPoste()->getSalaire() + $allocationconge + $prenciennete));

            $accompte= $entityManager->getRepository(Accompte::class)->findOneBy(['employe' => $employe->getId(), 'paye' => false, 'verser' => true], ['id' =>'DESC']);
            
            $totalAccompte= 0;
             // foreach ($accomptes as $accompte) {
            //     // calcul nombre d'heure
            //     $totalAccompte = $totalAccompte + $accompte->getMontant();
            // }
            if(!empty($accompte)){
                $totalAccompte = $accompte->getMontant();
                $accompte->setPaye(true);
                $entityManager->persist($accompte);

            }
            $paie->setAccompte($totalAccompte);
            
            /** logement fisc */
            $logementfisc = 0;
            if ($paie->getBrutinter() * 0.15 <= $logement) {
                $logementfisc = $paie->getBrutinter() * 0.15;
            } else {
                $logementfisc = $logement;
            }
            $paie->setLogementfisc(round($logementfisc));

            /** vehicule fisc */
            if ($paie->getBrutinter() * 0.1 <= $transport) {
                $vehiculefisc = $paie->getBrutinter() * 0.1;
            } else {
                $vehiculefisc = $transport;
            }

            $paie->setVehiculefisc(round($vehiculefisc));

            /** logement cnps */
            if ($paie->getBrutinter() * 0.15 <= $logement) {
                $logementcnps = $logement - ($paie->getBrutinter() * 0.15);
            } elseif ($paie->getBrutinter() * 0.15 >= $logement) {
                $logementcnps = 0;
            }

            $paie->setLogementcnps(round($logementcnps));

            /** vehicule cnps */
            if ($paie->getBrutinter() * 0.1 <= $transport) {
                $vehiculecnps = $transport - ($paie->getBrutinter() * 0.1);
            } elseif ($paie->getBrutinter() * 0.1 > $transport) {
                $vehiculecnps = 0;
            }

            $paie->setVehiculecnps(round($vehiculecnps));

            /** salaire brut taxe brutinter + transport + vehiculefisc */
            $paie->setBruttaxable($paie->getBrutinter() + $paie->getLogementfisc() + $paie->getVehiculefisc());
            $paie->setSalairecotisable($paie->getBrutinter() + $paie->getLogementcnps()  + $paie->getVehiculecnps());

            /** irpp */
            if($paie->getBruttaxable() < 62000) {
                $irpp = 0;
            } elseif ($paie->getBruttaxable() < 310000) {
                $irpp = ($paie->getBruttaxable() * 0.7 - $paie->getBruttaxable() * 0.028 - 41667) * 0.1;
            } elseif ($paie->getBruttaxable() < 429000) {
                $irpp = 16693 + ($paie->getBruttaxable() - 310000) * 0.7 * 0.15;
            } elseif ($paie->getBruttaxable() < 667000) {
                $irpp = 29188 + ($paie->getBruttaxable() - 429000) * 0.7 * 0.25;
            } elseif ($paie->getBruttaxable() > 667001) {
                $irpp = 70830 + ($paie->getBruttaxable() - 667000) * 0.7 * 0.35;
            }
            $irpp = round($irpp);
            $paie->setBaseirpp($paie->getBruttaxable());
            // $paie->setTauxirpp($irpp);
            $paie->setIrpp($irpp); 

            $paie->setBaseca($irpp);
            $paie->setTauxca(10);
            $ca = round($irpp * 0.1);
            $paie->setca($ca);
           
            /** dve local */
            if ($paie->getBruttaxable() < 62000) {
                $com = 0;
            } elseif ($paie->getBruttaxable() < 75001) {
                $com = 250;
            } elseif ($paie->getBruttaxable() < 100001) {
                $com = 500;
            } elseif ($paie->getBruttaxable() < 125001) {
                $com = 750;
            } elseif ($paie->getBruttaxable() < 150001) {
                $com = 1000;
            } elseif ($paie->getBruttaxable() < 200001) {
                $com = 1250;
            } elseif ($paie->getBruttaxable() < 250001) {
                $com = 1500;
            } elseif ($paie->getBruttaxable() < 300001) {
                $com = 2000;
            } elseif ($paie->getBruttaxable() < 500001) {
                $com = 2250;
            } elseif ($paie->getBruttaxable() > 500001) {
                $com = 2500;
            }
            $paie->setBaselocal($paie->getBruttaxable());
            $paie->setTauxlocal($com);
            $paie->setLocal($com);
           
            /** vcnps viel */
            if ($paie->getSalairecotisable() <= 750000) {
                $pv = $paie->getSalairecotisable() * 0.042;
            } else {
                $pv = 750000 * 0.042;
            }
            $pv = round($pv);
            $paie->setBasevieil($paie->getSalairecotisable());
            $paie->setTauxvieil(4.2);
            $paie->setVieil($pv);
            
            /** fonfoncier */
            if ($paie->getBruttaxable() <= 62000) {
                $foncier = 0;
            } else {
                $foncier = $paie->getBruttaxable() * 0.01;
            }
            $foncier = round($foncier);
            $paie->setBasefoncier($paie->getBruttaxable());
            $paie->setTauxfoncier(1);
            $paie->setFoncier($foncier);
            
            /** crtv */
            $CRTV = 0;
            if ($paie->getBruttaxable() <= 52000) {
                $CRTV = 0;
            } elseif ($paie->getBruttaxable() <= 100000) {
                $CRTV = 750;
            } elseif ($paie->getBruttaxable() <= 200000) {
                $CRTV = 1950;
            } elseif ($paie->getBruttaxable() < 300000) {
                $CRTV = 3250;
            } elseif ($paie->getBruttaxable() <= 400000) {
                $CRTV = 4550;
            } elseif ($paie->getBruttaxable() <= 500000) {
                $CRTV = 5850;
            } elseif ($paie->getBruttaxable() <= 600000) {
                $CRTV = 7150;
            } elseif ($paie->getBruttaxable() >= 700000) {
                $CRTV = 8450;
            }

            if ($paie->getBruttaxable() > 700000 && $paie->getBruttaxable() <= 800000) {
                $CRTV += 9750;
            } elseif ($paie->getBruttaxable() > 800000 && $paie->getBruttaxable() <= 900000) {
                $CRTV += 11050;
            } elseif ($paie->getBruttaxable() > 900000 && $paie->getBruttaxable() <= 1000000) {
                $CRTV += 12350;
            } elseif ($paie->getBruttaxable() > 1000000) {
                $CRTV += 13000;
            }
            // $paie->setTauxcrtv();
            $paie->setBasecrtv($paie->getBruttaxable());
            $paie->setCrtv($CRTV);

            /** allocation */
            if ($paie->getCode() == 1) {
                $allocation = 0;
            } else {
                if ($paie->getSalairecotisable() <= 750000) {
                    $allocation = $paie->getSalairecotisable() * 0.07;
                } else {
                    $allocation = 750000 * 0.07;
                }
            }
            $allocation = round($allocation);
            $paie->setAllocation($allocation);
            
            /** cp vieil */
            if ($paie->getCode() == 1) {
                $Vieillesse = 0;
            } else {
                if ($paie->getSalairecotisable() <= 750000) {
                    $Vieillesse = $paie->getSalairecotisable() * 0.042;  // 4.2% du salaire
                } else {
                    $Vieillesse = 750000 * 0.042;          // 4.2% plafonné à 750,000
                }
            }
            $Vieillesse = round($Vieillesse);
            $paie->setCpvieil($Vieillesse);

            /** trav */
            if ($paie->getCode() == 1) {
                $trav = 0;
            } else {
                $trav = $paie->getSalairecotisable() * 0.0175;
            }
            $trav = round($trav);
            $paie->setTav($trav);

            /** cp foncier */
            $credfonc = round($paie->getBrut() * 0.015);
            $paie->setCpfoncier($credfonc);

            /** fne */
            $fne = round($paie->getBrut() * 0.01);
            $paie->setFne($fne);

            $paie->setTotalchargepatronal($allocation + $Vieillesse + $trav + $credfonc + $fne);
            $paie->setTotalChargeEmploye($irpp + $ca + $com + $pv + $foncier + $CRTV + $ponction * $nombreJours);

            // a gere plutard
            $cotisationRetenue =  $CRTV + $foncier + $pv + $com + $ca + $irpp + $ponction * $nombreJours + $totalAccompte;
            $paie->setSalaireNet($paie->getBrut() -  $cotisationRetenue);

            // Enregistrement dans la table paie
            
            $entityManager->persist($paie);
            $entityManager->flush();
            $this->addFlash('notice', 'Bulletin validé avec succès');
           
            $response = $this->redirectToRoute('paie_historique_mois_en_cours');
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


    #[Route("/Paiement/", name :"mes_bulletins", methods : ["GET","POST"]) ]
    public function paiement(Security $security, Request $request, PaieRepository $paieRepository): Response
    {
        if ($this->getUser() !== null) {
            $entityManager = $this->entityManager;
//            $mois = $entityManager->getRepository(Mois::class)->find(date('m'));
            $employe = $security->getUser();

            $form = $this->createForm(FiltreBulletinType::class);
            $form->remove('employe');
            $form->handleRequest($request);
            // $paie = [];

            // if ($form->isSubmitted() && $form->isValid()) {
            //     $filters = $form->getData();
            //     $paie = $paieRepository->findByFiltrer(
            //         $filters['employe'] ?? null,
            //         $filters['mois'] ?? null,
            //         $filters['annee'] ?? null
            //     );
            //     return $this->render('paie/index.html.twig', [
            //         'form' => $form->createView(),
            //         'bulletins' => $paie,
            //     ]);
            // }
            $bulletin = $entityManager->getRepository(Paie::class)->findBy(['employe' => $employe]);

            $response = $this->render("paie/index.html.twig", [
                'bulletins' => $bulletin,
                'form' => $form->createView(),
//                'mois' => $mois,
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

    #[Route("/Paiement/Details/{id}", name :"mes_bulletin_details", methods : ["GET"]) ]
    public function paimentDetails(Paie $paie): Response
    {
        if ($this->security->isGranted('ROLE_RH')) {
            $detailsRetenues = json_decode($paie->getDetailsRetenues(), true); // Si tu as stocké en JSON, décode-le en tableau associatif

           
            $response = $this->render("paie/detail_bulletin.html.twig", [
                'paie' => $paie,
                'detailsRetenues' => $detailsRetenues,
                'mois' => $this->entityManager->getRepository(Mois::class)->find(date('m')),
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
