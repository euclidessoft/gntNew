<?php

namespace App\Controller;

use App\Entity\Fournisseur;
use App\Entity\Produit;
use App\Entity\FournisseurProduit;
use App\Form\FournisseurType;
use App\Repository\FournisseurRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Service\PdfService;

#[Route("{_locale}/fournisseur") ]
class FournisseurController extends AbstractController
{
       public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

    #[Route("/", name :"fournisseur_index", methods : ["GET"]) ]
    public function index(FournisseurRepository $fournisseurRepository): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
          
            $response = $this->render('fournisseur/index.html.twig', [
                'fournisseurs' => $fournisseurRepository->findAll(),
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

    #[Route("/new", name :"fournisseur_new", methods : ["GET","POST"]) ]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $fournisseur = new Fournisseur();
            $form = $this->createForm(FournisseurType::class, $fournisseur);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $entityManager->persist($fournisseur);
                $entityManager->flush();

                $compte = '401' . str_pad($fournisseur->getId(), 4, '0', STR_PAD_LEFT);
                $fournisseur->setCompte($compte);
                $entityManager->persist($fournisseur);
                $entityManager->flush();

              
                $response = $this->redirectToRoute('fournisseur_index', [], Response::HTTP_SEE_OTHER);
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

           
            $response = $this->render('fournisseur/new.html.twig', [
                'fournisseur' => $fournisseur,
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

    
    #[Route("/RepportAnalyse/", name :"fournisseur_rapport_analyse") ]
    public function rapporttiers()
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

            return $this->render('fournisseur/rapport_analyse.html.twig');
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

    
    #[Route("/fournisseur_rapport_analyse_lien", name :"fournisseur_rapport_analyse_lien") ]
    public function liendaysbrouyard(Request $request)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $date1 = $request->get('date1');
            $date2 = $request->get('date2');
            $lien = $this->generateUrl('fournisseur_analyse', ['date1' => $date1, 'date2' => $date2]);
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

     #[Route("/fournisseur_analyse/{date1}/{date2}", name :"fournisseur_analyse") ]
    public function analyse(Request $request, $date1, $date2, FournisseurRepository $repo)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            
            $response = $this->render('fournisseur/analyse.html.twig', [
                'fournisseurs' => $repo->findAll(),
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

    
     #[Route("/fournisseur_analyse_print/{date1}/{date2}", name :"fournisseur_analyse_print") ]
    public function analyseprint(Request $request, $date1, $date2, FournisseurRepository $repo)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            
            $response = $this->render('fournisseur/analyse_print.html.twig', [
                'fournisseurs' => $repo->findAll(),
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

    
    
     #[Route("/fournisseur_analyse_pdf/{date1}/{date2}", name :"fournisseur_analyse_pdf") ]
    public function analysepdf(Request $request, $date1, $date2, FournisseurRepository $repo, PdfService $pdfService)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
           

        return $pdfService->streamPdf(
           'fournisseur/analysepdf.html.twig', [
                'fournisseurs' => $repo->findAll(),
                'day1' => $date1,
                'day2' => $date2,
            ],
            sprintf('balance-%s.pdf',1)
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

    #[Route("/{id}", name :"fournisseur_show", methods : ["GET"], requirements:  ['id' => '\d+']) ]
    public function show(Fournisseur $fournisseur): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
           
            $response = $this->render('fournisseur/show.html.twig', [
                'fournisseur' => $fournisseur,
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

    #[Route("/Produits/{id}", name :"fournisseur_produit", methods : ["GET"]) ]
    public function produit(Fournisseur $fournisseur, ProduitRepository $repository): Response
    {/*  selection produits a affecter a un fournisseur*/
        if ($this->security->isGranted('ROLE_FINANCE')) {
         
            $response = $this->render('fournisseur/produit.html.twig', [
                'fournisseur' => $fournisseur,
                'produits' => $repository->nonAssocier($fournisseur),
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

    #[Route("/Affecter", name :"fournisseur_affecter", methods : [ "POST"]) ]
    public function affecter(Request $request, EntityManagerInterface $entityManager)
    {/** affectation de produits */
		if ($this->security->isGranted('ROLE_FINANCE')) 
		{
				if($request->get('produit'))
				{
					$produit = explode(";",$request->get('produit'));
					$produits = $entityManager->getrepository(Produit::class)->fournisseur( $produit);
					$fournisseur = $entityManager->getrepository(Fournisseur::class)->find($request->get('id'));
                    foreach($produits as  $prod){
                        $prod->addFournisseur($fournisseur);
                        $entityManager->persist($prod);
                    }
                    $entityManager->flush();
					$this->addFlash('notice', 'Produits effectués');
                    $url = $this->generateUrl('fournisseur_show', ['id' => $request->get('id')], UrlGeneratorInterface::ABSOLUTE_URL);
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

    #[Route("/{id}/edit", name :"fournisseur_edit", methods : ["GET","POST"]) ]
    public function edit(Request $request, Fournisseur $fournisseur): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            $form = $this->createForm(FournisseurType::class, $fournisseur);
            $form->remove('compte');
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->entityManager->flush();

               
                $response = $this->redirectToRoute('fournisseur_index', [], Response::HTTP_SEE_OTHER);
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

            $response = $this->render('fournisseur/edit.html.twig', [
                'fournisseur' => $fournisseur,
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

    #[Route("/{id}", name :"fournisseur_delete", methods : ["POST"]) ]
    public function delete(Request $request, Fournisseur $fournisseur): Response
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {
            try{
            if ($this->isCsrfTokenValid('delete' . $fournisseur->getId(), $request->request->get('_token')) && empty($fournisseur->getProduits())) {
                $entityManager = $this->entityManager;
                $entityManager->remove($fournisseur);
                $entityManager->flush();
            }
            else  $this->addFlash('notice', 'Suppression impossible verifier s\'il n\'est pas associé à des produits');
        }catch(Throwable $e){
            $this->addFlash('notice', 'Suppression impossilble pour des raison d\'archivage');

        }

            return $this->redirectToRoute('fournisseur_index', [], Response::HTTP_SEE_OTHER);
            
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
