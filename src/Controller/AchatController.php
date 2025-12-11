<?php

namespace App\Controller;

use App\Complement\Solde as Solde;
use App\Entity\Achat;
use App\Entity\Approvisionnement;
use App\Entity\Approvisionner;
use App\Entity\Debit;
use App\Entity\Ecriture;
use App\Entity\Facture;
use App\Form\AchatType;
use App\Repository\AchatRepository;
use App\Repository\ApprovisionnementRepository;
use App\Repository\ApprovisionnerRepository;
use App\Repository\FactureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

#[Route("/{_locale}/achat") ]
class AchatController extends AbstractController
{
      public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }
    
    #[Route("/", name :"achat_index", methods : ["GET"]) ]
    public function index(AchatRepository $achatRepository): Response
    {
        $response = $this->render('achat/index.html.twig', [
            'achats' => $achatRepository->findAll(),
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

    }

    #[Route("/Fournisseur_Facture_list", name :"facture_list", methods : ["GET"]) ]
    public function listfacture(FactureRepository $repository): Response
    {

        $response = $this->render('achat/facture_list.html.twig', [
            'approvisionnements' => $repository->findBy(['payer' => false]),
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
    }

    #[Route("/new/{facture}", name :"achat_new", methods : ["GET","POST"]) ]
    public function new(Request $request,Facture $facture, Solde $solde): Response
    {
        $achat = new Achat();
        $achat->setMontant($facture->getMontant());
        $achat->setFournisseur($facture->getFournisseur());
        $debit = new Debit();
        $ecriture = new Ecriture();
        $form = $this->createForm(AchatType::class, $achat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;
            $achat->setUser($this->getUser());
            $montant = 0;
            if($achat->getType() == 'Espece'){
                $montant = $solde->montantcaisse($entityManager, 571);
                $achat->setCompte($achat->getFournisseur()->getCompte());

                $debit->setType('Espece');
                $debit->setCompte(571);

                $ecriture->setType('Espece');
                $ecriture->setComptecredit($achat->getFournisseur()->getCompte());
                $ecriture->setLibellecomptecredit($achat->getFournisseur()->getNom());
                $ecriture->setComptedebit(571);
                $ecriture->setLibellecomptedebit("Caisse");
            }
            else{
                $montant = $solde->montantbanque($entityManager, $achat->getBanque()->getCompte());//sole compte
                $achat->setType('Banque');
                $achat->setCompte($achat->getFournisseur()->getCompte());

                $debit->setType('Banque');
                $debit->setMontant($achat->getMontant());
                $debit->setCompte($achat->getBanque()->getCompte());

                $ecriture->setType('Banque');
                $ecriture->setComptecredit($achat->getFournisseur()->getCompte());
                $ecriture->setLibellecomptecredit("Fournisseur");
                $ecriture->setComptedebit($achat->getBanque()->getCompte());
                $ecriture->setLibelleComptedebit($achat->getBanque()->getNom());
            }
            if($achat->getMontant() <= $montant) {
                $debit->setAchat($achat);
                $debit->setMontant($achat->getMontant());
                $facture->setPayer(true);

                $ecriture->setDebit($debit);
                $ecriture->setSolde(-$achat->getMontant());
                $ecriture->setMontant($achat->getMontant());
                $ecriture->setLibelle('Achat chez '. $achat->getFournisseur()->getDesignation());
                $entityManager->persist($achat);
                $entityManager->persist($debit);
                $entityManager->persist($ecriture);
                $entityManager->persist($facture);
                $entityManager->flush();
                $response = $this->redirectToRoute('achat_index', [], Response::HTTP_SEE_OTHER);
                $response->setSharedMaxAge(0);
                $response->headers->addCacheControlDirective('no-cache', true);
                $response->headers->addCacheControlDirective('no-store', true);
                $response->headers->addCacheControlDirective('must-revalidate', true);
                $response->setCache([
                    'max_age' => 0,
                    'private' => true,
                ]);
                return $response;
            }else{
                $this->addFlash('notice', 'Montant non disponible');
            }
        }

        $response = $this->render('achat/new.html.twig', [
            'achat' => $achat,
            'form' => $form->createView(),
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
    }

    #[Route("/{id}", name :"achat_show", methods : ["GET"]) ]
    public function show(Achat $achat): Response
    {
        $response = $this->render('achat/show.html.twig', [
            'achat' => $achat,
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
    }

    #[Route("/{id}/edit", name :"achat_edit", methods : ["GET","POST"]) ]
    public function edit(Request $request, Achat $achat): Response
    {
        $form = $this->createForm(AchatType::class, $achat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $response = $this->redirectToRoute('achat_index', [], Response::HTTP_SEE_OTHER);
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

        $response = $this->render('achat/edit.html.twig', [
            'achat' => $achat,
            'form' => $form->createView(),
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
    }

    #[Route("/{id}", name :"achat_delete", methods : ["POST"]) ]
    public function delete(Request $request, Achat $achat): Response
    {
        if ($this->isCsrfTokenValid('delete'.$achat->getId(), $request->request->get('_token'))) {
            $entityManager = $this->entityManager;
            $entityManager->remove($achat);
            $entityManager->flush();
        }

        $response = $this->redirectToRoute('achat_index', [], Response::HTTP_SEE_OTHER);
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
