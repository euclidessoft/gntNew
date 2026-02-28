<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Component\Security\Core\Security;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;


#[Route("{_locale}/Importation") ]
class ImportationController extends AbstractController
{
      public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

    #[Route("/", name :"importation", methods : ["POST"]) ]
    public function importation(Request $request, SessionInterface $session)
    {
        $file = $request->files->get('import');

        if (!$file) {
            return new Response('Aucun fichier envoyé');
            //dd($request);
        }

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet()->toArray();
            $session->set('sheet', $sheet);

            // $rows = [];

            // foreach ($sheet->getRowIterator() as $row) {

            //     $cellIterator = $row->getCellIterator();
            //     $cellIterator->setIterateOnlyExistingCells(false);

            //     $line = [];

            //     foreach ($cellIterator as $cell) {
            //         $line[] = $cell->getValue();
            //     }

            //     $rows[] = $line;
            // }

           // return $this->json($rows);
            $response = $this->redirectToRoute("commande_panier_panier");
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;

        } catch (\Exception $e) {
            return new Response('Erreur : ' . $e->getMessage());
        }
    }
    
    #[Route("/admin", name :"adminimportation", methods : ["POST"]) ]
    public function importationadmin(Request $request, SessionInterface $session)
    {
        $file = $request->files->get('import');

        if (!$file) {
            return new Response('Aucun fichier envoyé');
            //dd($request);
        }

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet()->toArray();
            $session->set('sheet', $sheet);

            $response = $this->redirectToRoute("commande_panier_panier");
            $response->setSharedMaxAge(0);
            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->setCache([
                'max_age' => 0,
                'private' => true,
            ]);
            return $response;

        } catch (\Exception $e) {
            return new Response('Erreur : ' . $e->getMessage());
        }
    }

     
    #[Route("/ImportPromo", name :"importpromo") ]
    public function importpromo(SessionInterface $session, Request $request, ProduitRepository $produitRepository)
    {
        if ($this->security->isGranted('ROLE_FINANCE')) {

           
            $commande = new Commande();

            $form = $this->createForm(CommandeType::class, $commande);
            $form->add('import', FileType::class, [
                        'label' => 'Importer un fichier Excel',
                        'mapped' => false, //  n'existe pas dans l'entité
                        'required' => true,
                    ]);
            $form->handleRequest($request);

             if ($form->isSubmitted()) {
                 // if ($request->isMethod('POST')) {
                 
                $session->set('client', $commande->getUser()->getId());
                $session->set('extranet', $commande->getUser()->getId());
                $session->set('prelevement', $commande->getUser()->isPrelevement());
                $file = $request->files->get('commande')['import'];
               
                if (!$file) {
                    return new Response('Aucun fichier envoyé');
                   // dd($request);
                }

                try {
                    $spreadsheet = IOFactory::load($file->getPathname());
                    $sheet = $spreadsheet->getActiveSheet()->toArray();
                    $res = [];
                    $panier = [];
                    foreach($sheet as $commande){
                        $id = $commande[0];
                        $quantite = $commande[1];
                        $reduction = null;
                        $produit = $produitRepository->findOneby(['reference' => $id]); // recuperation de id produit dans la db
                       
                        if($produit !== null){
                            if(!empty($produit->getPromotion())) {// verification promo reduction
                                if(!empty($produit->getPromotion()->getReduction())) {
                                    $reduction = $produit->getPromotion()->getReduction();
                                }
                            }
                            if($produit->getMincommande() <= $quantite) {// verification quantite minimum
                            $produit->setQuantite($quantite);

                            $panier[$id] = [// placement produit et quantite dans le panier
                                "produit" => $produit,
                                "promotion" => $reduction,
                            ];

                            // On sauvegarde dans la session
                            //$session->set("panier", $panier);

                            // $res['id'] = 'ok';
                            // $res['ref'] = $produit->getReference();
                            // $res['designation'] = $produit->getDesigantion();
                            // $res['fabriquant'] = $produit->getFabriquant();
                            // $res['quantite'] = $produit->getQuantite();
                        }else{
                            $res['id'] = 'no';
                        }
                       }
                    }
                    $session->set('sheet', $panier);
                
                
                    $response = $this->redirectToRoute('commande_panier_choix_paiement_extranet', ['commande' => 0]);
                    $response->setSharedMaxAge(0);
                    $response->headers->addCacheControlDirective('no-cache', true);
                    $response->headers->addCacheControlDirective('no-store', true);
                    $response->headers->addCacheControlDirective('must-revalidate', true);
                    $response->setCache([
                        'max_age' => 0,
                        'private' => true,
                    ]);
                    return $response;

                } catch (\Exception $e) {
                    return new Response('Erreur : ' . $e->getMessage());
                }
            }


            $response = $this->render('commande/admin/importpromo.html.twig', [
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
}