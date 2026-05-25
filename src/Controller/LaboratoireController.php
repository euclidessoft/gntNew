<?php

namespace App\Controller;

use App\Entity\Laboratoire;
use App\Entity\Produit;
use App\Entity\Commande;
use App\Entity\CommandeProduit;
use App\Repository\LivrerProduitRepository;
use App\Repository\ApprovisionnementRepository;
use App\Repository\ProduitRepository;
use App\Repository\CommandeRepository;
use App\Form\LaboratoireForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Service\PdfService;

#[Route('/{_locale}/Laboratoire')]
final class LaboratoireController extends AbstractController
{
    
       public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

    #[Route(name: 'app_laboratoire_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $laboratoires = $entityManager->getRepository(Laboratoire::class)
            ->findAll();

        return $this->render('laboratoire/admin/index.html.twig', [
            'laboratoires' => $laboratoires,
        ]);
    }

    #[Route('/new', name: 'app_laboratoire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserPasswordHasherInterface $encoder, TokenGeneratorInterface $tokenGenerator): Response
    {
        $laboratoire = new Laboratoire();
        $form = $this->createForm(LaboratoireForm::class, $laboratoire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $hashpass = $encoder->hashPassword($laboratoire, $laboratoire->getPassword());
            
            $laboratoire->setPassword($hashpass);
            $laboratoire->setUsername($laboratoire->getNom());
            $laboratoire->setRoles(["ROLE_LABORATOIRE"]);
            $laboratoire->setFonction('Laboratoire');
            $token = $tokenGenerator->generateToken();
            $laboratoire->setResetToken($token);
            $this->entityManager->persist($laboratoire);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_laboratoire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('laboratoire/admin/new.html.twig', [
            'laboratoire' => $laboratoire,
            'form' => $form,
        ]);
    }
    
    #[Route("/Produits/{id}", name :"laboratoire_produit", methods : ["GET"]) ]
    public function produit(laboratoire $laboratoire, ProduitRepository $repository): Response
    {/*  selection produits a affecter a un laboratoire*/
        if ($this->security->isGranted('ROLE_FINANCE')) {
           
            $response = $this->render('laboratoire/admin/produits.html.twig', [
                'laboratoire' => $laboratoire,
                'produits' => $repository->laboratoirenonAssocier($laboratoire),
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

    #[Route("/Affecter", name :"laboratoire_affecter", methods : [ "POST"]) ]
    public function affecter(Request $request, EntityManagerInterface $entityManager)
    {/** affectation de produits */
		if ($this->security->isGranted('ROLE_FINANCE')) 
		{
				if($request->get('produit'))
				{
					$produit = explode(";",$request->get('produit'));
					$produits = $entityManager->getrepository(Produit::class)->fournisseur( $produit);// tableau de produits used in fournisseur
					$laboratoire = $entityManager->getrepository(laboratoire::class)->find($request->get('id'));
                    foreach($produits as  $prod){
                        $prod->setLaboratoire($laboratoire);
                        $entityManager->persist($prod);
                    }
                    $entityManager->flush();
					$this->addFlash('notice', 'Produits effectués');
                    $url = $this->generateUrl('app_laboratoire_show', ['id' => $request->get('id')], UrlGeneratorInterface::ABSOLUTE_URL);
					$res['id']= $url;
					
					$response = new Response();
					$response->headers->set('content-type','application/json');
					$re = json_encode($res);
					$response->setContent($re);
					return $response;
				}
			
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
    
    #[Route('/StatMensuel/{id}', name: 'laboratoire_mensuel', methods: ['GET'])]
    public function laboshow(LivrerProduitRepository $repository, Laboratoire $laboratoire): Response
    {
        $produits = [];
        $laboprod = $laboratoire->getProduits();
        foreach($laboprod as $prod){
            $produits[] = $prod->getId();
        }
        // dd($produit);
         $commandes = $repository->labo($produits);
        //  $depart = $commandes[0]->getArchive();
            $tableauClasse = [];
        foreach ($commandes as $commande) {

            $mois = date('Y-m', strtotime($commande->getDate()->format("Y-m-d")));

            $tableauClasse[$mois][] = $commande;
        }
        //  dd($tableauClasse);
       
        return $this->render('laboratoire/labomensuel.html.twig', [
            'livrerproduits' => $tableauClasse,
            'laboratoire' => $laboratoire,
        ]);
    }

    #[Route('/{id}', name: 'app_laboratoire_show', methods: ['GET'])]
    public function show(Laboratoire $laboratoire): Response
    {
        return $this->render('laboratoire/show.html.twig', [
            'laboratoire' => $laboratoire,
        ]);
    }

    #[Route('/admin/{id}', name: 'app_laboratoire_show_admin', methods: ['GET'])]
    public function adminshow(Laboratoire $laboratoire): Response
    {
       
        if ($this->security->isGranted('ROLE_FINANCE')) {

            $response = $this->render('commande/admin/dashbord_laboratoire.html.twig', [
                'laboratoire' => $laboratoire,
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

    #[Route('/laboratoireProduit/{id}/{laboratoire}', name: 'laboratoire_produit_show', methods: ['GET'])]
    public function produitshow(Produit $produit, Laboratoire $laboratoire, livrerProduitRepository $repository): Response
    {
         $commandes = $repository->findBy(['produit' => $produit], ['id' => "ASC"]);
        //  $depart = $commandes[0]->getArchive();
            $tableauClasse = [];
        foreach ($commandes as $commande) {

            $mois = date('Y-m', strtotime($commande->getDate()->format("Y-m-d")));

            $tableauClasse[$mois][] = $commande;
        }
        //  dd($tableauClasse);
        $vendu =0;
        $ventes = $repository->findBy(['produit' => $produit]);
        foreach($ventes as $vente){
            $vendu += $vente->getQuantitelivrer();
        }
        return $this->render('laboratoire/produit_show.html.twig', [
            'livrerproduits' => $tableauClasse,
            'produit' => $produit,
            'laboratoire' => $laboratoire,
        ]);
    }


    
    #[Route('/laboratoireProduit_pdf/{id}/{laboratoire}', name: 'laboratoire_produit_show_pdf', methods: ['GET'])]
    public function produitshowpdf(Produit $produit, Laboratoire $laboratoire, livrerProduitRepository $repository,  PdfService $pdfService): Response
    {
         $commandes = $repository->findBy(['produit' => $produit], ['id' => "ASC"]);
        //  $depart = $commandes[0]->getArchive();
            $tableauClasse = [];
        foreach ($commandes as $commande) {

            $mois = date('Y-m', strtotime($commande->getDate()->format("Y-m-d")));

            $tableauClasse[$mois][] = $commande;
        }
        //  dd($tableauClasse);
        $vendu =0;
        $ventes = $repository->findBy(['produit' => $produit]);
        foreach($ventes as $vente){
            $vendu += $vente->getQuantitelivrer();
        }
       
                 return $pdfService->streamPdf(
            'laboratoire/produit_showpdf.html.twig', [
            'livrerproduits' => $tableauClasse,
            'produit' => $produit,
        ],
            sprintf('laboproduit-%s.pdf',1)
                 );
    }

    
    #[Route('/LaboStatMensuel/{laboratoire}/{mois}', name: 'laboratoire_mois', methods: ['GET'])]
    public function laboshowmensuel(LivrerProduitRepository $repository,Laboratoire $laboratoire, $mois): Response
    {
       
        $laboprod = $laboratoire->getProduits();
       
       
        return $this->render('laboratoire/labo_show.html.twig', [
            'produits' => $laboprod,
            'mois' => $mois,
            'laboratoire' => $laboratoire,
        ]);
    }

    
    #[Route('/LaboStatMensuel_pdf/{laboratoire}/{mois}', name: 'laboratoire_mois_pdf', methods: ['GET'])]
    public function laboshowmensuelpdf(LivrerProduitRepository $repository,Laboratoire $laboratoire, $mois,  PdfService $pdfService): Response
    {
       
        $laboprod = $laboratoire->getProduits();
       
      
        
                return $pdfService->streamPdf(
            'laboratoire/labo_showpdf.html.twig', [
                    'produits' => $laboprod,
                    'mois' => $mois,
                ],
            sprintf('laboproduit-%s.pdf',1)
        );
    }


    #[Route('/{id}/edit', name: 'app_laboratoire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Laboratoire $laboratoire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LaboratoireForm::class, $laboratoire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_laboratoire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('laboratoire/admin/edit.html.twig', [
            'laboratoire' => $laboratoire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_laboratoire_delete', methods: ['POST'])]
    public function delete(Request $request, Laboratoire $laboratoire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$laboratoire->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($laboratoire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_laboratoire_index', [], Response::HTTP_SEE_OTHER);
    }
}
