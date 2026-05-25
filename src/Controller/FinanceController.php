<?php

namespace App\Controller;

use App\Complement\Amortissement;
use App\Complement\Solde;
use App\Complement\Avantage as Gain;
use App\Entity\Banque;
use App\Entity\Reserve;
use App\Entity\Repport;
use App\Entity\Commande;
use App\Entity\Avantage;
use App\Entity\AveCom;
use App\Entity\Achat;
use App\Entity\CommandeProduit;
use App\Entity\Approvisionnement;
use App\Entity\Facture;
use App\Entity\Stock;
use App\Entity\EtatStock;
use App\Entity\Accompte;
use App\Entity\Avoir;
use App\Entity\Categorie;
use App\Entity\Client;
use App\Entity\Debit;
use App\Entity\Depense;
use App\Entity\Ecriture;
use App\Entity\Financement;
use App\Entity\Fournisseur;
use App\Entity\Paie;
use App\Entity\Panier;
use App\Entity\EcritureRepartition;
use App\Entity\PaieSalaire;
use App\Entity\LivrerReste;
use App\Repository\AvoirRepository;
use App\Repository\EcritureRepository;
use App\Repository\PaieRepository;
use App\Repository\PaiementRepository;
use App\Repository\VersementRepository;
use App\Repository\AvantageRepository;
use App\Repository\AveComRepository;
use App\Repository\AccompteRepository;
use App\Repository\CommandeRepository;
use App\Repository\CommandeProduitRepository;
use App\Repository\ApprovisionnementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;
use App\Service\PdfService;

#[Route("/{_locale}/finance", name :"finance_") ]
class FinanceController extends AbstractController
{ 
      public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

    #[Route("/", name :"index") ]
    public function index(EcritureRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $ecritures = $repository->findAll();
            $journal = $repository->journalAnnuelle(date('Y'));
            $caisse = 0;
            $banque = 0;
            $debitbanque = 0;
            $debitcaisse = 0;
            foreach ($ecritures as $ecriture) {
                if($ecriture->getType() != null){
                    $credit = null;
                    $debit = null;
                    if ($ecriture->getCredit() != null) {
                        $credit = $ecriture->getCredit();
                        $credit->getType() == 'Espece' ?
                            $caisse = $caisse + $credit->getMontant() :
                            $banque = $banque + $credit->getMontant();
                    } else {

                        $debit = $ecriture->getDebit();
                        $debit->getType() == 'Espece' ?
                            $debitcaisse = $debitcaisse + $debit->getMontant() :
                            $debitbanque = $debitbanque + $debit->getMontant();

                    }
                }

            }

            return $this->render('finance/index.html.twig', [
                'caisse' => $caisse - $debitcaisse,
                'banque' => $banque - $debitbanque,
                'ecritures' => $journal,
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
    
    #[Route("/SoldeCaisse", name :"solde") ]
    public function solde(EcritureRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {
            $ecritures = $repository->findAll();
            $journal = $repository->journalAnnuelle(date('Y'));
            $caisse = 0;
            $banque = 0;
            $debitbanque = 0;
            $debitcaisse = 0;
            foreach ($ecritures as $ecriture) {
                if($ecriture->getType() != null){
                    $credit = null;
                    $debit = null;
                    if ($ecriture->getCredit() != null) {
                        $credit = $ecriture->getCredit();
                        $credit->getType() == 'Espece' ?
                            $caisse = $caisse + $credit->getMontant() :
                            $banque = $banque + $credit->getMontant();
                    } else {

                        $debit = $ecriture->getDebit();
                        $debit->getType() == 'Espece' ?
                            $debitcaisse = $debitcaisse + $debit->getMontant() :
                            $debitbanque = $debitbanque + $debit->getMontant();

                    }
                }

            }

            return $this->render('finance/solde.html.twig', [
                'caisse' => $caisse - $debitcaisse,
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
    #[Route("/Avantage", name :"avantage") ]
    public function avantage(Request $request, Gain $gain): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
           
            if ($request->isMethod('POST')) {
                // $date = new \DateTime($request->request->get('date1'));
                // $datenow = new \Datetime();
                // date_add($date,date_interval_create_from_date_string("120 days"));
                // // dd($date);
                // if($date > $datenow){
                $commandes = $this->entityManager->getRepository(Commande::class)->avantage( $request->request->get('date1'), $request->request->get('date2'));
            //    dd($commandes);
                $avecom = new AveCom($this->getUser(),new \Datetime($request->request->get('date1')), new \Datetime($request->request->get('date2')));
               $this->entityManager->persist($avecom);

                foreach($commandes as $commande){

                $avoirs = $this->entityManager->getRepository(Avoir::class)->avantage($commande[0]->getUser()->getId(), $request->request->get('date1'), $request->request->get('date2'));
                $montantavoir = 0;
                foreach($avoirs as $avoir){
                    $montantavoir += $avoir->getMontant();
                }  
                $avantage = new Avantage();
                    $avantage->setEmploye($this->getUser());
                    $avantage->setCLient($commande[0]->getUser());
                    $avantage->setAveCom($avecom);
                    $avantage->setCa($commande['ca'] - $montantavoir);
                    $avantage->setTva($request->request->get('tva'));
                    $avantage->setAchat($commande['achat']);
                    $avantage->setCommission($gain->commission($commande['ca'], $request->request->get('com1'),$request->request->get('com2'),$request->request->get('com3'),$request->request->get('com4') ));
                    $avantage->setEscompte($gain->escompte($commande['achat'], $request->request->get('esc1'),$request->request->get('esc2'),$request->request->get('esc3'),$request->request->get('esc4')));
                    $avantage->setRistourne($gain->ristourne($commande['ca'], $request->request->get('rest1'),$request->request->get('rest2'),$request->request->get('rest3')));
                    
                    $this->entityManager->persist($avantage);
                
                }
                $this->entityManager->flush();
                return $this->redirectToRoute("finance_avantage_show",['id' => $avecom->getId()]);
                // }else{
                //     $this->addFlash('notice', "les dates sont trop anciennes");
                // }
            }
 
            $response = $this->render('finance/avantage.html.twig', []);
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

    #[Route("/AvantageClient", name :"avantage_client") ]
    public function avantageclient(Request $request, AvantageRepository $repo): Response
    {
        if ($this->security->isGranted('ROLE_CLIENT_ADMIN')) {
           $panier = $this->entityManager->getRepository(Panier::class)->findBy(['client' => $this->getUser()->getId()]);
           
 
            $response = $this->render('officine/avantage.html.twig', [
                "avantages" => $repo->findBy(['client' => $this->getUser()] ),
                "panier" => $panier,
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

    

    #[Route("/AvantageClient_pdf", name :"avantage_client_pdf") ]
    public function avantageclientpdf(Request $request, AvantageRepository $repo, PdfService $pdfService): Response
    {
        if ($this->security->isGranted('ROLE_CLIENT_ADMIN')) {
           
         
         return $pdfService->streamPdf(
           'officine/avantagepdf.html.twig', [
                "avantages" => $repo->findBy(['client' => $this->getUser()] ),
            ],
            sprintf('avantage-%s.pdf', 1)
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
    
    #[Route("/Palmares_AvantageClient/{client}", name :"avantage_client_palmares") ]
    public function palamaresavantageclient(Client $client, AvantageRepository $repo): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
         
            $response = $this->render('vente/avantage.html.twig', [
                "avantages" => $repo->findBy(['client' => $client] ),
                "user" => $client,
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


    #[Route("/Periode/Avantage", name :"avantage_index") ]
    public function avantageindex(Request $request, AveComRepository $repo): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
           
                       $response = $this->render('finance/avantage_index.html.twig', [
                        'periodes' => $repo->findAll(),
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

    #[Route("/JournalBanque/{banque}", name :"journal_banque") ]
    public function journalbanque(EcritureRepository $repository, Banque $banque, Solde $solde): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $ecritures = $repository->findAll();

            $caisse = 0;
            $bank = 0;
            $debitbanque = 0;
            $debitcaisse = 0;
            $ecrit = [];
            foreach ($ecritures as $ecriture) {
                if ($banque->getCompte() == $ecriture->getComptecredit() || $banque->getCompte() == $ecriture->getComptedebit()) {
                    $credit = null;
                    $debit = null;
                    if ($ecriture->getCredit() != null) {
                        $credit = $ecriture->getCredit();

                        $bank = $bank + $credit->getMontant();
                    } else {

                        $debit = $ecriture->getDebit();

                        $debitbanque = $debitbanque + $debit->getMontant();

                    }
                    $ecrit[] = $ecriture;
                }

            }

            return $this->render('banque/journal_banque.html.twig', [
                'soldecaisse' => $caisse - $debitcaisse,
                'soldebanque' => $bank - $debitbanque,
                'banque' => $banque,
                'ecritures' => $ecrit,
                'solde' => $solde->montantbanque($this->entityManager, $banque->getCompte()),
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
    


    #[Route("/PeriodeShow/{id}", name :"avantage_show") ]
    public function avantageshow(Request $request,AveCom $id, AvantageRepository $repo): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
           
                       $response = $this->render('finance/avantage_show.html.twig', [
                        'periodes' => $repo->findBy(['AveCom' => $id]),
                        'avecom' => $id,
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

    
    #[Route("/PeriodeShow_pdf/{id}", name :"avantage_show_pdf") ]
    public function avantageshowpdf(Request $request,AveCom $id, AvantageRepository $repo, PdfService $pdfService): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
        
         return $pdfService->streamPdf(
            'finance/avantage_showpdf.html.twig', [
                        'periodes' => $repo->findBy(['AveCom' => $id]),
                        'avecom' => $id,
                       ],
            sprintf('avantage-%s.pdf', 1)
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

    #[Route("/JournalCompte/{compte}", name :"journal_compte") ]
    public function journalCompte(EcritureRepository $repository, $compte): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $ecritures = $repository->findAll();

            $caisse = 0;
            $bank = 0;
            $debitbanque = 0;
            $debitcaisse = 0;
            $ecrit = [];
            foreach ($ecritures as $ecriture) {
                if ($compte == $ecriture->getComptecredit() || $compte == $ecriture->getComptedebit()) {
                    $credit = null;
                    $debit = null;
                    if ($ecriture->getType() == null) {
                        
                        $debitbanque += $ecriture->getMontant();
                        $bank = $bank + $ecriture->getMontant();
                    } else if ($ecriture->getCredit() != null) {
                        $credit = $ecriture->getCredit();

                        $bank = $bank + $credit->getMontant();
                    } else {

                        $debit = $ecriture->getDebit();

                        $debitbanque = $debitbanque + $debit->getMontant();

                    }
                    $ecrit[] = $ecriture;
                }

            }

            return $this->render('finance/journal_compte.html.twig', [
                'caisse' => $caisse - $debitcaisse,
                'banque' => $bank - $debitbanque,
                'ecritures' => $ecrit,
                'compte' => $compte,
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

    #[Route("/Palmares_compte/{client}", name :"journal_client") ]
    public function journalCompteclient(Client $client, PaiementRepository $paierepo, VersementRepository $versementrepo): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

              $commandes = $this->entityManager->getRepository(Commande::class)->historiquecompteclient($client->getId());
              $avoirs = $this->entityManager->getRepository(Avoir::class)->findBy(['client' => $client->getId()]);
             $paiements = $paierepo->balancecompte($client->getId());
                $versements = $versementrepo->balancecompte($client->getId());
               
                $result = [];
                foreach ([$commandes, $avoirs, $paiements, $versements] as $tableau) {
                    foreach ($tableau as $row) {
                        $date = $row->getDate()->format('Y-m-d');
                        // dd($date);
                        // On regroupe les lignes par date
                        $result[$date][] = $row;
                    }
                }
                ksort($result);
                // dd($result);
                $flat = [];

                foreach ($result as $date => $rows) {
                    foreach ($rows as $row) {
                        $flat[] = $row;
                    }
                } 

            return $this->render('vente/compte.html.twig', [
               'commandes' => $flat,
                'user' => $client,
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

    

    #[Route("/Analyse_compte/{fournisseur}", name :"journal_fournisseur") ]
    public function journalComptefournisseur(Fournisseur $fournisseur): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

              $factures = $this->entityManager->getRepository(Facture::class)->findBy(['fournisseur' => $fournisseur]);
              $achats = $this->entityManager->getRepository(Achat::class)->findBy(['fournisseur' => $fournisseur]);
            
             
               
                $result = [];
                foreach ([$factures, $achats] as $tableau) {
                    foreach ($tableau as $row) {
                        $date = $row->getDate()->format('Y-m-d');
                        // dd($date);
                        // On regroupe les lignes par date
                        $result[$date][] = $row;
                    }
                }
                ksort($result);
                // dd($result);
                $flat = [];

                foreach ($result as $date => $rows) {
                    foreach ($rows as $row) {
                        $flat[] = $row;
                    }
                } 

            return $this->render('vente/comptefournisseur.html.twig', [
               'commandes' => $flat,
                'fournisseur' => $fournisseur,
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

    #[Route("/Brouillard", name :"brouyard") ]
    public function brouyard(EcritureRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $ecritures = $repository->brouyard();
            $ouverture = $repository->ouverture();
            $caisse = 0;
            $caisseouv = 0;
            $banque = 0;
            $banqueouv = 0;
            $debitbanque = 0;
            $debitbanqueouv = 0;
            $debitcaisse = 0;
            $debitcaisseouv = 0;
            foreach ($ecritures as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $credit->getType() == 'Espece' ?
                        $caisse = $caisse + $credit->getMontant() :
                        $banque = $banque + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debit->getType() == 'Espece' ?
                        $debitcaisse = $debitcaisse + $debit->getMontant() :
                        $debitbanque = $debitbanque + $debit->getMontant();

                }

            }

            foreach ($ouverture as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $credit->getType() == 'Espece' ?
                        $caisseouv = $caisseouv + $credit->getMontant() :
                        $banqueouv = $banqueouv + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debit->getType() == 'Espece' ?
                        $debitcaisseouv = $debitcaisseouv + $debit->getMontant() :
                        $debitbanqueouv = $debitbanqueouv + $debit->getMontant();

                }

            }

            return $this->render('finance/brouyard.html.twig', [
                'caisse' => $caisse - $debitcaisse + $caisseouv - $debitcaisseouv,
                'banque' => $banque - $debitbanque + $banqueouv - $debitbanqueouv,
                'ecritures' => $ecritures,
                'ouverture' => ($caisseouv - $debitcaisseouv) + ($banqueouv - $debitbanqueouv)
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

    #[Route("/Brouillard_print", name :"brouyard_print") ]
    public function brouyard_print(EcritureRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $ecritures = $repository->brouyard();
            $ouverture = $repository->ouverture();
            $caisse = 0;
            $caisseouv = 0;
            $banque = 0;
            $banqueouv = 0;
            $debitbanque = 0;
            $debitbanqueouv = 0;
            $debitcaisse = 0;
            $debitcaisseouv = 0;
            foreach ($ecritures as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $credit->getType() == 'Espece' ?
                        $caisse = $caisse + $credit->getMontant() :
                        $banque = $banque + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debit->getType() == 'Espece' ?
                        $debitcaisse = $debitcaisse + $debit->getMontant() :
                        $debitbanque = $debitbanque + $debit->getMontant();

                }

            }

            foreach ($ouverture as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $credit->getType() == 'Espece' ?
                        $caisseouv = $caisseouv + $credit->getMontant() :
                        $banqueouv = $banqueouv + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debit->getType() == 'Espece' ?
                        $debitcaisseouv = $debitcaisseouv + $debit->getMontant() :
                        $debitbanqueouv = $debitbanqueouv + $debit->getMontant();

                }

            }

            return $this->render('finance/brouyard_print.html.twig', [
                'caisse' => $caisse - $debitcaisse + $caisseouv - $debitcaisseouv,
                'banque' => $banque - $debitbanque + $banqueouv - $debitbanqueouv,
                'ecritures' => $ecritures,
                'ouverture' => ($caisseouv - $debitcaisseouv) + ($banqueouv - $debitbanqueouv)
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

    #[Route("/BrouillardCaisse", name :"brouyard_caisse") ]
    public function brouyardCaisse(EcritureRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {
            $ecritures = $repository->brouyardcaisse();
            $ouverture = $repository->ouverturecaisse();
            $caisse = 0;
            $caisseouv = 0;
            $debitcaisse = 0;
            $debitcaisseouv = 0;
             $credits = [];
                $debits = [];
           

            foreach ($ouverture as $ecriture) {
                $credittest = null;
                $debittest = null;
                if ($ecriture->getCredit() != null) {
                    $credittest = $ecriture->getCredit();
                    $caisseouv = $caisseouv + $credittest->getMontant();
                } else {

                    $debittest = $ecriture->getDebit();
                    $debitcaisseouv = $debitcaisseouv + $debittest->getMontant();

                }

            }
             foreach ($ecritures as $ecriture) {

                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $credits[] = $ecriture;
                    $caisse = $caisse + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debits[] = $ecriture;
                    $debitcaisse = $debitcaisse + $debit->getMontant();

                }

            }

            return $this->render('finance/brouyard_caisse.html.twig', [
                'caisse' => $caisse - $debitcaisse + $caisseouv - $debitcaisseouv,
                 // 'ecritures' => $ecritures,
                'credits' => $credits,
                'debits' => $debits,
                'ouverture' => $caisseouv - $debitcaisseouv,
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

    #[Route("/BrouillardCaisse_print", name :"brouyard_caisse_print") ]
    public function brouyardCaisse_print(EcritureRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {
            $ecritures = $repository->brouyardcaisse();
            $ouverture = $repository->ouverturecaisse();
            $caisse = 0;
            $caisseouv = 0;
            $debitcaisse = 0;
            $debitcaisseouv = 0;
            $credits = [];
                $debits = [];
           

            foreach ($ouverture as $ecriture) {
                $credittest = null;
                $debittest = null;
                if ($ecriture->getCredit() != null) {
                    $credittest = $ecriture->getCredit();
                    $caisseouv = $caisseouv + $credittest->getMontant();
                } else {

                    $debittest = $ecriture->getDebit();
                    $debitcaisseouv = $debitcaisseouv + $debittest->getMontant();

                }

            }
             foreach ($ecritures as $ecriture) {

                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $credits[] = $ecriture;
                    $caisse = $caisse + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debits[] = $ecriture;
                    $debitcaisse = $debitcaisse + $debit->getMontant();

                }

            }

            return $this->render('finance/brouyard_caisse_print.html.twig', [
                'caisse' => $caisse - $debitcaisse + $caisseouv - $debitcaisseouv,
                // 'ecritures' => $ecritures,
                'credits' => $credits,
                'debits' => $debits,
                'ouverture' => $caisseouv - $debitcaisseouv,
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

    #[Route("/BrouillardBanque", name :"brouyard_banque") ]
    public function brouyardBanque(EcritureRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $ecritures = $repository->brouyardbanque();
            $ouverture = $repository->ouverturebanque();
            $banque = 0;
            $banqueouv = 0;
            $debitbanque = 0;
            $debitbanqueouv = 0;
            foreach ($ecritures as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $banque = $banque + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debitbanque = $debitbanque + $debit->getMontant();

                }

            }

            foreach ($ouverture as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();

                    $banqueouv = $banqueouv + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();

                    $debitbanqueouv = $debitbanqueouv + $debit->getMontant();

                }

            }

            return $this->render('finance/brouyard_banque.html.twig', [
                'banque' => $banque - $debitbanque + $banqueouv - $debitbanqueouv,
                'ecritures' => $ecritures,
                'ouverture' => $banqueouv - $debitbanqueouv,
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

    #[Route("/BrouillardBanque_print", name :"brouyard_banque_print") ]
    public function brouyardBanque_print(EcritureRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $ecritures = $repository->brouyardbanque();
            $ouverture = $repository->ouverturebanque();
            $banque = 0;
            $banqueouv = 0;
            $debitbanque = 0;
            $debitbanqueouv = 0;
            foreach ($ecritures as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $banque = $banque + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debitbanque = $debitbanque + $debit->getMontant();

                }

            }

            foreach ($ouverture as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();

                    $banqueouv = $banqueouv + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();

                    $debitbanqueouv = $debitbanqueouv + $debit->getMontant();

                }

            }

            return $this->render('finance/brouyard_banque_print.html.twig', [
                'banque' => $banque - $debitbanque + $banqueouv - $debitbanqueouv,
                'ecritures' => $ecritures,
                'ouverture' => $banqueouv - $debitbanqueouv,
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

    #[Route("/LienDayBrouyard", name :"day_brouyard_lien") ]
    public function liendaybrouyard(Request $request)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $date1 = $request->get('date1');
            $lien = $this->generateUrl('finance_day_brouyard', ['jour' => $date1]);
            $res['ok'] = $lien;
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
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

    #[Route("/DayRepport/", name :"rapport_date") ]
    public function rapportdate()
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {

            return $this->render('finance/rapport_date.html.twig');
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

    #[Route("/DayBrouyard/{jour}", name :"day_brouyard") ]
    public function daybrouyard(Request $request, $jour)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $repository = $this->entityManager->getRepository(Ecriture::class);
            $ecritures = $repository->daybrouyard($jour);
            $ouverture = $repository->ouvertureplace($jour);
            $caisse = 0;
            $caisseouv = 0;
            $banque = 0;
            $banqueouv = 0;
            $debitbanque = 0;
            $debitbanqueouv = 0;
            $debitcaisse = 0;
            $debitcaisseouv = 0;
            foreach ($ecritures as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $credit->getType() == 'Espece' ?
                        $caisse = $caisse + $credit->getMontant() :
                        $banque = $banque + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debit->getType() == 'Espece' ?
                        $debitcaisse = $debitcaisse + $debit->getMontant() :
                        $debitbanque = $debitbanque + $debit->getMontant();

                }

            }

            foreach ($ouverture as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $credit->getType() == 'Espece' ?
                        $caisseouv = $caisseouv + $credit->getMontant() :
                        $banqueouv = $banqueouv + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debit->getType() == 'Espece' ?
                        $debitcaisseouv = $debitcaisseouv + $debit->getMontant() :
                        $debitbanqueouv = $debitbanqueouv + $debit->getMontant();

                }

            }

            return $this->render('finance/brouyard_date.html.twig', [
                'caisse' => $caisse - $debitcaisse + $caisseouv - $debitcaisseouv,
                'banque' => $banque - $debitbanque + $banqueouv - $debitbanqueouv,
                'ecritures' => $ecritures,
                'ouverture' => ($caisseouv - $debitcaisseouv) + ($banqueouv - $debitbanqueouv),
                'day' => $jour,
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

    #[Route("/DayBrouyard_print/{jour}", name :"day_brouyard_print") ]
    public function daybrouyard_print(Request $request, $jour)
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {
            $repository = $this->entityManager->getRepository(Ecriture::class);
            $ecritures = $repository->daybrouyard($jour);
            $ouverture = $repository->ouvertureplace($jour);
            $caisse = 0;
            $caisseouv = 0;
            $banque = 0;
            $banqueouv = 0;
            $debitbanque = 0;
            $debitbanqueouv = 0;
            $debitcaisse = 0;
            $debitcaisseouv = 0;
            foreach ($ecritures as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $credit->getType() == 'Espece' ?
                        $caisse = $caisse + $credit->getMontant() :
                        $banque = $banque + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debit->getType() == 'Espece' ?
                        $debitcaisse = $debitcaisse + $debit->getMontant() :
                        $debitbanque = $debitbanque + $debit->getMontant();

                }

            }

            foreach ($ouverture as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $credit->getType() == 'Espece' ?
                        $caisseouv = $caisseouv + $credit->getMontant() :
                        $banqueouv = $banqueouv + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debit->getType() == 'Espece' ?
                        $debitcaisseouv = $debitcaisseouv + $debit->getMontant() :
                        $debitbanqueouv = $debitbanqueouv + $debit->getMontant();

                }

            }

            return $this->render('finance/brouyard_date_print.html.twig', [
                'caisse' => $caisse - $debitcaisse + $caisseouv - $debitcaisseouv,
                'banque' => $banque - $debitbanque + $banqueouv - $debitbanqueouv,
                'ecritures' => $ecritures,
                'ouverture' => ($caisseouv - $debitcaisseouv) + ($banqueouv - $debitbanqueouv),
                'day' => $jour,
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

    #[Route("/LienDayBrouyardCaisse", name :"day_brouyard_lien_caisse") ]
    public function liendaybrouyardaisse(Request $request)
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {
            $date1 = $request->get('date1');
            $lien = $this->generateUrl('finance_day_brouyard_caisse', ['jour' => $date1]);
            $res['ok'] = $lien;
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
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

    #[Route("/DayBrouyardCaisse/{jour}", name :"day_brouyard_caisse") ]
    public function daybrouyardcaisse(Request $request, $jour)
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {
            $repository = $this->entityManager->getRepository(Ecriture::class);
            $ecritures = $repository->daybrouyardcaisse($jour);
            $ouverture = $repository->ouvertureplacecaisse($jour);
            $caisse = 0;
            $caisseouv = 0;
            $banque = 0;
            $banqueouv = 0;
            $debitbanque = 0;
            $debitbanqueouv = 0;
            $debitcaisse = 0;
            $debitcaisseouv = 0;
                $credits = [];
                $debits = [];
           

            foreach ($ouverture as $ecriture) {
                $credittest = null;
                $debittest = null;
                if ($ecriture->getCredit() != null) {
                    $credittest = $ecriture->getCredit();
                    $caisseouv = $caisseouv + $credittest->getMontant();
                } else {

                    $debittest = $ecriture->getDebit();
                    $debitcaisseouv = $debitcaisseouv + $debittest->getMontant();

                }

            }
             foreach ($ecritures as $ecriture) {

                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $credits[] = $ecriture;
                    $caisse = $caisse + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debits[] = $ecriture;
                    $debitcaisse = $debitcaisse + $debit->getMontant();

                }

            }

            return $this->render('finance/brouyard_date_caisse.html.twig', [
                'caisse' => $caisse - $debitcaisse + $caisseouv - $debitcaisseouv,
                // 'ecritures' => $ecritures,
                'credits' => $credits,
                'debits' => $debits,
                'ouverture' => $caisseouv - $debitcaisseouv,
                'day' => $jour,
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

    #[Route("/DayBrouyardCaisse_print/{jour}", name :"day_brouyard_caisse_print") ]
    public function daybrouyardcaisse_print(Request $request, $jour)
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {
             $repository = $this->entityManager->getRepository(Ecriture::class);
            $ecritures = $repository->daybrouyardcaisse($jour);
            $ouverture = $repository->ouvertureplacecaisse($jour);
            $caisse = 0;
            $caisseouv = 0;
            $banque = 0;
            $banqueouv = 0;
            $debitbanque = 0;
            $debitbanqueouv = 0;
            $debitcaisse = 0;
            $debitcaisseouv = 0;
                $credits = [];
                $debits = [];
           

            foreach ($ouverture as $ecriture) {
                $credittest = null;
                $debittest = null;
                if ($ecriture->getCredit() != null) {
                    $credittest = $ecriture->getCredit();
                    $caisseouv = $caisseouv + $credittest->getMontant();
                } else {

                    $debittest = $ecriture->getDebit();
                    $debitcaisseouv = $debitcaisseouv + $debittest->getMontant();

                }

            }
             foreach ($ecritures as $ecriture) {

                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $credits[] = $ecriture;
                    $caisse = $caisse + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debits[] = $ecriture;
                    $debitcaisse = $debitcaisse + $debit->getMontant();

                }

            }

            return $this->render('finance/brouyard_date_caisse_print.html.twig', [
                'caisse' => $caisse - $debitcaisse + $caisseouv - $debitcaisseouv,
                // 'ecritures' => $ecritures,
                'credits' => $credits,
                'debits' => $debits,
                'ouverture' => $caisseouv - $debitcaisseouv,
                'day' => $jour,
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

    #[Route("/LienDayBrouyardBanque", name :"day_brouyard_lien_banque") ]
    public function liendaybrouyarbanque(Request $request)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $date1 = $request->get('date1');
            $lien = $this->generateUrl('finance_day_brouyard_banque', ['jour' => $date1]);
            $res['ok'] = $lien;
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
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


    #[Route("/DayBrouyardBanque/{jour}", name :"day_brouyard_banque") ]
    public function daybrouyardbanque(Request $request, $jour)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $repository = $this->entityManager->getRepository(Ecriture::class);
            $ecritures = $repository->daybrouyardbanque($jour);
            $ouverture = $repository->ouvertureplacebanque($jour);
            $caisse = 0;
            $caisseouv = 0;
            $banque = 0;
            $banqueouv = 0;
            $debitbanque = 0;
            $debitbanqueouv = 0;
            $debitcaisse = 0;
            $debitcaisseouv = 0;
            foreach ($ecritures as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $banque = $banque + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debitbanque = $debitbanque + $debit->getMontant();

                }

            }

            foreach ($ouverture as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $banqueouv = $banqueouv + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debitbanqueouv = $debitbanqueouv + $debit->getMontant();

                }

            }

            return $this->render('finance/brouyard_date_banque.html.twig', [
                'banque' => $banque - $debitbanque + $banqueouv - $debitbanqueouv,
                'ecritures' => $ecritures,
                'ouverture' => $banqueouv - $debitbanqueouv,
                'day' => $jour,
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

    #[Route("/DayBrouyardBanque_print/{jour}", name :"day_brouyard_banque_print") ]
    public function daybrouyardbanque_print(Request $request, $jour)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $repository = $this->entityManager->getRepository(Ecriture::class);
            $ecritures = $repository->daybrouyardbanque($jour);
            $ouverture = $repository->ouvertureplacebanque($jour);
            $caisse = 0;
            $caisseouv = 0;
            $banque = 0;
            $banqueouv = 0;
            $debitbanque = 0;
            $debitbanqueouv = 0;
            $debitcaisse = 0;
            $debitcaisseouv = 0;
            foreach ($ecritures as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $banque = $banque + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debitbanque = $debitbanque + $debit->getMontant();

                }

            }

            foreach ($ouverture as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $banqueouv = $banqueouv + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debitbanqueouv = $debitbanqueouv + $debit->getMontant();

                }

            }

            return $this->render('finance/brouyard_date_banque_print.html.twig', [
                'banque' => $banque - $debitbanque + $banqueouv - $debitbanqueouv,
                'ecritures' => $ecritures,
                'ouverture' => $banqueouv - $debitbanqueouv,
                'day' => $jour,
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


    #[Route("/IntervalRepport/", name :"rapport_interval") ]
    public function rapportinterval()
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {

            return $this->render('finance/rapport_interval.html.twig');
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

    #[Route("/TiersRepport/", name :"rapport_tiers") ]
    public function rapporttiers()
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {

            return $this->render('finance/choixtiers.html.twig');
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

    #[Route("/TiersRepport_client/", name :"rapport_tiers_client") ]
    public function rapporttiersclient()
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {

            return $this->render('finance/rapport_tiers_client.html.twig');
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
    #[Route("/TiersRepport_fournisseur/", name :"rapport_tiers_fournisseur") ]
    public function rapporttiersfournisseur()
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {

            return $this->render('finance/rapport_tiers_fournisseur.html.twig');
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

    #[Route("/LienDaysBrouyard", name :"days_brouyard_lien") ]
    public function liendaysbrouyard(Request $request)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $date1 = $request->get('date1');
            $date2 = $request->get('date2');
            $lien = $this->generateUrl('finance_days_brouyard', ['date1' => $date1, 'date2' => $date2]);
            $res['ok'] = $lien;
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
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


    #[Route("/DaysBrouyard/{date1}/{date2}", name :"days_brouyard") ]
    public function daysbrouyard(Request $request, $date1, $date2)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

            $repository = $this->entityManager->getRepository(Ecriture::class);
            $ecritures = $repository->plage($date1, $date2);
            $ouverture = $repository->ouvertureplace($date1);
            $caisse = 0;
            $caisseouv = 0;
            $banque = 0;
            $banqueouv = 0;
            $debitbanque = 0;
            $debitbanqueouv = 0;
            $debitcaisse = 0;
            $debitcaisseouv = 0;
            foreach ($ecritures as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $credit->getType() == 'Espece' ?
                        $caisse = $caisse + $credit->getMontant() :
                        $banque = $banque + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debit->getType() == 'Espece' ?
                        $debitcaisse = $debitcaisse + $debit->getMontant() :
                        $debitbanque = $debitbanque + $debit->getMontant();

                }

            }

            foreach ($ouverture as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $credit->getType() == 'Espece' ?
                        $caisseouv = $caisseouv + $credit->getMontant() :
                        $banqueouv = $banqueouv + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debit->getType() == 'Espece' ?
                        $debitcaisseouv = $debitcaisseouv + $debit->getMontant() :
                        $debitbanqueouv = $debitbanqueouv + $debit->getMontant();

                }

            }

            return $this->render('finance/brouyard_interval.html.twig', [
                'caisse' => $caisse - $debitcaisse + $caisseouv - $debitcaisseouv,
                'banque' => $banque - $debitbanque + $banqueouv - $debitbanqueouv,
                'ecritures' => $ecritures,
                'ouverture' => ($caisseouv - $debitcaisseouv) + ($banqueouv - $debitbanqueouv),
                'day1' => $date1,
                'day2' => $date2,
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

    

    #[Route("/LienDaysTiers", name :"days_tiers_lien") ]
    public function liendaystiersclient(Request $request)// clients
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $date1 = $request->get('date1');
            $date2 = $request->get('date2');
            $lien = $this->generateUrl('finance_days_tiers', ['date1' => $date1, 'date2' => $date2]);
            $res['ok'] = $lien;
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
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

    
    #[Route("/DaysBrouyardTiers/{date1}/{date2}", name :"days_tiers") ]
    public function daystiersclient(Request $request, $date1, $date2, PaiementRepository $paierepo, VersementRepository $versementrepo, AvoirRepository $avoirrepo)
    {// clients
        if ($this->security->isGranted('ROLE_FINANCE')) {

              $commandes = $this->entityManager->getRepository(Commande::class)->avantage( $date1, $date2);
            //   dd($commandes);
              $comm = [];
              foreach($commandes as $commande){
                $montant = 0;
                // dd($commande[0]->getUser()->getVersements());
                $paiements = $paierepo->balance($commande['id'], $date1, $date2);
                $versements = $versementrepo->balance($commande['id'], $date1, $date2);
                $avoirs = $avoirrepo->balance($commande['id'], $date1, $date2);
                 $montantavoir = 0;
                foreach($avoirs as $avoir){
                    $montantavoir += $avoir->getMontant();
                }
                foreach($versements as $versement){
                    $montant += $versement->getMontant();
                }

                foreach($paiements as $paiement){
                    $montant += $paiement->getMontant();
                }
               
                $com['id'] = $commande['id'];
                $com['compte'] = $commande[0]->getUser()->getCompte();
                $com['nom'] = $commande[0]->getUser()->getNom();
                $com['ca'] = $commande['ca'] - $montantavoir;
                $com['achat'] = $montant;
                $comm[] = $com;
           }
                    
            return $this->render('finance/brouyard_tiers.html.twig', [
                'commandes' => $comm,
                'day1' => $date1,
                'day2' => $date2,
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

     #[Route("/LienDaysTiers_Fournisseur", name :"days_tiers_lien_fournisseur") ]
    public function liendaystiersfournisseur(Request $request)//fournisseur
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $date1 = $request->get('date1');
            $date2 = $request->get('date2');
            $lien = $this->generateUrl('finance_days_tiers_fournisseur', ['date1' => $date1, 'date2' => $date2]);
            $res['ok'] = $lien;
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
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


    #[Route("/DaysBrouyardTiers_fournisseur/{date1}/{date2}", name :"days_tiers_fournisseur") ]
    public function daystiersfournisseur(Request $request, $date1, $date2)
    {// fournisseur
        if ($this->security->isGranted('ROLE_FINANCE')) {

              $factures = $this->entityManager->getRepository(Facture::class)->avantage( $date1, $date2);
           
            //   dd($factures);
              $comm = [];
              foreach($factures as $commande){
                $montant = 0;
               
                $achats = $this->entityManager->getRepository(Achat::class)->balance($commande['id'], $date1, $date2);

                foreach($achats as $achat){
                    $montant += $achat->getMontant();
                }
               
                $com['id'] = $commande['id'];
                $com['compte'] = $commande[0]->getFournisseur()->getCompte();
                $com['nom'] = $commande[0]->getFournisseur()->getDesignation();
                $com['ca'] = $commande['ca'];
                $com['achat'] = $montant;
                $comm[] = $com;
           }
                    
            return $this->render('finance/brouyard_tiers_fournisseur.html.twig', [
                'commandes' => $comm,
                'day1' => $date1,
                'day2' => $date2,
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

    #[Route("/DaysBrouyardTiers_fournisseur_print/{date1}/{date2}", name :"days_tiers_fournisseur_print") ]
    public function daystiersfournisseurprint(Request $request, $date1, $date2)
    {// fournisseur
        if ($this->security->isGranted('ROLE_FINANCE')) {

              $factures = $this->entityManager->getRepository(Facture::class)->avantage( $date1, $date2);
           
            //   dd($factures);
              $comm = [];
              foreach($factures as $commande){
                $montant = 0;
               
                $achats = $this->entityManager->getRepository(Achat::class)->balance($commande['id'], $date1, $date2);

                foreach($achats as $achat){
                    $montant += $achat->getMontant();
                }
               
                $com['id'] = $commande['id'];
                $com['compte'] = $commande[0]->getFournisseur()->getCompte();
                $com['nom'] = $commande[0]->getFournisseur()->getDesignation();
                $com['ca'] = $commande['ca'];
                $com['achat'] = $montant;
                $comm[] = $com;
           }
                    
            return $this->render('finance/brouyard_tiers_fournisseur_print.html.twig', [
                'commandes' => $comm,
                'day1' => $date1,
                'day2' => $date2,
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
    
    #[Route("/DaysBrouyardTiers_fournisseur_pdf/{date1}/{date2}", name :"days_tiers_fournisseur_pdf") ]
    public function daystiersfournisseurpdf(Request $request, $date1, $date2, PdfService $pdfService)
    {// fournisseur
        if ($this->security->isGranted('ROLE_FINANCE')) {

              $factures = $this->entityManager->getRepository(Facture::class)->avantage( $date1, $date2);
           
            //   dd($factures);
              $comm = [];
              foreach($factures as $commande){
                $montant = 0;
               
                $achats = $this->entityManager->getRepository(Achat::class)->balance($commande['id'], $date1, $date2);

                foreach($achats as $achat){
                    $montant += $achat->getMontant();
                }
               
                $com['id'] = $commande['id'];
                $com['compte'] = $commande[0]->getFournisseur()->getCompte();
                $com['nom'] = $commande[0]->getFournisseur()->getDesignation();
                $com['ca'] = $commande['ca'];
                $com['achat'] = $montant;
                $comm[] = $com;
           }
          
        return $pdfService->streamPdf(
            'finance/brouyard_tiers_fournisseurpdf.html.twig', [
                'commandes' => $comm,
                'day1' => $date1,
                'day2' => $date2,
            ],
            sprintf('balance-%s.pdf', 1)
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


    #[Route("/HistoriqueTiers/{client}/{date1}/{date2}", name :"days_tiers_history") ]
    public function historiqueclient(Request $request, $client, $date1, $date2, PaiementRepository $paierepo, VersementRepository $versementrepo)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
              $cli = $this->entityManager->getRepository(Client::class)->find($client);
              $commandes = $this->entityManager->getRepository(Commande::class)->historiqueclient($cli->getId(), $date1, $date2);
             $paiements = $paierepo->balance($cli->getId(), $date1, $date2);
                $versements = $versementrepo->balance($cli->getId(), $date1, $date2);
               
                $result = [];
                foreach ([$commandes, $paiements, $versements] as $tableau) {
                    foreach ($tableau as $row) {
                        $date = $row->getDate()->format('Y-m-d');
                        // dd($date);
                        // On regroupe les lignes par date
                        $result[$date][] = $row;
                    }
                }
                ksort($result);
                // dd($result);
                $flat = [];

                foreach ($result as $date => $rows) {
                    foreach ($rows as $row) {
                        $flat[] = $row;
                    }
                }  
            return $this->render('finance/brouyard_tiers_history.html.twig', [
                'commandes' => $flat,
                'user' => $cli,
                'day1' => $date1,
                'day2' => $date2,
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

    
    

    #[Route("/HistoriqueTiers_print/{client}/{date1}/{date2}", name :"days_tiers_history_print") ]
    public function historiqueclientprint(Request $request, $client, $date1, $date2, PaiementRepository $paierepo, VersementRepository $versementrepo)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
              $cli = $this->entityManager->getRepository(Client::class)->find($client);
              $commandes = $this->entityManager->getRepository(Commande::class)->historiqueclient($cli->getId(), $date1, $date2);
             $paiements = $paierepo->balance($cli->getId(), $date1, $date2);
                $versements = $versementrepo->balance($cli->getId(), $date1, $date2);
               
                $result = [];
                foreach ([$commandes, $paiements, $versements] as $tableau) {
                    foreach ($tableau as $row) {
                        $date = $row->getDate()->format('Y-m-d');
                        // dd($date);
                        // On regroupe les lignes par date
                        $result[$date][] = $row;
                    }
                }
                ksort($result);
                // dd($result);
                $flat = [];

                foreach ($result as $date => $rows) {
                    foreach ($rows as $row) {
                        $flat[] = $row;
                    }
                }  
            return $this->render('finance/brouyard_tiers_history_print.html.twig', [
                'commandes' => $flat,
                'user' => $cli,
                'day1' => $date1,
                'day2' => $date2,
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



    #[Route("/DaysBrouyardTiers_print/{date1}/{date2}", name :"days_tiers_print") ]
    public function daystiersprint(Request $request, $date1, $date2, PaiementRepository $paierepo, VersementRepository $versementrepo, AvoirRepository $avoirrepo)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

              $commandes = $this->entityManager->getRepository(Commande::class)->avantage( $date1, $date2);
            //   dd($commandes);
              $comm = [];
              foreach($commandes as $commande){
                $montant = 0;
                // dd($commande[0]->getUser()->getVersements());
                $paiements = $paierepo->balance($commande['id'], $date1, $date2);
                $versements = $versementrepo->balance($commande['id'], $date1, $date2);
                $avoirs = $avoirrepo->balance($commande['id'], $date1, $date2);
                 $montantavoir = 0;
                foreach($avoirs as $avoir){
                    $montantavoir += $avoir->getMontant();
                }

                foreach($versements as $versement){
                    $montant += $versement->getMontant();
                }

                foreach($paiements as $paiement){
                    $montant += $paiement->getMontant();
                }
                $com['compte'] = $commande[0]->getUser()->getCompte();
                $com['nom'] = $commande[0]->getUser()->getNom();
                $com['ca'] = $commande['ca'] - $montantavoir;
                $com['achat'] = $montant;
                $comm[] = $com;
           }
                    
            return $this->render('finance/brouyard_tiers_print.html.twig', [
                'commandes' => $comm,
                'day1' => $date1,
                'day2' => $date2,
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

    
    #[Route("/DaysBrouyardTiers_pdf/{date1}/{date2}", name :"days_tiers_pdf") ]
    public function daystierspdf(Request $request, $date1, $date2, PaiementRepository $paierepo, VersementRepository $versementrepo, AvoirRepository $avoirrepo, PdfService $pdfService)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

              $commandes = $this->entityManager->getRepository(Commande::class)->avantage( $date1, $date2);
            //   dd($commandes);
              $comm = [];
              foreach($commandes as $commande){
                $montant = 0;
                // dd($commande[0]->getUser()->getVersements());
                $paiements = $paierepo->balance($commande['id'], $date1, $date2);
                $versements = $versementrepo->balance($commande['id'], $date1, $date2);
                $avoirs = $avoirrepo->balance($commande['id'], $date1, $date2);
                 $montantavoir = 0;
                foreach($avoirs as $avoir){
                    $montantavoir += $avoir->getMontant();
                }

                foreach($versements as $versement){
                    $montant += $versement->getMontant();
                }

                foreach($paiements as $paiement){
                    $montant += $paiement->getMontant();
                }
                $com['compte'] = $commande[0]->getUser()->getCompte();
                $com['nom'] = $commande[0]->getUser()->getNom();
                $com['ca'] = $commande['ca'] - $montantavoir;
                $com['achat'] = $montant;
                $comm[] = $com;
           }
                    
      
        return $pdfService->streamPdf(
           'finance/brouyard_tierspdf.html.twig', [
                'commandes' => $comm,
                'day1' => $date1,
                'day2' => $date2,
            ],
            sprintf('balance-%s.pdf', 1)
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

    #[Route("/DaysBrouyard_print/{date1}/{date2}", name :"days_brouyard_print") ]
    public function daysbrouyard_print(Request $request, $date1, $date2)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

            $repository = $this->entityManager->getRepository(Ecriture::class);
            $ecritures = $repository->plage($date1, $date2);
            $ouverture = $repository->ouvertureplace($date1);
            $caisse = 0;
            $caisseouv = 0;
            $banque = 0;
            $banqueouv = 0;
            $debitbanque = 0;
            $debitbanqueouv = 0;
            $debitcaisse = 0;
            $debitcaisseouv = 0;
            foreach ($ecritures as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $credit->getType() == 'Espece' ?
                        $caisse = $caisse + $credit->getMontant() :
                        $banque = $banque + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debit->getType() == 'Espece' ?
                        $debitcaisse = $debitcaisse + $debit->getMontant() :
                        $debitbanque = $debitbanque + $debit->getMontant();

                }

            }

            foreach ($ouverture as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $credit->getType() == 'Espece' ?
                        $caisseouv = $caisseouv + $credit->getMontant() :
                        $banqueouv = $banqueouv + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debit->getType() == 'Espece' ?
                        $debitcaisseouv = $debitcaisseouv + $debit->getMontant() :
                        $debitbanqueouv = $debitbanqueouv + $debit->getMontant();

                }

            }

            return $this->render('finance/brouyard_interval_print.html.twig', [
                'caisse' => $caisse - $debitcaisse + $caisseouv - $debitcaisseouv,
                'banque' => $banque - $debitbanque + $banqueouv - $debitbanqueouv,
                'ecritures' => $ecritures,
                'ouverture' => ($caisseouv - $debitcaisseouv) + ($banqueouv - $debitbanqueouv),
                'day1' => $date1,
                'day2' => $date2,
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

    #[Route("/LienDaysBrouyardCaisse", name :"days_brouyard_lien_caisse") ]
    public function liendaysbrouyardcaisse(Request $request)
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {
            $date1 = $request->get('date1');
            $date2 = $request->get('date2');
            $lien = $this->generateUrl('finance_days_brouyard_caisse', ['date1' => $date1, 'date2' => $date2]);
            $res['ok'] = $lien;
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
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


    #[Route("/DaysBrouyardCaisse/{date1}/{date2}", name :"days_brouyard_caisse") ]
    public function daysbrouyardcaisse(Request $request, $date1, $date2)
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {

            $repository = $this->entityManager->getRepository(Ecriture::class);
            $ecritures = $repository->plagecaisse($date1, $date2);
            $ouverture = $repository->ouvertureplacecaisse($date1);
            $caisse = 0;
            $caisseouv = 0;
            $debitcaisse = 0;
            $debitcaisseouv = 0;
            $credits = [];
                $debits = [];
           

            foreach ($ouverture as $ecriture) {
                $credittest = null;
                $debittest = null;
                if ($ecriture->getCredit() != null) {
                    $credittest = $ecriture->getCredit();
                    $caisseouv = $caisseouv + $credittest->getMontant();
                } else {

                    $debittest = $ecriture->getDebit();
                    $debitcaisseouv = $debitcaisseouv + $debittest->getMontant();

                }

            }
             foreach ($ecritures as $ecriture) {

                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $credits[] = $ecriture;
                    $caisse = $caisse + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debits[] = $ecriture;
                    $debitcaisse = $debitcaisse + $debit->getMontant();

                }

            }

            return $this->render('finance/brouyard_interval_caisse.html.twig', [
                'caisse' => $caisse - $debitcaisse + $caisseouv - $debitcaisseouv,
                // 'ecritures' => $ecritures,
                'credits' => $credits,
                'debits' => $debits,
                'ouverture' => $caisseouv - $debitcaisseouv,
                'day1' => $date1,
                'day2' => $date2,
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

    #[Route("/DaysBrouyardCaisse_print/{date1}/{date2}", name :"days_brouyard_caisse_print") ]
    public function daysbrouyardcaisse_print(Request $request, $date1, $date2)
    {
        if ($this->security->isGranted('ROLE_CAISSIER')) {

            $repository = $this->entityManager->getRepository(Ecriture::class);
            $ecritures = $repository->plagecaisse($date1, $date2);
            $ouverture = $repository->ouvertureplacecaisse($date1);
            $caisse = 0;
            $caisseouv = 0;
            $debitcaisse = 0;
            $debitcaisseouv = 0;
             $credits = [];
                $debits = [];
           

            foreach ($ouverture as $ecriture) {
                $credittest = null;
                $debittest = null;
                if ($ecriture->getCredit() != null) {
                    $credittest = $ecriture->getCredit();
                    $caisseouv = $caisseouv + $credittest->getMontant();
                } else {

                    $debittest = $ecriture->getDebit();
                    $debitcaisseouv = $debitcaisseouv + $debittest->getMontant();

                }

            }
             foreach ($ecritures as $ecriture) {

                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $credits[] = $ecriture;
                    $caisse = $caisse + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debits[] = $ecriture;
                    $debitcaisse = $debitcaisse + $debit->getMontant();

                }

            }

            return $this->render('finance/brouyard_interval_caisse_print.html.twig', [
                'caisse' => $caisse - $debitcaisse + $caisseouv - $debitcaisseouv,
                // 'ecritures' => $ecritures,
                'credits' => $credits,
                'debits' => $debits,
                'ouverture' => $caisseouv - $debitcaisseouv,
                'day1' => $date1,
                'day2' => $date2,
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


    #[Route("/LienDaysBrouyardBanque", name :"days_brouyard_lien_banque") ]
    public function liendaysbrouyardbanque(Request $request)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $date1 = $request->get('date1');
            $date2 = $request->get('date2');
            $lien = $this->generateUrl('finance_days_brouyard_banque', ['date1' => $date1, 'date2' => $date2]);
            $res['ok'] = $lien;
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
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


    #[Route("/DaysBrouyardBanque/{date1}/{date2}", name :"days_brouyard_banque") ]
    public function daysbrouyardbanque(Request $request, $date1, $date2)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

            $repository = $this->entityManager->getRepository(Ecriture::class);
            $ecritures = $repository->plagebanque($date1, $date2);
            $ouverture = $repository->ouvertureplacebanque($date1);
            $caisse = 0;
            $caisseouv = 0;
            $banque = 0;
            $banqueouv = 0;
            $debitbanque = 0;
            $debitbanqueouv = 0;
            $debitcaisse = 0;
            $debitcaisseouv = 0;
            foreach ($ecritures as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $banque = $banque + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debitbanque = $debitbanque + $debit->getMontant();

                }

            }

            foreach ($ouverture as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $banqueouv = $banqueouv + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debitbanqueouv = $debitbanqueouv + $debit->getMontant();

                }

            }

           
            $response = $this->render('finance/brouyard_interval_banque.html.twig', [
                'banque' => $banque - $debitbanque + $banqueouv - $debitbanqueouv,
                'ecritures' => $ecritures,
                'ouverture' => $banqueouv - $debitbanqueouv,
                'day1' => $date1,
                'day2' => $date2,
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

    #[Route("/DaysBrouyardBanque_print/{date1}/{date2}", name :"days_brouyard_banque_print") ]
    public function daysbrouyardbanque_print(Request $request, $date1, $date2)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

            $repository = $this->entityManager->getRepository(Ecriture::class);
            $ecritures = $repository->plagebanque($date1, $date2);
            $ouverture = $repository->ouvertureplacebanque($date1);
            $caisse = 0;
            $caisseouv = 0;
            $banque = 0;
            $banqueouv = 0;
            $debitbanque = 0;
            $debitbanqueouv = 0;
            $debitcaisse = 0;
            $debitcaisseouv = 0;
            foreach ($ecritures as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $banque = $banque + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debitbanque = $debitbanque + $debit->getMontant();

                }

            }

            foreach ($ouverture as $ecriture) {
                $credit = null;
                $debit = null;
                if ($ecriture->getCredit() != null) {
                    $credit = $ecriture->getCredit();
                    $banqueouv = $banqueouv + $credit->getMontant();
                } else {

                    $debit = $ecriture->getDebit();
                    $debitbanqueouv = $debitbanqueouv + $debit->getMontant();

                }

            }

            return $this->render('finance/brouyard_interval_banque_print.html.twig', [
                'banque' => $banque - $debitbanque + $banqueouv - $debitbanqueouv,
                'ecritures' => $ecritures,
                'ouverture' => $banqueouv - $debitbanqueouv,
                'day1' => $date1,
                'day2' => $date2,
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


    #[Route("/Salaire", name :"salaire", methods : ["GET"]) ]
    public function salaire(PaieRepository $paieRepository): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $paie = $paieRepository->findBy(['payer' => false]);
            
            $response = $this->render('finance/salaire.html.twig', [
                'paies' => $paie,
                'banques' => $this->entityManager->getRepository(Banque::class)->findAll(),
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

    #[Route("/payer", name :"payer", methods : ["POST"]) ]
    public function payer(PaieRepository $paieRepository, Solde $solde, Request $request): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            
            $entityManager = $this->entityManager;
            $paie = $paieRepository->find($request->get('paie'));
            $banque = $entityManager->getRepository(Banque::class)->find($request->get('banque'));


            $montant = 0;

            $montant = $solde->montantbanque($entityManager, $banque->getCompte());
            $montantapayer = $paie->getBrut() + $paie->getTotalchargepatronal();

            if ($montantapayer <= $montant) {
               
                $paieSalaire = new PaieSalaire();


            $ecriturebase = new Ecriture();
            $ecriturebase->setComptecredit("661100");
            $ecriturebase->setLibellecomptecredit("SALAIRE DE BASE");
            $ecriturebase->setComptedebit("422100");
            $ecriturebase->setLibellecomptedebit("SALAIRE DE BASE");
            $ecriturebase->setSolde(0);
            $ecriturebase->setMontant($paie->getSalaireBase());
            $ecriturebase->setLibelle("SALAIRE DE BASE".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($ecriturebase);

            // anciennete 
            $ancien = $paie->getTauxenciennete() * $paie->getBaseenciennete();
            if( $ancien != 0){
              

                $ecritureanciennete = new Ecriture();
                $ecritureanciennete->setComptecredit("6612");
                $ecritureanciennete->setLibellecomptecredit("Prime d'anciennete");
                $ecritureanciennete->setComptedebit("422");
                $ecritureanciennete->setLibellecomptedebit("Prime d'anciennete");
                $ecritureanciennete->setSolde(0);
                $ecritureanciennete->setMontant($ancien);
                $ecritureanciennete->setLibelle("Prime d'anciennete ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
               
                $entityManager->persist($ecritureanciennete);
            }
            /*    $heureSup =  $paie->getTauxheureSup() * $paie->getBaseheureSup();
            if( $heureSup != 0){
              
                // traitement heure sup
            }
            if($paie->getPerformance() != 0){
               //traitement prime de performance
            }*/
            


            $primes = json_decode($paie->getIndemnite(), true);
             $j= 1;
            foreach ($primes as $prime) {

                $$j = new Ecriture();

                if (strtolower($prime['designation']) === 'indemnité de transport' || strtolower($prime['designation']) === 'indemnite de transport') {
                    $$j->setComptecredit("663400");
                }
                else if(strtolower($prime['designation']) === 'indemnité de logement' || strtolower($prime['designation']) === 'indemnite de logement') {
                    $$j->setComptecredit("663100");   
                }
                elseif (strtolower($prime['designation']) === 'indemnité de représentation'
                ||  strtolower($prime['designation']) === 'indemnité de representation'
                ||  strtolower($prime['designation']) === 'indemnite de représentation'
                 || strtolower($prime['designation']) === 'indemnite de representation') {
                    $$j->setComptecredit("663200");
                }
                else if(strtolower($prime['designation']) === 'indemnité de déplacement' 
                || strtolower($prime['designation']) === 'indemnité de deplacement'
                || strtolower($prime['designation']) === 'indemnite de déplacement'
                || strtolower($prime['designation']) === 'indemnite de deplacement') {
                    $$j->setComptecredit("663800");   
                }
                else if(strtolower($prime['designation']) === 'allocation de congé' 
                || strtolower($prime['designation']) === 'allocation de conge') {
                    $$j->setComptecredit("661300");   
                }

                $$j->setLibellecomptecredit($prime['designation']);
                $$j->setComptedebit("422100");
                $$j->setLibellecomptedebit($prime['designation']);
                $$j->setSolde(0);
                $$j->setMontant($prime['montant']);
                $$j->setLibelle($prime['designation']. " " .$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
               
                $entityManager->persist($$j);
               
               $j +=1;
            }

            $irpp = new Ecriture();
            $irpp->setComptecredit("422100");
            $irpp->setLibellecomptecredit("IRPP");
            $irpp->setComptedebit("447210");
            $irpp->setLibellecomptedebit("IRPP");
            $irpp->setSolde(0);
            $irpp->setMontant($paie->getIrpp());
            $irpp->setLibelle("IRPP ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($irpp);

            $ca = new Ecriture();
            $ca->setComptecredit("422100");
            $ca->setLibellecomptecredit("CAC");
            $ca->setComptedebit("447210");
            $ca->setLibellecomptedebit("CAC");
            $ca->setSolde(0);
            $ca->setMontant($paie->getCa());
            $ca->setLibelle("CAC ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($ca);

            $foncier = new Ecriture();
            $foncier->setComptecredit("422100");
            $foncier->setLibellecomptecredit("CFC/SALARIALE");
            $foncier->setComptedebit("447230");
            $foncier->setLibellecomptedebit("CFC/SALARIALE");
            $foncier->setSolde(0);
            $foncier->setMontant($paie->getFoncier());
            $foncier->setLibelle("CFC/SALARIALE ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($foncier);

            $local = new Ecriture();
            $local->setComptecredit("422100");
            $local->setLibellecomptecredit("Taxe de Develppement Local");
            $local->setComptedebit("447210");
            $local->setLibellecomptedebit("Taxe de Develppement Local");
            $local->setSolde(0);
            $local->setMontant($paie->getLocal());
            $local->setLibelle("Taxe de Develppement Local ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($local);

            $crtv = new Ecriture();
            $crtv->setComptecredit("447250");
            $crtv->setLibellecomptecredit("REDEVANCE AUDIO VIS");
            $crtv->setComptedebit("422100");
            $crtv->setLibellecomptedebit("REDEVANCE AUDIO VIS");
            $crtv->setSolde(0);
            $crtv->setMontant($paie->getCrtv());
            $crtv->setLibelle("REDEVANCE AUDIO VIS ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($crtv);

            $viel = new Ecriture();
            $viel->setComptecredit("431300");
            $viel->setLibellecomptecredit(" PVID/SALARIALE");
            $viel->setComptedebit("447210");
            $viel->setLibellecomptedebit(" PVID/SALARIALE");
            $viel->setSolde(0);
            $viel->setMontant($paie->getVieil());
            $viel->setLibelle(" PVID/SALARIALE ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($viel);

            $cpfoncier = new Ecriture();
            $cpfoncier->setComptecredit("641300");
            $cpfoncier->setLibellecomptecredit("FNE + CFC/PATRONALE");
            $cpfoncier->setComptedebit("422800");
            $cpfoncier->setLibellecomptedebit("FNE + CFC/PATRONALE");
            $cpfoncier->setSolde(0);
            $cpfoncier->setMontant($paie->getCpfoncier() + $paie->getFne());
            $cpfoncier->setLibelle("FNE + CFC/PATRONALE ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($cpfoncier);

            $allocation = new Ecriture();
            $allocation->setComptecredit("644100");
            $allocation->setLibellecomptecredit(" ALLOCATION FAM");
            $allocation->setComptedebit("431100");
            $allocation->setLibellecomptedebit(" ALLOCATION FAM");
            $allocation->setSolde(0);
            $allocation->setMontant($paie->getAllocation());
            $allocation->setLibelle(" ALLOCATION FAM ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($allocation);

            $tav = new Ecriture();
            $tav->setComptecredit("644100");
            $tav->setLibellecomptecredit("ACCIDENT DE TRAVAIL");
            $tav->setComptedebit("431200");
            $tav->setLibellecomptedebit("ACCIDENT DE TRAVAIL");
            $tav->setSolde(0);
            $tav->setMontant($paie->getTav());
            $tav->setLibelle("ACCIDENT DE TRAVAIL ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($tav);

            $cpviel = new Ecriture();
            $cpviel->setComptecredit("644100");
            $cpviel->setLibellecomptecredit("PVID/ PATRONALE");
            $cpviel->setComptedebit("431300");
            $cpviel->setLibellecomptedebit("PVID/ PATRONALE");
            $cpviel->setSolde(0);
            $cpviel->setMontant($paie->getCpvieil());
            $cpviel->setLibelle("PVID/ PATRONALE ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($cpviel);

            $debitinet = new Debit();
            $debitinet->setCompte($banque->getCompte());
            $debitinet->setType('Banque');
            $debitinet->setSalaire($paieSalaire);
            $debitinet->setMontant($paie->getSalaireNet());

            $ecritureinet = new Ecriture();
            $ecritureinet->setType('Banque');
            $ecritureinet->setComptecredit("422100");
            $ecritureinet->setLibellecomptecredit("Salaire Net");
            $ecritureinet->setComptedebit($banque->getCompte());
            $ecritureinet->setLibellecomptedebit($banque->getNom());
            $ecritureinet->setDebit($debitinet);
            $ecritureinet->setSolde(-$paie->getSalaireNet());
            $ecritureinet->setMontant($paie->getSalaireNet());
            $ecritureinet->setLibelle("Salaire Net ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($debitinet);
            $entityManager->persist($ecritureinet);

            $debitallocation = new Debit();
            $debitallocation->setCompte($banque->getCompte());
            $debitallocation->setType('Banque');
            $debitallocation->setSalaire($paieSalaire);
            $debitallocation->setMontant($paie->getAllocation());

            $ecritureallocation = new Ecriture();
            $ecritureallocation->setType('Banque');
            $ecritureallocation->setComptecredit("431100");
            $ecritureallocation->setLibellecomptecredit("ALLOCATION FAM");
            $ecritureallocation->setComptedebit($banque->getCompte());
            $ecritureallocation->setLibellecomptedebit($banque->getNom());
            $ecritureallocation->setDebit($debitallocation);
            $ecritureallocation->setSolde(-$paie->getAllocation());
            $ecritureallocation->setMontant($paie->getAllocation());
            $ecritureallocation->setLibelle("ALLOCATION FAM ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($debitallocation);
            $entityManager->persist($ecritureallocation);

            $debittav = new Debit();
            $debittav->setCompte($banque->getCompte());
            $debittav->setType('Banque');
            $debittav->setSalaire($paieSalaire);
            $debittav->setMontant($paie->getTav());

            $ecrituretav = new Ecriture();
            $ecrituretav->setType('Banque');
            $ecrituretav->setComptecredit("431200");
            $ecrituretav->setLibellecomptecredit("ACCIDENT DE TRAVAIL");
            $ecrituretav->setComptedebit($banque->getCompte());
            $ecrituretav->setLibellecomptedebit($banque->getNom());
            $ecrituretav->setDebit($debittav);
            $ecrituretav->setSolde(-$paie->getTav());
            $ecrituretav->setMontant($paie->getTav());
            $ecrituretav->setLibelle("ACCIDENT DE TRAVAIL ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($debittav);
            $entityManager->persist($ecrituretav);

            $debitpvid = new Debit();
            $debitpvid->setCompte($banque->getCompte());
            $debitpvid->setType('Banque');
            $debitpvid->setSalaire($paieSalaire);
            $debitpvid->setMontant($paie->getVieil() + $paie->getCpvieil());

            $ecriturepvid = new Ecriture();
            $ecriturepvid->setType('Banque');
            $ecriturepvid->setComptecredit("431300");
            $ecriturepvid->setLibellecomptecredit("PVID/ SAL + PATR");
            $ecriturepvid->setComptedebit($banque->getCompte());
            $ecriturepvid->setLibellecomptedebit($banque->getNom());
            $ecriturepvid->setDebit($debitpvid);
            $ecriturepvid->setSolde(-($paie->getVieil() + $paie->getCpvieil()));
            $ecriturepvid->setMontant($paie->getVieil() + $paie->getCpvieil());
            $ecriturepvid->setLibelle("PVID/ SAL + PATR ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($debitpvid);
            $entityManager->persist($ecriturepvid);

            $debitirpp = new Debit();
            $debitirpp->setCompte($banque->getCompte());
            $debitirpp->setType('Banque');
            $debitirpp->setSalaire($paieSalaire);
            $debitirpp->setMontant($paie->getIrpp());

            $ecritureirpp = new Ecriture();
            $ecritureirpp->setType('Banque');
            $ecritureirpp->setComptecredit("447210");
            $ecritureirpp->setLibellecomptecredit("IRPP");
            $ecritureirpp->setComptedebit($banque->getCompte());
            $ecritureirpp->setLibellecomptedebit($banque->getNom());
            $ecritureirpp->setDebit($debitirpp);
            $ecritureirpp->setSolde(-$paie->getIrpp());
            $ecritureirpp->setMontant($paie->getIrpp());
            $ecritureirpp->setLibelle("IRPP ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($debitirpp);
            $entityManager->persist($ecritureirpp);

            $debiticac = new Debit();
            $debiticac->setCompte($banque->getCompte());
            $debiticac->setType('Banque');
            $debiticac->setSalaire($paieSalaire);
            $debiticac->setMontant($paie->getCa());

            $ecritureicac = new Ecriture();
            $ecritureicac->setType('Banque');
            $ecritureicac->setComptecredit("447220");
            $ecritureicac->setLibellecomptecredit("CAC");
            $ecritureicac->setComptedebit($banque->getCompte());
            $ecritureicac->setLibellecomptedebit($banque->getNom());
            $ecritureicac->setDebit($debiticac);
            $ecritureicac->setSolde(-$paie->getCa());
            $ecritureicac->setMontant($paie->getCa());
            $ecritureicac->setLibelle("CAC ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($debiticac);
            $entityManager->persist($ecritureicac);

            $debitfoncier = new Debit();
            $debitfoncier->setCompte($banque->getCompte());
            $debitfoncier->setType('Banque');
            $debitfoncier->setSalaire($paieSalaire);
            $debitfoncier->setMontant($paie->getFoncier());

            $ecriturefoncier = new Ecriture();
            $ecriturefoncier->setType('Banque');
            $ecriturefoncier->setComptecredit("447230");
            $ecriturefoncier->setLibellecomptecredit("CFC /SALARIAL");
            $ecriturefoncier->setComptedebit($banque->getCompte());
            $ecriturefoncier->setLibellecomptedebit($banque->getNom());
            $ecriturefoncier->setDebit($debitfoncier);
            $ecriturefoncier->setSolde(-$paie->getFoncier());
            $ecriturefoncier->setMontant($paie->getFoncier());
            $ecriturefoncier->setLibelle("CFC /SALARIAL ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($debitfoncier);
            $entityManager->persist($ecriturefoncier);

            $debitlocal = new Debit();
            $debitlocal->setCompte($banque->getCompte());
            $debitlocal->setType('Banque');
            $debitlocal->setSalaire($paieSalaire);
            $debitlocal->setMontant($paie->getLocal());

            $ecriturelocal = new Ecriture();
            $ecriturelocal->setType('Banque');
            $ecriturelocal->setComptecredit("447240");
            $ecriturelocal->setLibellecomptecredit("TAXE DE DEVELOPPEMENT LOC");
            $ecriturelocal->setComptedebit($banque->getCompte());
            $ecriturelocal->setLibellecomptedebit($banque->getNom());
            $ecriturelocal->setDebit($debitlocal);
            $ecriturelocal->setSolde(-$paie->getLocal());
            $ecriturelocal->setMontant($paie->getLocal());
            $ecriturelocal->setLibelle("TAXE DE DEVELOPPEMENT LOC ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($debitlocal);
            $entityManager->persist($ecriturelocal);

            $debitcrtv = new Debit();
            $debitcrtv->setCompte($banque->getCompte());
            $debitcrtv->setType('Banque');
            $debitcrtv->setSalaire($paieSalaire);
            $debitcrtv->setMontant($paie->getCrtv());

            $ecriturelcrtv = new Ecriture();
            $ecriturelcrtv->setType('Banque');
            $ecriturelcrtv->setComptecredit("447250");
            $ecriturelcrtv->setLibellecomptecredit("REDEVANCE AUDIO VIS");
            $ecriturelcrtv->setComptedebit($banque->getCompte());
            $ecriturelcrtv->setLibellecomptedebit($banque->getNom());
            $ecriturelcrtv->setDebit($debitcrtv);
            $ecriturelcrtv->setSolde(-$paie->getCrtv());
            $ecriturelcrtv->setMontant($paie->getCrtv());
            $ecriturelcrtv->setLibelle("REDEVANCE AUDIO VIS ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($debitcrtv);
            $entityManager->persist($ecriturelcrtv);

            $debitcpfoncier = new Debit();
            $debitcpfoncier->setCompte($banque->getCompte());
            $debitcpfoncier->setType('Banque');
            $debitcpfoncier->setSalaire($paieSalaire);
            $debitcpfoncier->setMontant($paie->getCpfoncier() + $paie->getFne());

            $ecriturelcpfocncier = new Ecriture();
            $ecriturelcpfocncier->setType('Banque');
            $ecriturelcpfocncier->setComptecredit("442100");
            $ecriturelcpfocncier->setLibellecomptecredit("FNE + CFC/PATRONALE");
            $ecriturelcpfocncier->setComptedebit($banque->getCompte());
            $ecriturelcpfocncier->setLibellecomptedebit($banque->getNom());
            $ecriturelcpfocncier->setDebit($debitcpfoncier);
            $ecriturelcpfocncier->setSolde(-($paie->getCpfoncier() + $paie->getFne()));
            $ecriturelcpfocncier->setMontant($paie->getCpfoncier() + $paie->getFne());
            $ecriturelcpfocncier->setLibelle("FNE + CFC/PATRONALE ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($debitcpfoncier);
            $entityManager->persist($ecriturelcpfocncier);

       
               
                $paieSalaire->setUser($this->getUser());
                $paieSalaire->setMontant($paie->getSalaireNet());
                $paieSalaire->setEmploye($paie->getEmploye());
                $paieSalaire->setCompte($banque->getCompte());
                $entityManager->persist($paieSalaire);
                $paie->setPayer(true);
                $paie->setDatepaye(new \DateTime());
                $entityManager->persist($paie);

               $entityManager->flush();
                $this->addFlash('notice', 'Paiement effectué avec succès');

//                    return $this->redirectToRoute('depense_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $this->addFlash('notice', 'Montant non disponible');
            }




            $res['id'] = 'ok';


            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
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

    #[Route("/payerTous", name :"payerTous", methods : ["POST"]) ]
    public function payertous(PaieRepository $paieRepository, Solde $solde, Request $request): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

            $entityManager = $this->entityManager;
            $montant = $request->get('montant');
            $banque = $entityManager->getRepository(Banque::class)->find($request->get('banque'));

            $solde = $solde->montantbanque($entityManager, $banque->getCompte());

            if ($montant <= $solde) {
                $paies = $paieRepository->findBy(['payer' => false]);


                foreach ($paies as $paie) {
                       
                        $paieSalaire = new PaieSalaire();
        
        
                    

            $ecriturebase = new Ecriture();
            $ecriturebase->setComptecredit("661100");
            $ecriturebase->setLibellecomptecredit("SALAIRE DE BASE");
            $ecriturebase->setComptedebit("422100");
            $ecriturebase->setLibellecomptedebit("SALAIRE DE BASE");
            $ecriturebase->setSolde(0);
            $ecriturebase->setMontant($paie->getSalaireBase());
            $ecriturebase->setLibelle("SALAIRE DE BASE".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($ecriturebase);

            // anciennete 
            $ancien = $paie->getTauxenciennete() * $paie->getBaseenciennete();
            if( $ancien != 0){
              

                $ecritureanciennete = new Ecriture();
                $ecritureanciennete->setComptecredit("6612");
                $ecritureanciennete->setLibellecomptecredit("Prime d'anciennete");
                $ecritureanciennete->setComptedebit("422");
                $ecritureanciennete->setLibellecomptedebit("Prime d'anciennete");
                $ecritureanciennete->setSolde(0);
                $ecritureanciennete->setMontant($ancien);
                $ecritureanciennete->setLibelle("Prime d'anciennete ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
               
                $entityManager->persist($ecritureanciennete);
            }
            /*    $heureSup =  $paie->getTauxheureSup() * $paie->getBaseheureSup();
            if( $heureSup != 0){
              
                // traitement heure sup
            }
            if($paie->getPerformance() != 0){
               //traitement prime de performance
            }*/
            


            $primes = json_decode($paie->getIndemnite(), true);
             $j= 1;
            foreach ($primes as $prime) {

                $$j = new Ecriture();

                if (strtolower($prime['designation']) === 'indemnité de transport' || strtolower($prime['designation']) === 'indemnite de transport') {
                    $$j->setComptecredit("663400");
                }
                else if(strtolower($prime['designation']) === 'indemnité de logement' || strtolower($prime['designation']) === 'indemnite de logement') {
                    $$j->setComptecredit("663100");   
                }
                elseif (strtolower($prime['designation']) === 'indemnité de représentation'
                ||  strtolower($prime['designation']) === 'indemnité de representation'
                ||  strtolower($prime['designation']) === 'indemnite de représentation'
                 || strtolower($prime['designation']) === 'indemnite de representation') {
                    $$j->setComptecredit("663200");
                }
                else if(strtolower($prime['designation']) === 'indemnité de déplacement' 
                || strtolower($prime['designation']) === 'indemnité de deplacement'
                || strtolower($prime['designation']) === 'indemnite de déplacement'
                || strtolower($prime['designation']) === 'indemnite de deplacement') {
                    $$j->setComptecredit("663800");   
                }
                else if(strtolower($prime['designation']) === 'allocation de congé' 
                || strtolower($prime['designation']) === 'allocation de conge') {
                    $$j->setComptecredit("661300");   
                }

                $$j->setLibellecomptecredit($prime['designation']);
                $$j->setComptedebit("422100");
                $$j->setLibellecomptedebit($prime['designation']);
                $$j->setSolde(0);
                $$j->setMontant($prime['montant']);
                $$j->setLibelle($prime['designation']. " " .$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
               
                $entityManager->persist($$j);
               
               $j +=1;
            }

            $irpp = new Ecriture();
            $irpp->setComptecredit("422100");
            $irpp->setLibellecomptecredit("IRPP");
            $irpp->setComptedebit("447210");
            $irpp->setLibellecomptedebit("IRPP");
            $irpp->setSolde(0);
            $irpp->setMontant($paie->getIrpp());
            $irpp->setLibelle("IRPP ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($irpp);

            $ca = new Ecriture();
            $ca->setComptecredit("422100");
            $ca->setLibellecomptecredit("CAC");
            $ca->setComptedebit("447210");
            $ca->setLibellecomptedebit("CAC");
            $ca->setSolde(0);
            $ca->setMontant($paie->getCa());
            $ca->setLibelle("CAC ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($ca);

            $foncier = new Ecriture();
            $foncier->setComptecredit("422100");
            $foncier->setLibellecomptecredit("CFC/SALARIALE");
            $foncier->setComptedebit("447230");
            $foncier->setLibellecomptedebit("CFC/SALARIALE");
            $foncier->setSolde(0);
            $foncier->setMontant($paie->getFoncier());
            $foncier->setLibelle("CFC/SALARIALE ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($foncier);

            $local = new Ecriture();
            $local->setComptecredit("422100");
            $local->setLibellecomptecredit("Taxe de Develppement Local");
            $local->setComptedebit("447210");
            $local->setLibellecomptedebit("Taxe de Develppement Local");
            $local->setSolde(0);
            $local->setMontant($paie->getLocal());
            $local->setLibelle("Taxe de Develppement Local ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($local);

            $crtv = new Ecriture();
            $crtv->setComptecredit("447250");
            $crtv->setLibellecomptecredit("REDEVANCE AUDIO VIS");
            $crtv->setComptedebit("422100");
            $crtv->setLibellecomptedebit("REDEVANCE AUDIO VIS");
            $crtv->setSolde(0);
            $crtv->setMontant($paie->getCrtv());
            $crtv->setLibelle("REDEVANCE AUDIO VIS ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($crtv);

            $viel = new Ecriture();
            $viel->setComptecredit("431300");
            $viel->setLibellecomptecredit(" PVID/SALARIALE");
            $viel->setComptedebit("447210");
            $viel->setLibellecomptedebit(" PVID/SALARIALE");
            $viel->setSolde(0);
            $viel->setMontant($paie->getVieil());
            $viel->setLibelle(" PVID/SALARIALE ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($viel);

            $cpfoncier = new Ecriture();
            $cpfoncier->setComptecredit("641300");
            $cpfoncier->setLibellecomptecredit("FNE + CFC/PATRONALE");
            $cpfoncier->setComptedebit("422800");
            $cpfoncier->setLibellecomptedebit("FNE + CFC/PATRONALE");
            $cpfoncier->setSolde(0);
            $cpfoncier->setMontant($paie->getCpfoncier() + $paie->getFne());
            $cpfoncier->setLibelle("FNE + CFC/PATRONALE ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($cpfoncier);

            $allocation = new Ecriture();
            $allocation->setComptecredit("644100");
            $allocation->setLibellecomptecredit(" ALLOCATION FAM");
            $allocation->setComptedebit("431100");
            $allocation->setLibellecomptedebit(" ALLOCATION FAM");
            $allocation->setSolde(0);
            $allocation->setMontant($paie->getAllocation());
            $allocation->setLibelle(" ALLOCATION FAM ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($allocation);

            $tav = new Ecriture();
            $tav->setComptecredit("644100");
            $tav->setLibellecomptecredit("ACCIDENT DE TRAVAIL");
            $tav->setComptedebit("431200");
            $tav->setLibellecomptedebit("ACCIDENT DE TRAVAIL");
            $tav->setSolde(0);
            $tav->setMontant($paie->getTav());
            $tav->setLibelle("ACCIDENT DE TRAVAIL ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($tav);

            $cpviel = new Ecriture();
            $cpviel->setComptecredit("644100");
            $cpviel->setLibellecomptecredit("PVID/ PATRONALE");
            $cpviel->setComptedebit("431300");
            $cpviel->setLibellecomptedebit("PVID/ PATRONALE");
            $cpviel->setSolde(0);
            $cpviel->setMontant($paie->getCpvieil());
            $cpviel->setLibelle("PVID/ PATRONALE ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($cpviel);

            $debitinet = new Debit();
            $debitinet->setCompte($banque->getCompte());
            $debitinet->setType('Banque');
            $debitinet->setSalaire($paieSalaire);
            $debitinet->setMontant($paie->getSalaireNet());

            $ecritureinet = new Ecriture();
            $ecritureinet->setType('Banque');
            $ecritureinet->setComptecredit("422100");
            $ecritureinet->setLibellecomptecredit("Salaire Net");
            $ecritureinet->setComptedebit($banque->getCompte());
            $ecritureinet->setLibellecomptedebit($banque->getNom());
            $ecritureinet->setDebit($debitinet);
            $ecritureinet->setSolde(-$paie->getSalaireNet());
            $ecritureinet->setMontant($paie->getSalaireNet());
            $ecritureinet->setLibelle("Salaire Net ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($debitinet);
            $entityManager->persist($ecritureinet);

            $debitallocation = new Debit();
            $debitallocation->setCompte($banque->getCompte());
            $debitallocation->setType('Banque');
            $debitallocation->setSalaire($paieSalaire);
            $debitallocation->setMontant($paie->getAllocation());

            $ecritureallocation = new Ecriture();
            $ecritureallocation->setType('Banque');
            $ecritureallocation->setComptecredit("431100");
            $ecritureallocation->setLibellecomptecredit("ALLOCATION FAM");
            $ecritureallocation->setComptedebit($banque->getCompte());
            $ecritureallocation->setLibellecomptedebit($banque->getNom());
            $ecritureallocation->setDebit($debitallocation);
            $ecritureallocation->setSolde(-$paie->getAllocation());
            $ecritureallocation->setMontant($paie->getAllocation());
            $ecritureallocation->setLibelle("ALLOCATION FAM ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($debitallocation);
            $entityManager->persist($ecritureallocation);

            $debittav = new Debit();
            $debittav->setCompte($banque->getCompte());
            $debittav->setType('Banque');
            $debittav->setSalaire($paieSalaire);
            $debittav->setMontant($paie->getTav());

            $ecrituretav = new Ecriture();
            $ecrituretav->setType('Banque');
            $ecrituretav->setComptecredit("431200");
            $ecrituretav->setLibellecomptecredit("ACCIDENT DE TRAVAIL");
            $ecrituretav->setComptedebit($banque->getCompte());
            $ecrituretav->setLibellecomptedebit($banque->getNom());
            $ecrituretav->setDebit($debittav);
            $ecrituretav->setSolde(-$paie->getTav());
            $ecrituretav->setMontant($paie->getTav());
            $ecrituretav->setLibelle("ACCIDENT DE TRAVAIL ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($debittav);
            $entityManager->persist($ecrituretav);

            $debitpvid = new Debit();
            $debitpvid->setCompte($banque->getCompte());
            $debitpvid->setType('Banque');
            $debitpvid->setSalaire($paieSalaire);
            $debitpvid->setMontant($paie->getVieil() + $paie->getCpvieil());

            $ecriturepvid = new Ecriture();
            $ecriturepvid->setType('Banque');
            $ecriturepvid->setComptecredit("431300");
            $ecriturepvid->setLibellecomptecredit("PVID/ SAL + PATR");
            $ecriturepvid->setComptedebit($banque->getCompte());
            $ecriturepvid->setLibellecomptedebit($banque->getNom());
            $ecriturepvid->setDebit($debitpvid);
            $ecriturepvid->setSolde(-($paie->getVieil() + $paie->getCpvieil()));
            $ecriturepvid->setMontant($paie->getVieil() + $paie->getCpvieil());
            $ecriturepvid->setLibelle("PVID/ SAL + PATR ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($debitpvid);
            $entityManager->persist($ecriturepvid);

            $debitirpp = new Debit();
            $debitirpp->setCompte($banque->getCompte());
            $debitirpp->setType('Banque');
            $debitirpp->setSalaire($paieSalaire);
            $debitirpp->setMontant($paie->getIrpp());

            $ecritureirpp = new Ecriture();
            $ecritureirpp->setType('Banque');
            $ecritureirpp->setComptecredit("447210");
            $ecritureirpp->setLibellecomptecredit("IRPP");
            $ecritureirpp->setComptedebit($banque->getCompte());
            $ecritureirpp->setLibellecomptedebit($banque->getNom());
            $ecritureirpp->setDebit($debitirpp);
            $ecritureirpp->setSolde(-$paie->getIrpp());
            $ecritureirpp->setMontant($paie->getIrpp());
            $ecritureirpp->setLibelle("IRPP ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($debitirpp);
            $entityManager->persist($ecritureirpp);

            $debiticac = new Debit();
            $debiticac->setCompte($banque->getCompte());
            $debiticac->setType('Banque');
            $debiticac->setSalaire($paieSalaire);
            $debiticac->setMontant($paie->getCa());

            $ecritureicac = new Ecriture();
            $ecritureicac->setType('Banque');
            $ecritureicac->setComptecredit("447220");
            $ecritureicac->setLibellecomptecredit("CAC");
            $ecritureicac->setComptedebit($banque->getCompte());
            $ecritureicac->setLibellecomptedebit($banque->getNom());
            $ecritureicac->setDebit($debiticac);
            $ecritureicac->setSolde(-$paie->getCa());
            $ecritureicac->setMontant($paie->getCa());
            $ecritureicac->setLibelle("CAC ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($debiticac);
            $entityManager->persist($ecritureicac);

            $debitfoncier = new Debit();
            $debitfoncier->setCompte($banque->getCompte());
            $debitfoncier->setType('Banque');
            $debitfoncier->setSalaire($paieSalaire);
            $debitfoncier->setMontant($paie->getFoncier());

            $ecriturefoncier = new Ecriture();
            $ecriturefoncier->setType('Banque');
            $ecriturefoncier->setComptecredit("447230");
            $ecriturefoncier->setLibellecomptecredit("CFC /SALARIAL");
            $ecriturefoncier->setComptedebit($banque->getCompte());
            $ecriturefoncier->setLibellecomptedebit($banque->getNom());
            $ecriturefoncier->setDebit($debitfoncier);
            $ecriturefoncier->setSolde(-$paie->getFoncier());
            $ecriturefoncier->setMontant($paie->getFoncier());
            $ecriturefoncier->setLibelle("CFC /SALARIAL ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($debitfoncier);
            $entityManager->persist($ecriturefoncier);

            $debitlocal = new Debit();
            $debitlocal->setCompte($banque->getCompte());
            $debitlocal->setType('Banque');
            $debitlocal->setSalaire($paieSalaire);
            $debitlocal->setMontant($paie->getLocal());

            $ecriturelocal = new Ecriture();
            $ecriturelocal->setType('Banque');
            $ecriturelocal->setComptecredit("447240");
            $ecriturelocal->setLibellecomptecredit("TAXE DE DEVELOPPEMENT LOC");
            $ecriturelocal->setComptedebit($banque->getCompte());
            $ecriturelocal->setLibellecomptedebit($banque->getNom());
            $ecriturelocal->setDebit($debitlocal);
            $ecriturelocal->setSolde(-$paie->getLocal());
            $ecriturelocal->setMontant($paie->getLocal());
            $ecriturelocal->setLibelle("TAXE DE DEVELOPPEMENT LOC ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($debitlocal);
            $entityManager->persist($ecriturelocal);

            $debitcrtv = new Debit();
            $debitcrtv->setCompte($banque->getCompte());
            $debitcrtv->setType('Banque');
            $debitcrtv->setSalaire($paieSalaire);
            $debitcrtv->setMontant($paie->getCrtv());

            $ecriturelcrtv = new Ecriture();
            $ecriturelcrtv->setType('Banque');
            $ecriturelcrtv->setComptecredit("447250");
            $ecriturelcrtv->setLibellecomptecredit("REDEVANCE AUDIO VIS");
            $ecriturelcrtv->setComptedebit($banque->getCompte());
            $ecriturelcrtv->setLibellecomptedebit($banque->getNom());
            $ecriturelcrtv->setDebit($debitcrtv);
            $ecriturelcrtv->setSolde(-$paie->getCrtv());
            $ecriturelcrtv->setMontant($paie->getCrtv());
            $ecriturelcrtv->setLibelle("REDEVANCE AUDIO VIS ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($debitcrtv);
            $entityManager->persist($ecriturelcrtv);

            $debitcpfoncier = new Debit();
            $debitcpfoncier->setCompte($banque->getCompte());
            $debitcpfoncier->setType('Banque');
            $debitcpfoncier->setSalaire($paieSalaire);
            $debitcpfoncier->setMontant($paie->getCpfoncier() + $paie->getFne());

            $ecriturelcpfocncier = new Ecriture();
            $ecriturelcpfocncier->setType('Banque');
            $ecriturelcpfocncier->setComptecredit("442100");
            $ecriturelcpfocncier->setLibellecomptecredit("FNE + CFC/PATRONALE");
            $ecriturelcpfocncier->setComptedebit($banque->getCompte());
            $ecriturelcpfocncier->setLibellecomptedebit($banque->getNom());
            $ecriturelcpfocncier->setDebit($debitcpfoncier);
            $ecriturelcpfocncier->setSolde(-($paie->getCpfoncier() + $paie->getFne()));
            $ecriturelcpfocncier->setMontant($paie->getCpfoncier() + $paie->getFne());
            $ecriturelcpfocncier->setLibelle("FNE + CFC/PATRONALE ".$paie->getEmploye()->getNom(). " ".$paie->getEmploye()->getPrenom());
            $entityManager->persist($debitcpfoncier);
            $entityManager->persist($ecriturelcpfocncier);
        
                   
        
        
                  
                       
                        $paieSalaire->setUser($this->getUser());
                        $paieSalaire->setMontant($paie->getSalaireNet());
                        $paieSalaire->setEmploye($paie->getEmploye());
                        $paieSalaire->setCompte($banque->getCompte());
                        $entityManager->persist($paieSalaire);
                        $paie->setPayer(true);
                        $paie->setDatepaye(new \DateTime());
                        $entityManager->persist($paie);
        
                       $entityManager->flush();


                }
                $this->addFlash('notice', 'Paiements effectués avec succès');
            
//                    return $this->redirectToRoute('depense_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $this->addFlash('danger', 'Montant non disponible');
            }




            $res['id'] = 'ok';


            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
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

    #[Route("/Accompte", name :"accompte", methods : ["GET"]) ]
    public function accompte(AccompteRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $accomptes = $repository->findBy(['verser' => false]);
            
            $response = $this->render('finance/accompte.html.twig', [
                'accomptes' => $accomptes,
                'banques' => $this->entityManager->getRepository(Banque::class)->findAll(),
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

    #[Route("/payeraccompte", name :"payeraccompte", methods : ["POST"]) ]
    public function payeraccompte(Solde $solde, Request $request): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $debit = new Debit();
            $ecriture = new Ecriture();
            $entityManager = $this->entityManager;

            $banque = $entityManager->getRepository(Banque::class)->find($request->get('banque'));
            $accompte = $entityManager->getRepository(Accompte::class)->find($request->get('accompte'));
           

            $montant = $solde->montantbanque($entityManager, $banque->getCompte());

            if ($accompte->getMontant() <= $montant) {

            // $depenseNet = new Depense();
            // $depenseNet->setUser($this->getUser());
            // $depenseNet->setType("Banque");
            // $depenseNet->setLibelle("Accompte sur Salaire");
            // $depenseNet->setMontant($accompte->getMontant());
            // $depenseNet->setStatut("Effectuée");
            // $depenseNet->setCompte("421100");

            $debit->setCompte($banque->getCompte());
            $debit->setType('Banque');
            // $debit->setDepense($depenseNet);
            $debit->setMontant($accompte->getMontant());


            $ecriture->setType('Banque');
            $ecriture->setComptecredit("421100");
            $ecriture->setLibellecomptecredit("Personnel Avances");
            $ecriture->setComptedebit($banque->getCompte());
            $ecriture->setLibellecomptedebit($banque->getNom());
            $ecriture->setDebit($debit);
            $ecriture->setSolde(-$accompte->getMontant());
            $ecriture->setMontant($accompte->getMontant());
            $ecriture->setLibelle("Accompte sur salaire ".$accompte->getEmploye()->getNom(). " ".$accompte->getEmploye()->getPrenom());

            $accompte->setVerser(true);
            $entityManager->persist($accompte);

            // $entityManager->persist($depenseNet);
            $entityManager->persist($debit);
            $entityManager->persist($ecriture);
            $entityManager->flush();
                $this->addFlash('notice', 'Paiement effectué avec succès');

//                    return $this->redirectToRoute('depense_index', [], Response::HTTP_SEE_OTHER);
                } else {
                    $this->addFlash('danger', 'Montant non disponible');
                }




            $res['id'] = 'ok';


            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
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
    
    #[Route("/CompteResultat", name :"Compteresultat", methods : ["POST", "GET"]) ]
    public function CompteResultat(Amortissement $amortis, Request $request, CommandeRepository $repository, CommandeProduitRepository $CommandeProduitRepository, ApprovisionnementRepository $Approrepository, PaieRepository $paieRepository): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $annee = date('Y');
            $commandes = $repository->Annuelle($annee);// achat deja paye
            $commandespassee = $repository->Annuelle($annee-1);// achat deja paye
            $credits = $repository->credits($annee);// achat a credit deja livree
            $creditspassee = $repository->credits($annee-1);// achat a credit deja livree
            // $creditavances = $repository->creditAvance();// achat a credit non livrer avec avance recu
            $vente = 0;
            $ventepassee = 0;
            $achat = 0;
            $achatpassee = 0;
            $reapprro = 0;
            $reapprropassee = 0;
            $provision = 0;
            $provisionpassee = 0;
            $chargepersonnel = 0;
            $chargepersonnelpassee = 0;
            $charge = 0;
            $chargepassee = 0;
            $amortissement = 0;
            $amortissementpassee = 0;
            $transport = 0;
            $transportpassee = 0;
            $servicesexternes = 0;
            $servicesexternespassee = 0;
            $autrescharges = 0;
            $autreschargespassee = 0;
            $impotettaxe = 0;
            $impotettaxepassee = 0;
            $autresachats = 0;
            $autresachatspassee = 0;

            $approvisionnements = $Approrepository->resultat($annee);
            $approvisionnementspassee = $Approrepository->resultat($annee-1);

            // variation stock produit
            
            $repport = $this->entityManager->getRepository(Repport::class)->findOneBy(['annee' => date('Y')-1]);
            $repport !== null ? $stockinitial = $repport->getStockfinal() : $stockinitial = 0;
            $stockfinal = 0;// a definir
            $stocks = $this->entityManager->getRepository(Stock::class)->findAll();
            foreach($stocks as $stock){
                //definition stockfinal
                $stockfinal += $stock->getQuantite() * $stock->getProduit()->getPght();
                //$approvs = $Approrepository->findBy(['produit' => $stock->getProduit()->getId()]);

            }
            $variation = $stockfinal - $stockinitial;
            $repport !== null ? $variationpassee = $repport->getStockfinal() - $repport->getStockinitial() : $variationpassee = 0;
            
            //fin variation

            foreach($approvisionnements as $approvisionnement){
              // achat annuel 
               // $reapprro = $reapprro + $approvisionnement->getQuantite() * $approvisionnement->getPght();
                $achat = $achat  + $approvisionnement->getQuantite() * $approvisionnement->getPght();
                
            }
             foreach($approvisionnementspassee as $approvisionnement){
              // achat annuel 
               // $reapprro = $reapprro + $approvisionnement->getQuantite() * $approvisionnement->getPght();
                $achatpassee = $achatpassee  + $approvisionnement->getQuantite() * $approvisionnement->getPght();
                
            }

            

            foreach($commandes as $commande){
                if($commande->getLivraison() == true){
                $vente = $vente + $commande->getMontant() - $commande->getAcompte();
                }
              /*  else{// commande non livrer
                    $commandeproduits = $this->entityManager->getRepository(CommandeProduit::class)->findBy(['commande' => $commande->getId()]);
                    foreach($commandeproduits as $commandeproduit){
                        $stoc = $this->entityManager->getRepository(Stock::class)->findOneBy(['produit' => $commandeproduit->getProduit()->getId()]);
                        if(!empty($stoc)){
                            $quant = $stoc->getQuantite() - $commandeproduit->getQuantite();
                            if($quant < 0){
                                // $autreschrages += -1 * $commandeproduit->getSession() * $quant;// -1 * la quatite 
                            }
                        }else{
                            // $autreschrages += $commandeproduit->getQuantite() * $commandeproduit->getSession();
                        }
                    }
                    
                }*/
            } 
            foreach($commandespassee as $commande){
                if($commande->getLivraison() == true){
                $ventepassee = $ventepassee + $commande->getMontant() - $commande->getAcompte();
                }
             
            }          

            foreach($credits as $credit){// ne tient pas compte des versement
                $rest = 0;
                $restes = $this->entityManager->getRepository(LivrerReste::class)->findby(['commande' => $credit]);
                foreach($restes as $reste){
                    $rest += ($reste->getQuantite()- $reste->getQuantitelivre()) * $reste->getSession();
                }
             
                $vente = $vente + $credit->getMontant() - $rest - $credit->getAcompte() - $commande->getEscompte();
                
            } 

            foreach($creditspassee as $credit){// ne tient pas compte des versement
                $rest = 0;
                $restes = $this->entityManager->getRepository(LivrerReste::class)->findby(['commande' => $credit]);
                foreach($restes as $reste){
                    $rest += ($reste->getQuantite()- $reste->getQuantitelivre()) * $reste->getSession();
                }
             
                $ventepassee = $ventepassee + $credit->getMontant() - $rest - $credit->getAcompte() - $commande->getEscompte();
                
            }          

            // foreach($creditavances as $creditavance){// credit avec avance sans livraison
               
             
            //     $charge += $creditavance->getVersement();
                
            // }
            

            $restes = $this->entityManager->getRepository(LivrerReste::class)->Annuelle($annee);
            foreach($restes as $reste){// on enleve les reste a livrer sur le montant des vente 
                // $stoc = $this->entityManager->getRepository(Stock::class)->find($reste->getProduit());
                $restelivrer = $reste->getQuantite() - $reste->getQuantitelivre();
                $tva = 0;
                // if(!empty($stoc)){
                //     $quant = $stoc->getQuantite() - $restelivrer;
                //     if($quant < 0){
                //         $reste->getTva() == true ? $tva = $restelivrer * 0.1925: null;
                //         $vente -= -1 * $reste->getSession() * $quant + $tva;// -1 * la quatite 
                //     }
                // }else{
                    $reste->getTva() == true ? $tva = $restelivrer * 0.1925: null;
                    $vente -= $restelivrer * $reste->getSession() + $tva;
                // } 
            }
            $restespassee = $this->entityManager->getRepository(LivrerReste::class)->Annuelle($annee-1);
            foreach($restespassee as $reste){// on enleve les reste a livrer sur le montant des vente 
                // $stoc = $this->entityManager->getRepository(Stock::class)->find($reste->getProduit());
                $restelivrer = $reste->getQuantite() - $reste->getQuantitelivre();
                $tva = 0;

                    $reste->getTva() == true ? $tva = $restelivrer * 0.1925: null;
                    $ventepassee -= $restelivrer * $reste->getSession() + $tva;
            }
            
            $avoirs = $this->entityManager->getRepository(Avoir::class)->Annuelle($annee);
            foreach($avoirs as $avoir){    
                $vente -= $avoir->getMontant(); 
            }
            
            $avoirspassee = $this->entityManager->getRepository(Avoir::class)->Annuellepassee($annee);
            foreach($avoirspassee as $avoir){    
                $ventepassee -= $avoir->getMontant(); 
            }
            $autrescharges = $autrescharges - $autreschargespassee;// on enleve les charges des annees passees

            // $credits = $repository->findBy(['paiement' => null, 'credit' => true, 'suivi' => true, 'payer' => false]);// achat a credit
            // foreach($credits as $commande){
            //     $vente = $vente + $commande->getMontant();
            //     $commandesproduits = $Produitrepository->findBy(['commande' => $commande->getid()]);
            //     foreach($commandesproduits as $commandeproduit){
            //         $achat = $achat + $commandeproduit->getQuantite() * $commandeproduit->getPght();
            //     }
            // }


            //charge personnel
            $salaires = $paieRepository->compteResultat($annee);
            foreach($salaires as $salaire){
                $chargepersonnel = $chargepersonnel + $salaire->getSalaireNet() + $salaire->getTotalchargepatronal() + $salaire->getTotalChargeEmploye();

                
            }

            $salairespassee = $paieRepository->compteResultat($annee-1);
            foreach($salairespassee as $salaire){
                $chargepersonnelpassee = $chargepersonnelpassee + $salaire->getSalaireNet() + $salaire->getTotalchargepatronal() + $salaire->getTotalChargeEmploye();

                
            }

            // interet pret bancaire
            $interet = 0;
             $prets = $this->entityManager->getRepository(Financement::class)->findBy(['rembourser' => false, 'apport' => false]);
             foreach($prets as $pret){
                

                $interet = $interet + ((($pret->getMontant() * $pret->getTaux()/100) / $pret->getDuree()) * count($pret->getRemboursements()));    
            }
            $interetpassee = 0;
            //  $pretpassees = $this->entityManager->getRepository(Financement::class)->findBy(['rembourser' => false, 'apport' => false]);
            //  foreach($pretspassee as $pret){
                

            //     $interetpassee = $interetpassee + ((($pret->getMontant() * $pret->getTaux()/100) / $pret->getDuree()) * count($pret->getRemboursements()));    
            // }
            
            // amortissement et charge
            

            $depenses = $this->entityManager->getRepository(Depense::class)->immobilisation();
            
                foreach($depenses as $depense){

                    $lignedepense = $depense->getCategorie();

                    
                    if (substr($lignedepense->getCompte(), 0, 2) === "22" ) {

                       
                        $lignedepense->getAmortissement() != null ? $amortissement += $amortis->amortissement($depense) : null;
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 3) === "211" 
                            || substr($lignedepense->getCompte(), 0, 4) === "2181"
                            || substr($lignedepense->getCompte(), 0, 4) === "2191") {

                        $lignedepense->getAmortissement() != null ? $amortissement += $amortis->amortissement($depense) : null ;    
                       
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 3) === "212" 
                            || substr($lignedepense->getCompte(), 0, 3) === "213"
                            || substr($lignedepense->getCompte(), 0, 3) === "214"
                            || substr($lignedepense->getCompte(), 0, 4) === "2193") {

                        $lignedepense->getAmortissement() != null ? $amortissement += $amortis->amortissement($depense) : null;    
                       
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 3) === "215"
                             ||   substr($lignedepense->getCompte(), 0, 3) === "216") {

                        $lignedepense->getAmortissement() != null ? $amortissement += $amortis->amortissement($depense) : null;    
                       
                    
                    } elseif ((substr($lignedepense->getCompte(), 0, 3) === "217"
                             ||   substr($lignedepense->getCompte(), 0, 3) === "218"
                             ||   substr($lignedepense->getCompte(), 0, 4) === "2198")
                             &&   substr($lignedepense->getCompte(), 0, 4) !== "2181") {

                        $lignedepense->getAmortissement() != null ? $amortissement += $amortis->amortissement($depense) : null ;    
                       
                    
                    }elseif (substr($lignedepense->getCompte(), 0, 3) === "231" 
                            || substr($lignedepense->getCompte(), 0, 3) === "232"
                            || substr($lignedepense->getCompte(), 0, 3) === "233"
                            || substr($lignedepense->getCompte(), 0, 3) === "237"
                            || substr($lignedepense->getCompte(), 0, 4) === "2391") {

                        $lignedepense->getAmortissement() != null ? $amortissement += $amortis->amortissement($depense) : null ;    
                       
                    
                    }elseif (substr($lignedepense->getCompte(), 0, 3) === "234" 
                            || substr($lignedepense->getCompte(), 0, 3) === "235"
                            || substr($lignedepense->getCompte(), 0, 3) === "238"
                            || substr($lignedepense->getCompte(), 0, 4) === "2392"
                            || substr($lignedepense->getCompte(), 0, 4) === "2393") {

                        $lignedepense->getAmortissement() != null ? $amortissement += $amortis->amortissement($depense) : null ;    
                       
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 3) === "245"
                              || substr($lignedepense->getCompte(), 0, 4) === "2495") {
                                

                        $lignedepense->getAmortissement() != null ? $amortissement += $amortis->amortissement($depense) : null;    
                       
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "24"
                             &&   substr($lignedepense->getCompte(), 0, 3) !== "245"
                             &&   substr($lignedepense->getCompte(), 0, 4) !== "2495") {
                              
                        $lignedepense->getAmortissement() != null ? $amortissement += $amortis->amortissement($depense) : null ;    
                       
                    
                    }elseif (substr($lignedepense->getCompte(), 0, 3) === "251"
                              || substr($lignedepense->getCompte(), 0, 3) === "252") {

                        $lignedepense->getAmortissement() != null ? $amortissement += $amortissement->amortissement($depense) : null;    
                        
                    
                    }elseif (substr($lignedepense->getCompte(), 0, 2) === "26") {

                        $lignedepense->getAmortissement() != null ? $amortissement += $amortissement->amortissement($depense) : null ;    
                       
                    
                    }elseif (substr($lignedepense->getCompte(), 0, 2) === "27") {

                        $lignedepense->getAmortissement() != null ? $amortissement += $amortissement->amortissement($depense) : null ;    
                       
                    
                    }

                 }  
                
                 $depenses = $this->entityManager->getRepository(Depense::class)->compteResultat($annee);
            
                foreach($depenses as $depense){
                      $lignedepense = $depense->getCategorie();


                    if (substr($lignedepense->getCompte(), 0, 2) === "22" ) {

                    //terrain pas amortissement
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 3) === "604" 
                    || substr($lignedepense->getCompte(), 0, 3) === "605" 
                    || substr($lignedepense->getCompte(), 0, 3) === "608") {

                        $autresachats += $depense->getMontant(); 
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "62"
                    || substr($lignedepense->getCompte(), 0, 2) === "63") {

                        $servicesexternes += $depense->getMontant(); 
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "61") {

                        $transport += $depense->getMontant(); 
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "65") {

                        $autreschrages += $depense->getMontant(); 
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "64") {

                        $impotettaxe += $depense->getMontant(); 
                
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "67") {
                        $charge += $depense->getMontant(); 
                    }elseif (substr($lignedepense->getCompte(), 0, 3) === "697") {
                        $provision += $depense->getMontant(); 
                    }
                    
                    

                      
                }
             $depensespassee = $this->entityManager->getRepository(Depense::class)->compteResultat($annee-1);
            
                foreach($depensespassee as $depense){
                      $lignedepense = $depense->getCategorie();


                    if (substr($lignedepense->getCompte(), 0, 2) === "22" ) {

                    //terrain pas amortissement
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 3) === "604" 
                    || substr($lignedepense->getCompte(), 0, 3) === "605" 
                    || substr($lignedepense->getCompte(), 0, 3) === "608") {

                        $autresachatspassee += $depense->getMontant(); 
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "62"
                    || substr($lignedepense->getCompte(), 0, 2) === "63") {

                        $servicesexternespassee += $depense->getMontant(); 
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "61") {

                        $transportpassee += $depense->getMontant(); 
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "65") {

                        $autreschragespassee += $depense->getMontant(); 
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "64") {

                        $impotettaxepassee += $depense->getMontant(); 
                
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "67") {
                        $chargepassee += $depense->getMontant(); 
                    }elseif (substr($lignedepense->getCompte(), 0, 3) === "697") {
                        $provisionpassee += $depense->getMontant(); 
                    }
                    
                    

                      
                }
            
           

            $response = $this->render('finance/resultat.html.twig',[
                'vente' => $vente,
                'ventepassee' => $ventepassee,
                'achat' => $achat,
                'achatpassee' => $achatpassee,
                'transport' => $transport,
                'transportpassee' => $transportpassee,
                'provision' => $provision,
                'provisionpassee' => $provisionpassee,
                'chargepersonnel' => $chargepersonnel,
                'chargepersonnelpassee' => $chargepersonnelpassee,
                'interet' => $interet,
                'interetpassee' => $interetpassee,
                'amortissement' => $amortissement,
                'amortissementpassee' => $amortissementpassee,
                'variation' => $variation,
                'variationpassee' => $variationpassee,
                'charge' => $charge,
                'chargepassee' => $chargepassee,
                'servicesexternes' => $servicesexternes,
                'servicesexternespassee' => $servicesexternespassee,
                'autrescharges' => $autrescharges,
                'autreschargespassee' => $autreschargespassee,
                'impotettaxe' => $impotettaxe,
                'impotettaxepassee' => $impotettaxepassee,
                'autresachats' => $autresachats,
                'autresachatspassee' => $autresachatspassee,
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

    #[Route("/Bilan", name :"bilan", methods : ["POST", "GET"]) ]
    public function bilan(Amortissement $amortis, Solde $solde, Request $request, EcritureRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $annee = date('Y');
            
            $ecritures = $repository->findAll();
            $caisse = 0;
            $banque = 0;
            $debitbanque = 0;
            $debitcaisse = 0;
            foreach ($ecritures as $ecriture) {
                if($ecriture->getType() != null){
                    $credit = null;
                    $debit = null;
                    if ($ecriture->getCredit() != null) {
                        $credit = $ecriture->getCredit();
                        $credit->getType() == 'Espece' ?
                            $caisse = $caisse + $credit->getMontant() :
                            $banque = $banque + $credit->getMontant();
                    } else {

                        $debit = $ecriture->getDebit();
                        $debit->getType() == 'Espece' ?
                            $debitcaisse = $debitcaisse + $debit->getMontant() :
                            $debitbanque = $debitbanque + $debit->getMontant();

                    }
                }

            }

            $ecriturespassee = $repository->Annuelle($annee-1);
            $caissepassee = 0;
            $banquepassee = 0;
            $debitbanquepassee = 0;
            $debitcaissepassee = 0;
            foreach ($ecriturespassee as $ecriture) {
                if($ecriture->getType() != null){
                    $credit = null;
                    $debit = null;
                    if ($ecriture->getCredit() != null) {
                        $credit = $ecriture->getCredit();
                        $credit->getType() == 'Espece' ?
                            $caissepassee = $caissepassee + $credit->getMontant() :
                            $banquepassee = $banquepassee + $credit->getMontant();
                    } else {

                        $debit = $ecriture->getDebit();
                        $debit->getType() == 'Espece' ?
                            $debitcaissepassee = $debitcaissepassee + $debit->getMontant() :
                            $debitbanquepassee = $debitbanquepassee + $debit->getMontant();

                    }
                }

            }

            $stock = 0;
            $repport = $this->entityManager->getRepository(Repport::class)->findOneBy(['annee' => $annee - 1]);
            $repport !== null ? $stockpassee = $repport->getStockfinal() : $stockpassee = 0;
            //$stockpassee = 0;
            $stocks = $this->entityManager->getRepository(Stock::class)->findAll();
            foreach($stocks as $sto){
                $stock += $sto->getQuantite() * $sto->getProduit()->getPght();
                //$approvs = $Approrepository->findBy(['produit' => $stock->getProduit()->getId()]);

            }
            // $stock = $stockfinal - $stockinitial;

           $solde = ($caisse - $debitcaisse) + ($banque - $debitbanque);
           $soldepassee = ($caissepassee - $debitcaissepassee) + ($banquepassee - $debitbanquepassee);
            // amortissement
            $terrain  = 0;
            $amortterrain  = 0;
            $brevet = 0;
            $titre = 0;
            $hao = 0;
            $placement = 0;
            $encaisser = 0;
            $ecartactif = 0;
            $autrecreance = 0;
            $autrecreancepassee = 0;
            $avancefournisseur = 0;
            $avancefournisseurpassee = 0;
            $autresimmofin = 0;
            $avanceimmo = 0;
            $amortbrevet = 0;
            $amorttitre = 0;
            $amorthao = 0;
            $amortautresimmofin = 0;
            $amortavanceimmo = 0;
            $autresincorp = 0;
            $amortautresincorp = 0;
            $developpement = 0;
            $amortdeveloppement = 0;
            $mobilier = 0;
            $amortmobilier = 0;
            $fond = 0;
            $amortfond = 0;
            $batiment = 0;
            $amortbatiment = 0;
            $amenagement = 0;
            $amortamenagement = 0;
            $transport = 0;
            $amorttransport = 0;
           
            $depenses = $this->entityManager->getRepository(Depense::class)->immobilisation();
            foreach($depenses as $depense){
                
                    $lignedepense = $depense->getCategorie();
                    if (substr($lignedepense->getCompte(), 0, 2) === "22" ) {

                        $terrain += $depense->getMontant(); 
                        $lignedepense->getAmortissement() != null ? $amortterrain += $amortis->amortissement($depense) : null;
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 3) === "211" 
                            || substr($lignedepense->getCompte(), 0, 4) === "2181"
                            || substr($lignedepense->getCompte(), 0, 4) === "2191") {

                        $lignedepense->getAmortissement() != null ? $amortdeveloppement += $amortis->amortissement($depense) : null ;    
                        $developpement += $depense->getMontant(); 
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 3) === "212" 
                            || substr($lignedepense->getCompte(), 0, 3) === "213"
                            || substr($lignedepense->getCompte(), 0, 3) === "214"
                            || substr($lignedepense->getCompte(), 0, 4) === "2193") {

                        $lignedepense->getAmortissement() != null ? $amortbrevet += $amortis->amortissement($depense) : null ;    
                        $brevet += $depense->getMontant(); 
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 3) === "215"
                             ||   substr($lignedepense->getCompte(), 0, 3) === "216") {

                        $lignedepense->getAmortissement() != null ? $amortfond += $amortis->amortissement($depense) : null;    
                        $fond += $depense->getMontant(); 
                    
                    } elseif ((substr($lignedepense->getCompte(), 0, 3) === "217"
                             ||   substr($lignedepense->getCompte(), 0, 3) === "218"
                             ||   substr($lignedepense->getCompte(), 0, 4) === "2198")
                             &&   substr($lignedepense->getCompte(), 0, 4) !== "2181") {

                        $lignedepense->getAmortissement() != null ? $amortautresincorp += $amortis->amortissement($depense) : null ;    
                        $autresincorp += $depense->getMontant(); 
                    
                    }elseif (substr($lignedepense->getCompte(), 0, 3) === "231" 
                            || substr($lignedepense->getCompte(), 0, 3) === "232"
                            || substr($lignedepense->getCompte(), 0, 3) === "233"
                            || substr($lignedepense->getCompte(), 0, 3) === "237"
                            || substr($lignedepense->getCompte(), 0, 4) === "2391") {

                        $lignedepense->getAmortissement() != null ? $amortbatiment += $amortis->amortissement($depense) : null ;    
                        $batiment += $depense->getMontant(); 
                    
                    }elseif (substr($lignedepense->getCompte(), 0, 3) === "234" 
                            || substr($lignedepense->getCompte(), 0, 3) === "235"
                            || substr($lignedepense->getCompte(), 0, 3) === "238"
                            || substr($lignedepense->getCompte(), 0, 4) === "2392"
                            || substr($lignedepense->getCompte(), 0, 4) === "2393") {

                        $lignedepense->getAmortissement() != null ? $amortamenagement += $amortis->amortissement($depense) : null ;    
                        $amenagement += $depense->getMontant(); 
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "24"
                             &&   substr($lignedepense->getCompte(), 0, 3) !== "245"
                             &&   substr($lignedepense->getCompte(), 0, 4) !== "2495") {

                        $lignedepense->getAmortissement() != null ? $amortmobilier += $amortis->amortissement($depense) : null ;    
                        $mobilier += $depense->getMontant(); 
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 3) === "245"
                              || substr($lignedepense->getCompte(), 0, 4) === "2495") {

                        $lignedepense->getAmortissement() != null ? $amorttransport += $amortis->amortissement($depense) : null;    
                        $transport += $depense->getMontant(); 
                    
                    }elseif (substr($lignedepense->getCompte(), 0, 3) === "251"
                              || substr($lignedepense->getCompte(), 0, 3) === "252") {

                        $lignedepense->getAmortissement() != null ? $amortavanceimmo += $amortis->amortissement($depense) : null;    
                        $avanceimmo += $depense->getMontant(); 
                    
                    }elseif (substr($lignedepense->getCompte(), 0, 2) === "26") {

                        $lignedepense->getAmortissement() != null ? $amorttitre += $amortis->amortissement($depense) : null ;    
                        $titre += $depense->getMontant(); 
                    
                    }elseif (substr($lignedepense->getCompte(), 0, 2) === "27") {

                        $lignedepense->getAmortissement() != null ? $amortautresimmofin += $amortis->amortissement($depense) : null ;    
                        $autresimmofin += $depense->getMontant(); 
                    
                    }
                }

                 $depenses = $this->entityManager->getRepository(Depense::class)->compteResultat($annee);
            
                foreach($depenses as $depense){
                      $lignedepense = $depense->getCategorie();


                    if (substr($lignedepense->getCompte(), 0, 2) === "22" ) {

                    //terrain pas amortissement
                    
                    }elseif (substr($lignedepense->getCompte(), 0, 3) === "485"
                            || substr($lignedepense->getCompte(), 0, 3) === "488") {

                         
                        $hao += $depense->getMontant(); 
                    
                    }elseif (substr($lignedepense->getCompte(), 0, 3) === "409") {

                         
                        $avancefournisseur += $depense->getMontant(); 
                    
                    }elseif ((substr($lignedepense->getCompte(), 0, 3) === "185"
                            || substr($lignedepense->getCompte(), 0, 2) === "42"
                            || substr($lignedepense->getCompte(), 0, 2) === "43"
                            || substr($lignedepense->getCompte(), 0, 2) === "44"
                            || substr($lignedepense->getCompte(), 0, 2) === "45"
                            || substr($lignedepense->getCompte(), 0, 2) === "46"
                            || substr($lignedepense->getCompte(), 0, 2) === "47")
                            && substr($lignedepense->getCompte(), 0, 3) !== "478") {

                         
                        $autrecreance += $depense->getMontant(); 
                    
                    }elseif (substr($lignedepense->getCompte(), 0, 2) === "50") {

                         
                        $placement += $depense->getMontant(); 
                    
                    }elseif (substr($lignedepense->getCompte(), 0, 2) === "51") {

                         
                        $encaisser += $depense->getMontant(); 
                    
                    }elseif (substr($lignedepense->getCompte(), 0, 3) === "478") {

                         
                        $ecartactif += $depense->getMontant(); 
                    
                    }
                
                
                
            }
            

            $detteavoir = 0;
            // $avoirs = $this->entityManager->getRepository(Avoir::class)->findBy(['rebourser' => false]);
            // foreach($avoirs as $avoir){    
            //     $detteavoir += $avoir->getMontant(); 
            // }
            
            $detteavoirpassee = 0;
            // $avoirspassee = $this->entityManager->getRepository(Avoir::class)->Annuellepassee($annee);// 
            // foreach($avoirspassee as $avoir){    
            //     $detteavoirpassee += $avoir->getMontant(); 
            // }

            // capital
            $capital = 0;
             $prets = $this->entityManager->getRepository(Financement::class)->findBy(['apport' => true]);
             foreach($prets as $pret){
                

                $capital = $capital + $pret->getMontant() ;    
            }
            
             // interet pret bancaire
             $montantpret = 0;
             $remboursement = 0;
             $prets = $this->entityManager->getRepository(Financement::class)->findBy(['rembourser' => false, 'apport' => false]);
             foreach($prets as $pret){
                $montantpret += $pret->getMontant();
                if($pret->getRemboursements() != null){
                    foreach ($pret->getRemboursements() as $rembours) {
                        $remboursement = $remboursement + $rembours->getMontant();
                        }
                }   
            }

            // avance client
            $avanceclient = 0;
            $commandes = $this->entityManager->getRepository(Commande::class)->avances($annee);// achat deja paye
            foreach($commandes as $commande){
                  $avanceclient += $commande->getMontant();
               
              /*      $commandeproduits = $this->entityManager->getRepository(CommandeProduit::class)->findBy(['commande' => $commande->getId()]);
                    foreach($commandeproduits as $commandeproduit){
                        $stoc = $this->entityManager->getRepository(Stock::class)->findOneBy(['produit' => $commandeproduit->getProduit()->getId()]);
                        if(!empty($stoc)){
                            $quant = $stoc->getQuantite() - $commandeproduit->getQuantite();
                            if($quant < 0){
                                $avanceclient += -1 * $commandeproduit->getSession() * $quant;// -1 * la quatite 
                            }else{
                                $avanceclient += $commandeproduit->getQuantite() * $commandeproduit->getSession();
                            }
                        }else{
                            $avanceclient += $commandeproduit->getQuantite() * $commandeproduit->getSession();
                        }
                    }*/
                    
                
            }
            
            $avanceclientpassee = 0;
            $commandespassee = $this->entityManager->getRepository(Commande::class)->avances($annee-1);// achat deja paye
            foreach($commandespassee as $commande){
                  $avanceclientpassee += $commande->getMontant();   
            }
            

            // $restes = $this->entityManager->getRepository(LivrerReste::class)->Annuelle($annee);
            // foreach($restes as $reste){    
            // //    $stoc = $this->entityManager->getRepository(Stock::class)->find($reste->getProduit());
            //     $restelivrer = $reste->getQuantite() - $reste->getQuantitelivre();
            //     $tva = 0;
            //     // if(!empty($stoc)){
            //     //     $quant = $stoc->getQuantite() - $restelivrer;
            //     //     if($quant < 0){
            //     //         $reste->getTva() == true ? $tva = $restelivrer * 0.1925: null;
            //     //         $avanceclient += -1 * $reste->getSession() * $quant + $tva;// -1 * la quatite 
            //     //     }
            //     // }else{
            //         $reste->getTva() == true ? $tva = $restelivrer * 0.1925: null;
            //         $avanceclient += $restelivrer * $reste->getSession() + $tva;
            //     // }    
            // }


            $creditavances = $this->entityManager->getRepository(Commande::class)->creditAvance($annee);// achat a credit non livrer avec avance recu
            foreach($creditavances as $creditavance){
               
             
                $avanceclient += $creditavance->getVersement();
                
            }

             $creditavancespassee = $this->entityManager->getRepository(Commande::class)->creditAvance($annee-1);// achat a credit non livrer avec avance recu
            foreach($creditavancespassee as $creditavance){
               
             
                $avanceclientpassee += $creditavance->getVersement();
                
            }
            // fin avance client

            // creaces clients
            $creance= 0;
            $credits = $this->entityManager->getRepository(Commande::class)->findBy(['suivi' => true, 'payer' => false, 'livraison' => true, 'credit' => true ]);// achat a credit
            foreach($credits as $credit){
            //    $rest = 0;
            //     $restes = $this->entityManager->getRepository(LivrerReste::class)->findby(['commande' => $credit]);
            //     foreach($restes as $reste){
            //         $rest += ($reste->getQuantite()- $reste->getQuantitelivre()) * $reste->getSession();
            //     }
                // $creance = $creance + $credit->getMontant() - $credit->getVersement() - $rest;
                $creance = $creance + $credit->getMontant() - $credit->getVersement();
                
            }
            
            $creancepassee= 0;
            $creditspassee = $this->entityManager->getRepository(Commande::class)->credits($annee-1);// achat a credit
            foreach($creditspassee as $credit){
               
                $creancepassee = $creancepassee + $credit->getMontant() - $credit->getVersement();
                
            }

            // dtte fournisseur
            $dettefournisseur = 0;
            $factures = $this->entityManager->getRepository(Facture::class)->findBy(['payer' => false]);
            foreach($factures as $facture){    
               
                    $dettefournisseur += $facture->getMontant();
                   
            }
            
            $dettefournisseurpassee = 0;
            $facturespassee = $this->entityManager->getRepository(Facture::class)->Annuelle($annee-1);
            foreach($facturespassee as $facture){    
               
                    $dettefournisseurpassee += $facture->getMontant();
                   
            }

            //repport $
            $rep = 0;
          $repp = $this->entityManager->getRepository(Repport::class)->findOneBy(['annee' => $annee - 1]);
           $repp !== null ?  $rep = $repp->getMontant() : null;
            $rep_passee = 0;
          $repp = $this->entityManager->getRepository(Repport::class)->findOneBy(['annee' => $annee - 2]);
           $repp !== null ?  $rep_passee = $repp->getMontant() : null;

            $response = $this->render('finance/bilan.html.twig',[
              
                'detteavoir' => $detteavoir,
                'detteavoirpassee' => $detteavoirpassee,
                'solde' => $solde,
                'soldepassee' => $soldepassee,
                'stock' => $stock,
                'stockpassee' => $stockpassee,
                'capital' => $capital,
                'pret' => $montantpret - $remboursement,
                'avanceclient' => $avanceclient,
                'avanceclientpassee' => $avanceclientpassee,
                'creance' => $creance,
                'creancepassee' => $creancepassee,
                'dettefournisseur' => $dettefournisseur,
                'dettefournisseurpassee' => $dettefournisseurpassee,
                'terrain' => $terrain,
                'mobilier' => $mobilier,
                'transport' => $transport,
                'brevet' => $brevet,
                'batiment' => $batiment,
                'amenagement' => $amenagement,
                'fond' => $fond,
                'hao' => $hao,
                'placement' => $placement,
                'encaisser' => $encaisser,
                'ecartactif' => $ecartactif,
                'autrecreance' => $autrecreance,
                'autrecreancepassee' => $autrecreancepassee,
                'avancefournisseur' => $avancefournisseur,
                'avancefournisseurpassee' => $avancefournisseurpassee,
                'autresimmofin' => $autresimmofin,
                'titre' => $titre,
                'avanceimmo' => $avanceimmo,
                'autresincorp' => $autresincorp,
                'developpement' => $developpement,
                'amortmobilier' => $amortmobilier,
                'amortterrain' => $amortterrain,
                'amorttransport' => $amorttransport,
                'amortbrevet' => $amortbrevet,
                'amortbatiment' => $amortbatiment,
                'amortamenagement' => $amortamenagement,
                'amortfond' => $amortfond,
                'amortautresimmofin' => $amortautresimmofin,
                'amorttitre' => $amorttitre,
                'amortavanceimmo' => $amortavanceimmo,
                'amortautresincorp' => $amortautresincorp,
                'amortdeveloppement' => $amortdeveloppement,
                'resultat' => $this->ResultatInterne(),
                'repport' => $rep,
                'repportpassee' => $rep_passee,
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

    public function ResultatInterne()
    {
        
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $annee = date('Y');
            $commandes = $this->entityManager->getRepository(Commande::class)->Annuelle($annee);// achat deja paye
            $commandespassee = $this->entityManager->getRepository(Commande::class)->Annuelle($annee-1);// achat deja paye
            $credits = $this->entityManager->getRepository(Commande::class)->credits($annee);// achat a credit
            $creditspassee = $this->entityManager->getRepository(Commande::class)->credits($annee-1);// achat a credit
            // $creditavances = $this->entityManager->getRepository(Commande::class)->creditAvance();// achat a credit non livrer avec avance recu
            $vente = 0;
            $ventepassee = 0;
            $achat = 0;
            $achatpassee = 0;
            $reapprro = 0;
            $reapprropassee = 0;
            $chargepersonnel= 0;
            $chargepersonnelpassee = 0;
            $charge = 0;
            $chargepassee = 0;
            $interet = 0;
            $interetpassee = 0;
            $amortissementpassee = 0;
            $approvisionnements = $this->entityManager->getRepository(Approvisionnement::class)->resultat($annee);
            $approvisionnementspassee = $this->entityManager->getRepository(Approvisionnement::class)->resultat($annee-1);

            // variation stock produit
             $repport = $this->entityManager->getRepository(Repport::class)->findOneBy(['annee' => date('Y')-1]);
            $repport !== null ?  $stockinitial = $repport->getStockfinal() : $stockinitial = 0;
            $stockfinal = 0;// a definir
            $stocks = $this->entityManager->getRepository(Stock::class)->findAll();
            foreach($stocks as $stock){
                //definition stockfinal
                $stockfinal += $stock->getQuantite() * $stock->getProduit()->getPght();
                //$approvs = $Approrepository->findBy(['produit' => $stock->getProduit()->getId()]);

            }
            $variation = $stockfinal - $stockinitial;
            $variationpassee = 0;
            $repport !== null ?  $variationpassee = $repport->getStockfinal() - $repport->getStockinitial() : $stockinitial = 0;
            

            foreach($approvisionnements as $approvisionnement){
              // achat  
               // $reapprro = $reapprro + $approvisionnement->getQuantite() * $approvisionnement->getPght();
                $achat = $achat  + $approvisionnement->getQuantite() * $approvisionnement->getPght();
                
            }
             foreach($approvisionnementspassee as $approvisionnement){
              // achat  
               // $reapprro = $reapprro + $approvisionnement->getQuantite() * $approvisionnement->getPght();
                $achatpassee = $achatpassee  + $approvisionnement->getQuantite() * $approvisionnement->getPght();
                
            }

            foreach($commandes as $commande){
                if($commande->getLivraison() == true){
                $vente = $vente + $commande->getMontant() - $commande->getAcompte();
                }
              /*  else{// commande non livrer
                    $commandeproduits = $this->entityManager->getRepository(CommandeProduit::class)->findBy(['commande' => $commande->getId()]);
                    foreach($commandeproduits as $commandeproduit){
                        $stoc = $this->entityManager->getRepository(Stock::class)->findOneBy(['produit' => $commandeproduit->getProduit()->getId()]);
                        if(!empty($stoc)){
                            $quant = $stoc->getQuantite() - $commandeproduit->getQuantite();
                            if($quant < 0){
                                $charge += -1 * $commandeproduit->getSession() * $quant;// -1 * la quatite 
                            }
                        }else{
                            $charge += $commandeproduit->getQuantite() * $commandeproduit->getSession();
                        }
                    }
                    
                }*/
            } 
            
            foreach($commandespassee as $commande){
                if($commande->getLivraison() == true){
                $ventepassee = $ventepassee + $commande->getMontant() - $commande->getAcompte();
                }
            } 

            foreach($credits as $credit){// ne tient pas compte des versemet effectue sur le credit
               
                $rest = 0;
                $restes = $this->entityManager->getRepository(LivrerReste::class)->findby(['commande' => $credit]);
                foreach($restes as $reste){
                    $rest += ($reste->getQuantite()- $reste->getQuantitelivre()) * $reste->getSession();
                }
                $vente = $vente + $credit->getMontant() - $rest - $credit->getAcompte() - $commande->getEscompte();
                
            }
            
            foreach($creditspassee as $credit){// ne tient pas compte des versemet effectue sur le credit
               
                $rest = 0;
                $restes = $this->entityManager->getRepository(LivrerReste::class)->findby(['commande' => $credit]);
                foreach($restes as $reste){
                    $rest += ($reste->getQuantite()- $reste->getQuantitelivre()) * $reste->getSession();
                }
                $ventepassee = $ventepassee + $credit->getMontant() - $rest - $credit->getAcompte() - $commande->getEscompte();
                
            }
            
            
           
         $restes = $this->entityManager->getRepository(LivrerReste::class)->Annuelle($annee);
            foreach($restes as $reste){// on enleve les reste a livrer sur le montant des vente 
               // $stoc = $this->entityManager->getRepository(Stock::class)->find($reste->getProduit());
                $restelivrer = $reste->getQuantite() - $reste->getQuantitelivre();
                $tva = 0;
                // if(!empty($stoc)){
                //     $quant = $stoc->getQuantite() - $restelivrer;
                //     if($quant < 0){
                //         $reste->Tva() == true ? $tva = $restelivrer * 0.1925: null;
                //         $vente -= -1 * $reste->getSession() * $quant + $tva;// -1 * la quatite 
                //     }
                // }else{
                    $reste->getTva() == true ? $tva = $restelivrer * 0.1925: null;
                    $vente -= $restelivrer * $reste->getSession() + $tva;
                // } 
            }
            
            $restespassee = $this->entityManager->getRepository(LivrerReste::class)->Annuelle($annee-1);
            foreach($restespassee as $reste){// on enleve les reste a livrer sur le montant des vente 
               // $stoc = $this->entityManager->getRepository(Stock::class)->find($reste->getProduit());
                $restelivrer = $reste->getQuantite() - $reste->getQuantitelivre();
                $tva = 0;
               
                    $reste->getTva() == true ? $tva = $restelivrer * 0.1925: null;
                    $ventepassee -= $restelivrer * $reste->getSession() + $tva;
             
            }
            
    
            $avoirs = $this->entityManager->getRepository(Avoir::class)->Annuelle($annee);
            foreach($avoirs as $avoir){    
                $vente -= $avoir->getMontant(); 
            }
            
            $avoirspassee = $this->entityManager->getRepository(Avoir::class)->Annuellepassee($annee);
            foreach($avoirspassee as $avoir){    
                $ventepassee -= $avoir->getMontant(); 
            }
            $charge = $charge - $chargepassee;


            //charge personnel
            $salaires = $this->entityManager->getRepository(Paie::class)->compteResultat($annee);
            foreach($salaires as $salaire){
                $chargepersonnel = $chargepersonnel + $salaire->getSalaireNet() + $salaire->getTotalchargepatronal() + $salaire->getTotalChargeEmploye();

                
            }
            $salairespassee = $this->entityManager->getRepository(Paie::class)->compteResultat($annee-1);
            foreach($salairespassee as $salaire){
                $chargepersonnelpassee = $chargepersonnelpassee + $salaire->getSalaireNet() + $salaire->getTotalchargepatronal() + $salaire->getTotalChargeEmploye();

                
            }

            // interet pret bancaire
             $prets = $this->entityManager->getRepository(Financement::class)->findBy(['rembourser' => false, 'apport' => false]);
             foreach($prets as $pret){
                

                $interet = $interet + ((($pret->getMontant() * $pret->getTaux()/100) / $pret->getDuree()) * count($pret->getRemboursements()));    
            }
            
            // amortissement
            $amortissement = 0;
            $amortis = new Amortissement();
            $depenses = $this->entityManager->getRepository(Depense::class)->immobilisation();
            
            foreach($depenses as $depense){

                $lignedepense = $depense->getCategorie();
                if (substr($lignedepense->getCompte(), 0, 2) === "22" ) {

                       
                    $lignedepense->getAmortissement() != null ? $amortissement += $amortis->amortissement($depense) : null;
                
                } elseif (substr($lignedepense->getCompte(), 0, 3) === "211" 
                        || substr($lignedepense->getCompte(), 0, 4) === "2181"
                        || substr($lignedepense->getCompte(), 0, 4) === "2191") {

                    $lignedepense->getAmortissement() != null ? $amortissement += $amortis->amortissement($depense) : null ;    
                   
                
                } elseif (substr($lignedepense->getCompte(), 0, 3) === "212" 
                        || substr($lignedepense->getCompte(), 0, 3) === "213"
                        || substr($lignedepense->getCompte(), 0, 3) === "214"
                        || substr($lignedepense->getCompte(), 0, 4) === "2193") {

                    $lignedepense->getAmortissement() != null ? $amortissement += $amortis->amortissement($depense) : null;    
                   
                
                } elseif (substr($lignedepense->getCompte(), 0, 3) === "215"
                         ||   substr($lignedepense->getCompte(), 0, 3) === "216") {

                    $lignedepense->getAmortissement() != null ? $amortissement += $amortis->amortissement($depense) : null;    
                   
                
                } elseif ((substr($lignedepense->getCompte(), 0, 3) === "217"
                         ||   substr($lignedepense->getCompte(), 0, 3) === "218"
                         ||   substr($lignedepense->getCompte(), 0, 4) === "2198")
                         &&   substr($lignedepense->getCompte(), 0, 4) !== "2181") {

                    $lignedepense->getAmortissement() != null ? $amortissement += $amortis->amortissement($depense) : null ;    
                   
                
                }elseif (substr($lignedepense->getCompte(), 0, 3) === "231" 
                        || substr($lignedepense->getCompte(), 0, 3) === "232"
                        || substr($lignedepense->getCompte(), 0, 3) === "233"
                        || substr($lignedepense->getCompte(), 0, 3) === "237"
                        || substr($lignedepense->getCompte(), 0, 4) === "2391") {

                    $lignedepense->getAmortissement() != null ? $amortissement += $amortis->amortissement($depense) : null ;    
                   
                
                }elseif (substr($lignedepense->getCompte(), 0, 3) === "234" 
                        || substr($lignedepense->getCompte(), 0, 3) === "235"
                        || substr($lignedepense->getCompte(), 0, 3) === "238"
                        || substr($lignedepense->getCompte(), 0, 4) === "2392"
                        || substr($lignedepense->getCompte(), 0, 4) === "2393") {

                    $lignedepense->getAmortissement() != null ? $amortissement += $amortis->amortissement($depense) : null ;    
                   
                
                } elseif (substr($lignedepense->getCompte(), 0, 2) === "24"
                         &&   substr($lignedepense->getCompte(), 0, 3) !== "245"
                         &&   substr($lignedepense->getCompte(), 0, 4) !== "2495") {

                    $lignedepense->getAmortissement() != null ? $amortissement += $amortis->amortissement($depense) : null ;    
                   
                
                } elseif (substr($lignedepense->getCompte(), 0, 3) === "245"
                          || substr($lignedepense->getCompte(), 0, 4) === "2495") {

                    $lignedepense->getAmortissement() != null ? $amortissement += $amortis->amortissement($depense) : null;    
                   
                
                }elseif (substr($lignedepense->getCompte(), 0, 3) === "251"
                          || substr($lignedepense->getCompte(), 0, 3) === "252") {

                    $lignedepense->getAmortissement() != null ? $amortissement += $amortis->amortissement($depense) : null;    
                    
                
                }elseif (substr($lignedepense->getCompte(), 0, 2) === "26") {

                    $lignedepense->getAmortissement() != null ? $amortissement += $amortis->amortissement($depense) : null ;    
                   
                
                }elseif (substr($lignedepense->getCompte(), 0, 2) === "27") {

                    $lignedepense->getAmortissement() != null ? $amortissement += $amortis->amortissement($depense) : null ;    
                   
                
                }
            }
            
             $depenses = $this->entityManager->getRepository(Depense::class)->compteResultat($annee);
            
                foreach($depenses as $depense){
                      $lignedepense = $depense->getCategorie();


                    if (substr($lignedepense->getCompte(), 0, 2) === "22" ) {

                    //terrain pas amortissement
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 3) === "604" 
                    || substr($lignedepense->getCompte(), 0, 3) === "605" 
                    || substr($lignedepense->getCompte(), 0, 3) === "608") {

                        $charge += $depense->getMontant(); 
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "62"
                    || substr($lignedepense->getCompte(), 0, 2) === "63") {

                        $charge += $depense->getMontant(); 
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "61") {

                        $charge += $depense->getMontant(); 
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "65") {

                        $charge += $depense->getMontant(); 
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "64") {

                        $charge += $depense->getMontant(); 
                
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "67") {
                        $charge += $depense->getMontant(); 
                    }elseif (substr($lignedepense->getCompte(), 0, 3) === "697") {
                        $charge += $depense->getMontant(); 
                    }
                    
                    

                      
                }
                
                $depensespassee = $this->entityManager->getRepository(Depense::class)->compteResultat($annee-1);
            
                foreach($depensespassee as $depense){
                      $lignedepense = $depense->getCategorie();


                    if (substr($lignedepense->getCompte(), 0, 2) === "22" ) {

                    //terrain pas amortissement
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 3) === "604" 
                    || substr($lignedepense->getCompte(), 0, 3) === "605" 
                    || substr($lignedepense->getCompte(), 0, 3) === "608") {

                        $chargepassee += $depense->getMontant(); 
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "62"
                    || substr($lignedepense->getCompte(), 0, 2) === "63") {

                        $chargepassee += $depense->getMontant(); 
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "61") {

                        $chargepassee += $depense->getMontant(); 
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "65") {

                        $chargepassee += $depense->getMontant(); 
                    
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "64") {

                        $chargpassee += $depense->getMontant(); 
                
                    } elseif (substr($lignedepense->getCompte(), 0, 2) === "67") {
                        $chargepassee += $depense->getMontant(); 
                    }elseif (substr($lignedepense->getCompte(), 0, 3) === "697") {
                        $chargepassee += $depense->getMontant(); 
                    }
                    
                    

                      
                }
            //  $amortis = $this->entityManager->getRepository(Categorie::class)->resultat();
            //  foreach($amortis as $amorti){
            //     $depenses = $this->entityManager->getRepository(Depense::class)->findBy(['categorie' => $amorti->getId()]);
            //     foreach($depenses as $depense){

            //         $amortissement = $amortissement + ($depense->getMontant() / ( $amorti->getAmortissement() * 12)) ;    
            //     }
            // }

            // $depenses = $this->entityManager->getRepository(Depense::class)->compteResultat(date('Y'));
            //  foreach($depenses as $depense){
            //     if($depense->getCategorie()->getAmortissement() === null){

            //         $charge += $depense->getMontant(); 
            //     } 
            // }

            $xa = $vente - ($achat - $variation);
            $xapassee = $ventepassee - ($achatpassee - $variationpassee);
            $tb = 0;
            $tc = 0;
            $td = 0;
            $xb = $vente + $tb + $tc + $td;
            $xbpassee = $ventepassee + $tb + $tc + $td;
            $te = 0;
            $tf = 0;
            $tg = 0;
            $th = 0;
            $ti = 0;

            $rc = 0;
            $rd = 0;
            $re = 0;
            $rf = 0;
            $rg = 0;
            $rh = 0;
            $ri = 0;
            $rj = 0;
            $xc = $xa + ($tb + $tc + $td)+ ($te + $tf + $tg + $th + $ti) - ($rc + $rd + $re + $rf + $rg + $rh + $ri + $rj );
            $xcpassee = $xapassee + ($tb + $tc + $td)+ ($te + $tf + $tg + $th + $ti) - ($rc + $rd + $re + $rf + $rg + $rh + $ri + $rj );
            $xd = $xc - $chargepersonnel;
            $xdpassee = $xcpassee - $chargepersonnelpassee;
            $tj = 0;
            $rl = 0;
            $xe = $xd + $tj + $rl;
            $xepassee = $xdpassee + $tj + $rl;
            $tk = 0;
            $tl = 0;
            $tm = 0;
            $rm = $interet + $charge;
            $rmpassee = $interetpassee + $chargepassee;
            $xf = $tk + $tl + $tm - ($rm + $amortissement);
            $xfpassee = $tk + $tl + $tm - ($rmpassee + $amortissementpassee);
            $xg = $xe + $xf;
            $xgpassee = $xepassee + $xfpassee;
            $tn = 0;
            $to = 0;
            $ro = 0;
            $rp = 0;
            $rq = 0;
            $rs = 0;
            $xh = ( $tn + $to ) - ( $ro + $rp);
            $xhpassee = ( $tn + $to ) - ( $ro + $rp);
            $xi = $xg + $xh - $rq - $rs;
            $xipassee = $xgpassee + $xhpassee - $rq - $rs;
            $result = [
                'xi' => $xi,
                'xipassee' => $xipassee
            ];
            return $result;
        } 
    }

    #[Route("/Amortissement/{depense}", name :"amortissement", methods : ["POST", "GET"]) ]
    public function amortissement(Depense $depense): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            
           // Informations de l'immobilisation
        $valeurAquisition = $depense->getMontant(); // en FCFA
        $duree = $depense->getCategorie()->getAmortissement(); // en années
        $dateAcquisition = $depense->getDate();
        $firstyear = new \DateTime($dateAcquisition->format("Y").'-12-31');
        $dernierAnnee = (int)$dateAcquisition->format("Y") + $duree;

        // Calcul de la dotation annuelle
        $dotationAnnuelle = $valeurAquisition / $duree;
        $dotationMensuelle = $valeurAquisition / ($duree * 12);

        // // Affichage du plan d'amortissement
        // echo "Plan d’amortissement de l’immobilisation : $description\n";
        // echo "Valeur d’acquisition : " . number_format($valeurAquisition, 0, ',', ' ') . " FCFA\n";
        // echo "Durée : $duree ans\n";
        // echo "Dotation annuelle : " . number_format($dotationAnnuelle, 0, ',', ' ') . " FCFA\n\n";

        // echo "Année\tDotation\tCumul amortissement\n";
       $cumul = 0;
       $now = new \Datetime();
        $interval = $now->diff($dateAcquisition);
        $inter = $firstyear->diff($dateAcquisition);
        $moisfirstyear = $inter->m;

        $yearDiff = $interval->y;
        $monthDiff = $interval->m +1; 
        $yearDiff >= $duree ? $yearDiff = $duree : null ;
        //     $j = 0;
        // for ($i = 0; $i < $yearDiff+1; $i++) {
        //     $annee = (int)$dateAcquisition->format("Y") + $j;
        //     if($yearDiff <= 0){
            
           
        //         $cumul += $monthDiff * $dotationMensuelle;
        //        // echo "$annee\t" . number_format($dotationAnnuelle, 0, ',', ' ') . "\t" . number_format($cumul, 0, ',', ' ') . "\n";
            
        // }
        // }
           

            $response = $this->render('finance/amortissement.html.twig',[
              'annee' => (int)$dateAcquisition->format("Y"),
              'dernierAnnee' => $dernierAnnee,
              'monthDiff' => $monthDiff,
              'yearDiff' => $yearDiff,
              'moisfirstyear' => $moisfirstyear,
              'duree' => $duree,
              'depense' => $depense,
              'dotationAnnuelle' => $dotationAnnuelle,
              'dotationMensuelle' => $dotationMensuelle,
               
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


    #[Route("/Immobilisation/", name :"immobilisation", methods : ["POST", "GET"]) ]
    public function immobilisation(): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            
           // Informations de l'immobilisation
        $depenses = $this->entityManager->getRepository(Depense::class)->findAll();
        $listdepenses = [];
            
            foreach($depenses as $depense){

                $lignedepense = $depense->getCategorie();
                if (substr($lignedepense->getCompte(), 0, 2) === "22" ) {

                    $lignedepense->getAmortissement() != null ?  $listdepenses[] = $depense : null ; 
                    
                } elseif (substr($lignedepense->getCompte(), 0, 3) === "211" 
                        || substr($lignedepense->getCompte(), 0, 4) === "2181"
                        || substr($lignedepense->getCompte(), 0, 4) === "2191") {

                    $lignedepense->getAmortissement() != null ?  $listdepenses[] = $depense : null ;    
                   
                
                } elseif (substr($lignedepense->getCompte(), 0, 3) === "212" 
                        || substr($lignedepense->getCompte(), 0, 3) === "213"
                        || substr($lignedepense->getCompte(), 0, 3) === "214"
                        || substr($lignedepense->getCompte(), 0, 4) === "2193") {

                    $lignedepense->getAmortissement() != null ?  $listdepenses[] = $depense : null;    
                   
                
                } elseif (substr($lignedepense->getCompte(), 0, 3) === "215"
                         ||   substr($lignedepense->getCompte(), 0, 3) === "216") {

                    $lignedepense->getAmortissement() != null ?  $listdepenses[] = $depense : null;    
                   
                
                } elseif ((substr($lignedepense->getCompte(), 0, 3) === "217"
                         ||   substr($lignedepense->getCompte(), 0, 3) === "218"
                         ||   substr($lignedepense->getCompte(), 0, 4) === "2198")
                         &&   substr($lignedepense->getCompte(), 0, 4) !== "2181") {

                    $lignedepense->getAmortissement() != null ?  $listdepenses[] = $depense : null ;    
                   
                
                }elseif (substr($lignedepense->getCompte(), 0, 3) === "231" 
                        || substr($lignedepense->getCompte(), 0, 3) === "232"
                        || substr($lignedepense->getCompte(), 0, 3) === "233"
                        || substr($lignedepense->getCompte(), 0, 3) === "237"
                        || substr($lignedepense->getCompte(), 0, 4) === "2391") {

                    $lignedepense->getAmortissement() != null ?  $listdepenses[] = $depense : null ;    
                   
                
                }elseif (substr($lignedepense->getCompte(), 0, 3) === "234" 
                        || substr($lignedepense->getCompte(), 0, 3) === "235"
                        || substr($lignedepense->getCompte(), 0, 3) === "238"
                        || substr($lignedepense->getCompte(), 0, 4) === "2392"
                        || substr($lignedepense->getCompte(), 0, 4) === "2393") {

                    $lignedepense->getAmortissement() != null ?  $listdepenses[] = $depense : null ;    
                   
                
                } elseif (substr($lignedepense->getCompte(), 0, 2) === "24"
                         &&   substr($lignedepense->getCompte(), 0, 3) !== "245"
                         &&   substr($lignedepense->getCompte(), 0, 4) !== "2495") {

                    $lignedepense->getAmortissement() != null ?  $listdepenses[] = $depense : null ;    
                   
                
                } elseif (substr($lignedepense->getCompte(), 0, 3) === "245"
                          || substr($lignedepense->getCompte(), 0, 4) === "2495") {

                    $lignedepense->getAmortissement() != null ?  $listdepenses[] = $depense : null;    
                   
                
                }elseif (substr($lignedepense->getCompte(), 0, 3) === "251"
                          || substr($lignedepense->getCompte(), 0, 3) === "252") {

                    $lignedepense->getAmortissement() != null ? $ $listdepenses[] = $depens: null ;   
                    
                
                }elseif (substr($lignedepense->getCompte(), 0, 2) === "26") {

                    $lignedepense->getAmortissement() != null ?  $listdepenses[] = $depense : null ;    
                   
                
                }elseif (substr($lignedepense->getCompte(), 0, 2) === "27") {

                    $lignedepense->getAmortissement() != null ?  $listdepenses[] = $depense : null ;    
                   
                
                }
                
                
               

                  
            }
           

            $response = $this->render('finance/immobilisation.html.twig',[
              'depenses' => $listdepenses,
               
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

    #[Route("/repartir/", name :"repartir", methods : ["POST", "GET"]) ]
    public function repartir(Request $request): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $resultat = $this->ResultatInterne();
            $entityManager = $this->entityManager;
            $banques = $entityManager->getRepository(Banque::class)->findAll();
       
            if ($request->isMethod('POST')) {

                $repartiton = $entityManager->getRepository(EcritureRepartition::class)->findBy(['annee' => date('Y')]);
                if(empty($repartiton)){

                    $banque = $entityManager->getRepository(Banque::class)->find($request->request->get('banque'));
                
                    $ecriture = new EcritureRepartition();
                    $ecriture->setComptecredit("131");
                    $ecriture->setLibellecomptecredit("Résultat net de l’exercice");
                    $ecriture->setComptedebit("1301");
                    $ecriture->setLibellecomptedebit("Résultat net en instance d’affectation");
                    $ecriture->setMontant($resultat);
                    $ecriture->setLibelle("Résultat net en instance d’affectation");
                    $ecriture->setAnnee(date('Y'));
                    $entityManager->persist($ecriture);

                    foreach($request->request as $key=>$value){
                        if($key == "reservelegal" && $value != null){
                            $reservelegal = new EcritureRepartition();
                            $reservelegal->setComptecredit("1301");
                            $reservelegal->setLibellecomptecredit("Résultat net de l’exercice");
                            $reservelegal->setComptedebit("111");
                            $reservelegal->setLibellecomptedebit("Réserve légale");
                            $reservelegal->setMontant($value);
                            $reservelegal->setLibelle("Réserve légale");
                            $reservelegal->setAnnee(date('Y'));
                            $entityManager->persist($reservelegal);
                            $reserve = new Reserve();
                            $reserve->setCompte("111");
                            $reserve->setAnnee(date('Y'));
                            $reserve->setMontant($value);
                            $entityManager->persist($reserve);
                        }elseif($key == "reservestatutaire" && $value != null){
                            $reservestatutaire = new EcritureRepartition();
                            $reservestatutaire->setComptecredit("1301");
                            $reservestatutaire->setLibellecomptecredit("Résultat net en instance d’affectation");
                            $reservestatutaire->setComptedebit("112");
                            $reservestatutaire->setLibellecomptedebit("Resrves Statutaires ou Contactueles");
                            $reservestatutaire->setMontant($value);
                            $reservestatutaire->setLibelle("Resrves Statutaires ou Contactueles");
                            $reservestatutaire->setAnnee(date('Y'));
                            $entityManager->persist($reservestatutaire);
                            $reserve = new Reserve();
                            $reserve->setCompte("112");
                            $reserve->setAnnee(date('Y'));
                            $reserve->setMontant($value);
                            $entityManager->persist($reserve);
                            
                        }elseif($key == "reservereglementaire" && $value != null){
                            $reservereglementaire = new EcritureRepartition();
                            $reservereglementaire->setComptecredit("1301");
                            $reservereglementaire->setLibellecomptecredit("Résultat net en instance d’affectation");
                            $reservereglementaire->setComptedebit("113");
                            $reservereglementaire->setLibellecomptedebit("Resrves Reglementaire");
                            $reservereglementaire->setMontant($value);
                            $reservereglementaire->setLibelle("Resrves Reglementaire");
                            $reservereglementaire->setAnnee(date('Y'));
                            $entityManager->persist($reservereglementaire);
                            $reserve = new Reserve();
                            $reserve->setCompte("113");
                            $reserve->setAnnee(date('Y'));
                            $reserve->setMontant($value);
                            $entityManager->persist($reserve);
                            
                        }elseif($key == "reservefacultative" && $value != null){
                            $reservefacultative = new EcritureRepartition();
                            $reservefacultative->setComptecredit("1301");
                            $reservefacultative->setLibellecomptecredit("Résultat net en instance d’affectation");
                            $reservefacultative->setComptedebit("1181");
                            $reservefacultative->setLibellecomptedebit("Resrves Facultatives");
                            $reservefacultative->setMontant($value);
                            $reservefacultative->setLibelle("Resrves Facultatives");
                            $reservefacultative->setAnnee(date('Y'));
                            $entityManager->persist($reservefacultative);
                            $reserve = new Reserve();
                            $reserve->setCompte("1181");
                            $reserve->setAnnee(date('Y'));
                            $reserve->setMontant($value);
                            $entityManager->persist($reserve);
                            
                        }elseif($key == "autrereserve" && $value != null){
                            $autrereserve = new EcritureRepartition();
                            $autrereserve->setComptecredit("1301");
                            $autrereserve->setLibellecomptecredit("Résultat net en instance d’affectation");
                            $autrereserve->setComptedebit("1188");
                            $autrereserve->setLibellecomptedebit("Autres réserves");
                            $autrereserve->setMontant($value);
                            $autrereserve->setLibelle("Autres réserves");
                            $autrereserve->setAnnee(date('Y'));
                            $entityManager->persist($autrereserve);
                            $reserve = new Reserve();
                            $reserve->setCompte("1188");
                            $reserve->setAnnee(date('Y'));
                            $reserve->setMontant($value);
                            $entityManager->persist($reserve);
                            
                        }elseif($key == "repport" && $value != null){
                            $repport = new EcritureRepartition();
                            $repport->setComptecredit("1301");
                            $repport->setLibellecomptecredit("Résultat net en instance d’affectation");
                            $repport->setComptedebit("121");
                            $repport->setLibellecomptedebit("Report à nouveau créditeur");
                            $repport->setMontant($value);
                            $repport->setLibelle("Report à nouveau créditeur");
                            $repport->setAnnee(date('Y'));
                            $entityManager->persist($repport);

                            $reserve = new Repport();
                            // $reserve->setCompte("1291");
                            $reserve->setAnnee(date('Y'));
                            $reserve->setMontant($value);
                            $entityManager->persist($reserve);
                            
                        }elseif($key == "nom" && $value != null){
                            $nom = new EcritureRepartition();
                            $nom->setComptecredit("1301");
                            $nom->setLibellecomptecredit("Résultat net en instance d’affectation");
                            $nom->setComptedebit($request->request->get('compte'));
                            $nom->setLibellecomptedebit("Associés, Dividendes à payer Actionnaire");
                            $nom->setMontant($request->request->get('dividende'));
                            $nom->setLibelle("Associés, Dividendes à payer Actionnaire ".$request->request->get('nom'));
                            $nom->setAnnee(date('Y'));
                            $entityManager->persist($nom);  
                    
                            $impotDividende = new EcritureRepartition();
                            $impotDividende->setComptecredit($request->request->get('compte'));
                            $impotDividende->setLibellecomptecredit("Associés, Dividendes à payer Actionnaire");
                            $impotDividende->setComptedebit("4424");
                            $impotDividende->setLibellecomptedebit("Impôts et taxes recouvrables /sté");
                            $impotDividende->setMontant($request->request->get('impotDividende'));
                            $impotDividende->setLibelle("Impôts et taxes recouvrables /sté ".$request->request->get('nom'));
                            $impotDividende->setAnnee(date('Y'));
                            $entityManager->persist($impotDividende);  

                            $gain = new EcritureRepartition();
                            $gain->setComptecredit($request->request->get('compte'));
                            $gain->setLibellecomptecredit("Associés, Dividendes à payer Actionnaire");
                            $gain->setComptedebit($banque->getCompte());
                            $gain->setLibellecomptedebit($banque->getNom());
                            $gain->setMontant($request->request->get('dividende') - $request->request->get('impotDividende'));
                            $gain->setLibelle("Associés, Dividendes à payer Actionnaire ".$request->request->get('nom'));
                            $gain->setAnnee(date('Y'));
                            $entityManager->persist($gain); 

                            $debit = new Debit();
                            $debit->setCompte($banque->getCompte());
                            $debit->setType('Banque');
                            // $debitfoncier->setSalaire($paieSalaire);
                            $debit->setMontant($request->request->get('dividende') - $request->request->get('impotDividende'));

                            $ecriturelo = new Ecriture();
                            $ecriturelo->setType('Banque');
                            $ecriturelo->setComptecredit($request->request->get('compte'));
                            $ecriturelo->setLibellecomptecredit("Associés, Dividendes à payer Actionnaire");
                            $ecriturelo->setComptedebit($banque->getCompte());
                            $ecriturelo->setLibellecomptedebit($banque->getNom());
                            $ecriturelo->setDebit($debit);
                            $ecriturelo->setSolde(-($request->request->get('dividende') - $request->request->get('impotDividende')));
                            $ecriturelo->setMontant($request->request->get('dividende') - $request->request->get('impotDividende'));
                            $ecriturelo->setLibelle("Associés, Dividendes à payer Actionnaire ".$request->request->get('nom'));
                            $entityManager->persist($debit);
                            $entityManager->persist($ecriturelo);

                        }elseif($key == "nom1" && $value != null){
                            $nom1 = new EcritureRepartition();
                            $nom1->setComptecredit("1301");
                            $nom1->setLibellecomptecredit("Résultat net en instance d’affectation");
                            $nom1->setComptedebit($request->request->get('compte1'));
                            $nom1->setLibellecomptedebit("Associés, Dividendes à payer Actionnaire");
                            $nom1->setMontant($request->request->get('dividende1'));
                            $nom1->setLibelle("Associés, Dividendes à payer Actionnaire ".$request->request->get('nom1'));
                            $nom1->setAnnee(date('Y'));
                            $entityManager->persist($nom1);  
                    
                            $impotDividende1 = new EcritureRepartition();
                            $impotDividende1->setComptecredit($request->request->get('compte1'));
                            $impotDividende1->setLibellecomptecredit("Associés, Dividendes à payer Actionnaire");
                            $impotDividende1->setComptedebit("4424");
                            $impotDividende1->setLibellecomptedebit("Impôts et taxes recouvrables /sté");
                            $impotDividende1->setMontant($request->request->get('impotDividende1'));
                            $impotDividende1->setLibelle("Impôts et taxes recouvrables /sté ".$request->request->get('nom1'));
                            $impotDividende1->setAnnee(date('Y'));
                            $entityManager->persist($impotDividende1);  

                            $gain1 = new EcritureRepartition();
                            $gain1->setComptecredit($request->request->get('compte1'));
                            $gain1->setLibellecomptecredit("Associés, Dividendes à payer Actionnaire");
                            $gain1->setComptedebit($banque->getCompte());
                            $gain1->setLibellecomptedebit($banque->getNom());
                            $gain1->setMontant($request->request->get('dividende1') - $request->request->get('impotDividende1'));
                            $gain1->setLibelle("Associés, Dividendes à payer Actionnaire ".$request->request->get('nom1'));
                            $gain1->setAnnee(date('Y'));
                            $entityManager->persist($gain1); 

                            $debit1 = new Debit();
                            $debit1->setCompte($banque->getCompte());
                            $debit1->setType('Banque');
                            // $debitfoncier->setSalaire($paieSalaire);
                            $debit1->setMontant($request->request->get('dividende1') - $request->request->get('impotDividende1'));

                            $ecriturelocal1 = new Ecriture();
                            $ecriturelocal1->setType('Banque');
                            $ecriturelocal1->setComptecredit($request->request->get('compte1'));
                            $ecriturelocal1->setLibellecomptecredit("Associés, Dividendes à payer Actionnaire");
                            $ecriturelocal1->setComptedebit($banque->getCompte());
                            $ecriturelocal1->setLibellecomptedebit($banque->getNom());
                            $ecriturelocal1->setDebit($debit1);
                            $ecriturelocal1->setSolde(-($request->request->get('dividende1') - $request->request->get('impotDividende1')));
                            $ecriturelocal1->setMontant($request->request->get('dividende1') - $request->request->get('impotDividende1'));
                            $ecriturelocal1->setLibelle("Associés, Dividendes à payer Actionnaire ".$request->request->get('nom1'));
                            $entityManager->persist($debit1);
                            $entityManager->persist($ecriturelocal1);

                        }elseif($key == "nom2" && $value != null){
                            $nom2 = new EcritureRepartition();
                            $nom2->setComptecredit("1301");
                            $nom2->setLibellecomptecredit("Résultat net en instance d’affectation");
                            $nom2->setComptedebit($request->request->get('compte2'));
                            $nom2->setLibellecomptedebit("Associés, Dividendes à payer Actionnaire");
                            $nom2->setMontant($request->request->get('dividende2'));
                            $nom2->setLibelle("Associés, Dividendes à payer Actionnaire ".$request->request->get('nom2'));
                            $nom2->setAnnee(date('Y'));
                            $entityManager->persist($nom2);  
                    
                            $impotDividende2 = new EcritureRepartition();
                            $impotDividende2->setComptecredit($request->request->get('compte2'));
                            $impotDividende2->setLibellecomptecredit("Associés, Dividendes à payer Actionnaire");
                            $impotDividende2->setComptedebit("4424");
                            $impotDividende2->setLibellecomptedebit("Impôts et taxes recouvrables /sté");
                            $impotDividende2->setMontant($request->request->get('impotDividende2'));
                            $impotDividende2->setLibelle("Impôts et taxes recouvrables /sté ".$request->request->get('nom2'));
                            $impotDividende2->setAnnee(date('Y'));
                            $entityManager->persist($impotDividende2);  

                            $gain2 = new EcritureRepartition();
                            $gain2->setComptecredit($request->request->get('compte2'));
                            $gain2->setLibellecomptecredit("Associés, Dividendes à payer Actionnaire");
                            $gain2->setComptedebit($banque->getCompte());
                            $gain2->setLibellecomptedebit($banque->getNom());
                            $gain2->setMontant($request->request->get('dividende2') - $request->request->get('impotDividende2'));
                            $gain2->setLibelle("Associés, Dividendes à payer Actionnaire ".$request->request->get('nom2'));
                            $gain2->setAnnee(date('Y'));
                            $entityManager->persist($gain2); 

                            $debit2 = new Debit();
                            $debit2->setCompte($banque->getCompte());
                            $debit2->setType('Banque');
                            // $debitfoncier->setSalaire($paieSalaire);
                            $debit2->setMontant($request->request->get('dividende2') - $request->request->get('impotDividende2'));
                             
                            
                            $ecriturelocal = new Ecriture();
                            $ecriturelocal->setType('Banque');
                            $ecriturelocal->setComptecredit($request->request->get('compte2'));
                            $ecriturelocal->setLibellecomptecredit("Associés, Dividendes à payer Actionnaire");
                            $ecriturelocal->setComptedebit($banque->getCompte());
                            $ecriturelocal->setLibellecomptedebit($banque->getNom());
                            $ecriturelocal->setDebit($debit2);
                            $ecriturelocal->setSolde(-($request->request->get('dividende2') - $request->request->get('impotDividende2')));
                            $ecriturelocal->setMontant($request->request->get('dividende2') - $request->request->get('impotDividende2'));
                            $ecriturelocal->setLibelle("Associés, Dividendes à payer Actionnaire ".$request->request->get('nom2'));
                            $entityManager->persist($debit2);
                            $entityManager->persist($ecriturelocal);
                        }
                        $entityManager->flush();
                    }
                    $this->addFlash('notice', 'Resultat reussie');
                
                }else{
                    $this->addFlash('notice', 'Resultat deja reparti');
                }
                
              return $this->redirectToroute('finance_journal_repartir'); 
            }


            $response = $this->render('finance/repartir.html.twig',[
              'resultat' => $resultat,
              'banques' => $banques,
               
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

    #[Route("/journalRepartir/", name :"journal_repartir", methods : ["POST", "GET"]) ]
    public function Journalrepartir(Request $request): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $resultat = $this->ResultatInterne();
            $entityManager = $this->entityManager;
            

            $ecritures = $entityManager->getRepository(EcritureRepartition::class)->findAll();
               
            $response = $this->render('finance/journalrepartition.html.twig',[
              'ecritures' => $ecritures,
               
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

    #[Route("/RepartirNegatif/", name :"repartir_negatif", methods : ["POST", "GET"]) ]
    public function repartinegatif(Request $request): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $resultat = $this->ResultatInterne();
            $entityManager = $this->entityManager;
            

            $ecritures = $entityManager->getRepository(EcritureRepartition::class)->findAll();
            if(empty($repartiton)){
                $ecriture = new EcritureRepartition();
                $ecriture->setComptecredit("1309");
                $ecriture->setLibellecomptecredit("Résultat net en instance d’affectation : perte");
                $ecriture->setComptedebit("139");
                $ecriture->setLibellecomptedebit("Résultat net : perte");
                $ecriture->setMontant($resultat);
                $ecriture->setLibelle("Résultat net : perte");
                $ecriture->setAnnee(date('Y'));
                $entityManager->persist($ecriture);

                $perte = new EcritureRepartition();
                $perte->setComptecredit("1291");
                $perte->setLibellecomptecredit(" perte nette à reporter");
                $perte->setComptedebit("1309");
                $perte->setLibellecomptedebit("Résultat net en instance d’affectation : perte");
                $perte->setMontant($resultat);
                $perte->setLibelle("Résultat net en instance d’affectation : perte");
                $perte->setAnnee(date('Y'));
                $entityManager->persist($perte);
                
                $entityManager->flush();
                

                $this->addFlash('notice', 'Repartition reussie');
                return $this->redirectToroute('finance_journal_repartir'); 
            }else{

                $this->addFlash('notice', 'Resultat deja reparti');
                return $this->redirectToroute('finance_journal_repartir'); 
            }
            
             
            $response = $this->render('finance/journalrepartition.html.twig',[
              'ecritures' => $ecritures,
               
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
