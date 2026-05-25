<?php

namespace App\Controller;

use App\Entity\Approvisionnement;
use App\Entity\Approvisionner;
use App\Entity\Facture;
use App\Entity\Fournisseur;
use App\Entity\FournisseurProduit;
use App\Entity\Produit;
use App\Entity\Stock;
use App\Repository\ApprovisionnementRepository;
use App\Repository\ApprovisionnerRepository;
use App\Repository\FournisseurRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use App\Service\PdfService;

#[Route("/{_locale}/Stock/Approvisionnement" , name :"stock_") ]
class ApprovisionnementController extends AbstractController
{
      public function __construct(private Security $security, private EntityManagerInterface $entityManager)
    {
    }

    #[Route("/Approvisionnement/", name :"appro_index", methods : ["GET"]) ]
    public function index(SessionInterface $session, ApprovisionnementRepository $approvisionnementRepository, ProduitRepository $produitRepository, FournisseurRepository $fournisseurRepository): Response
    {

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $produits = $produitRepository->reapprovisionnement();

            $approv = $session->get("approv", []);
            $dataPanier = [];
            $total = 0;

//            foreach ($approv as $commande) {
//                $dataPanier[] = [
//                    "produit" => $commande['produit'],
//                ];
//            }

            foreach ($approv as $key => $item) {// ffabrication des donnees dans la session pour affichage
                $ligne = explode("{/",$item);
                $produit = $produitRepository->find($ligne[0]);
                $fournisseur = $fournisseurRepository->find($ligne[4]);
                $dataPanier[]= [
                 'idtab' => $key,
                'id' => $ligne[0],
                'designation' => $produit->getDesigantion(),
                'fournisseur' => $fournisseur->getDesignation(),
                'reference' => $produit->getReference(),
                'quantite' => $ligne[1],
                'lot' => $ligne[2],
                'peremption' => $ligne[3],
                    ];
            }

            $response = $this->render('approvisionnement/index.html.twig', [
                'approvisionnements' => $approvisionnementRepository->findAll(),
                'produits' => $produits,
                'panier' => $dataPanier,
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
            $response = $this->redirectToRoute('security_login');
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

    #[Route("/Historique/", name :"historique", methods : ["GET"]) ]
    public function historique(ApprovisionnerRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_STOCK')) {

            $response = $this->render('approvisionnement/historique.html.twig', [
                'approvisionnements' => $repository->findAll(),
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
            $response = $this->redirectToRoute('security_login');
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


    #[Route("/add/", name :"appro_add") ]
    public function add(Request $request, ProduitRepository $produitRepository, FournisseurRepository $fournisseurRepository, SessionInterface $session)
    {
        // On récupère le panier actuel
        $approv = $session->get("approv", []);
        if ($request->isXmlHttpRequest()) {// traitement de la requete ajax
            $id = $request->get('prod');// recuperation de id produit
            $numero = $request->get('lot');// recuperation de id produit
            $peremption = $request->get('perem');// recuperation de id produit
            $quantite = $request->get('quantite');// recuperation de la quantite commamde
            $idfournisseur = $request->get('fournisseur');// recuperation de la quantite commamde
            foreach ($approv as $key => $item) {
                $ligne = explode("{/",$item);
                if ($ligne[0] == $id && $ligne[2] == $numero) {
                    $res['id'] = 'Un produit avec les même reference a été ajouté';
                    goto suite;
                }
            }
            $produit = $produitRepository->find($id); // recuperation de id produit dans la db
            $fournisseur = $fournisseurRepository->find($idfournisseur); // recuperation de id produit dans la db
//            if (empty($approv[$id])) {//verification existance produit dans le panier

            $chaine = $id."{/".$quantite."{/".$numero."{/".$peremption."{/".$idfournisseur;

//            $produit->setQuantite($quantite);
//            $produit->setLot($numero);
//            $produit->setPeremption($peremption);
//
//            $approv[count($approv)] = [// placement produit et quantite dans le panier
//                "produit" => $produit,
            $approv[] = $chaine;

            // On sauvegarde dans la session
            $session->set("approv", $approv);
            $res['id'] = 'ok';
            $res['ref'] = $produit->getReference();
            $res['designation'] = $produit->getDesigantion();
            $res['fournisseur'] = $fournisseur->getDesignation();
            $res['lot'] = $numero;
            $res['peremption'] = $peremption;
            $res['quantite'] = $quantite;//$produit->getQuantite();

//            } else {
//                $res['id'] = 'no';
//            }
                suite:
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
            return $response;
        }

    }

    #[Route("/edit/", name :"edit") ]
    public function edit(Request $request, SessionInterface $session)
    {

        if ($request->isXmlHttpRequest()) {// traitement de la requete ajax
            $id = $request->get('prod');// recuperation de id produit
            $quantite = $request->get('quantite');// recuperation de la quantite commamde
            $approv = $session->get("approv", []);
            if (!empty($approv[$id])) {//verification existance produit dans le panier

                $produit = $approv[$id]['produit'];
                $produit->setQuantite($quantite);
                $approv[$id]['produit'] = $produit;

                // On sauvegarde dans la session
                $session->set("approv", $approv);

                $res['id'] = 'ok';
                $res['panier'] = $quantite;

            } else {
                $res['id'] = 'no';
            }

            //$session->set("approv", $approv);
            $res['id'] = 'ok';
            $response = new Response();
            $response->headers->set('content-type', 'application/json');
            $re = json_encode($res);
            $response->setContent($re);
            return $response;
        }

    }

    #[Route("/delete/", name :"delete") ]
    public function delete(Request $request, ProduitRepository $repository, SessionInterface $session)
    {
        // On récupère le panier actuel
        $approv = $session->get("approv", []);
        $id = $request->get('prod');
        $numero = $request->get('lot');
        foreach ($approv as $key => $item) {
            $ligne = explode("{/",$item);
            if ($ligne[0] == $id && $ligne[2] == $numero) {
                unset($approv[$key]);
            }
        }
//        $id = $repository->find($request->get('prod'))->getId();
//        foreach ($approv as $key => $item) {
//            if ($item['produit']->getId() == $id) {
//                unset($approv[$id]);
//            }
//        }
        // On sauvegarde dans la session
        $session->set("approv", $approv);
        $res['id'] = 'ok';
        $res['nb'] = count($approv);
        $response = new Response();
        $response->headers->set('content-type', 'application/json');
        $re = json_encode($res);
        $response->setContent($re);
        return $response;
    }

    #[Route("/fournisseur/", name :"fournisseur") ]
    public function fournisseur(Request $request, ProduitRepository $repository, SessionInterface $session)
    {

        $fournisseurproduits = $this->entityManager->getRepository(FournisseurProduit::class)->findBy(['produit' => $request->get('prod')]);

        if(count($fournisseurproduits) > 0) {
            foreach ($fournisseurproduits as $fournisseurproduit) {

                $res[] = [
                    'test' => 'ok',
                    'id' => $fournisseurproduit->getFournisseur()->getId(),
                    'designation' => $fournisseurproduit->getFournisseur()->getDesignation(),
                ];
            }

            }else{
                $res[] = [
                    'test' => 'no',
                    'designation' => "Aucun fournisseur",
                ];
            }

        $response = new Response();
        $response->headers->set('content-type', 'application/json');
        $re = json_encode($res);
        $response->setContent($re);
        return $response;
    }

    #[Route("/deleteAll/", name :"delete_all") ]
    public function deleteAll(SessionInterface $session)
    {
        $session->remove("panier");

        $response = $this->redirectToRoute('commande_panier_panier', [], Response::HTTP_SEE_OTHER);
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

    #[Route("/valider/", name :"valider") ]
    public function valider(SessionInterface $session)
    {
        if ($this->security->isGranted('ROLE_STOCK')) {
            $approv = $session->get("approv", []);
            $em = $this->entityManager;
            $approvisionner = new  Approvisionner();
            $fact = [];
            $listfournisseur = [];
            if (count($approv) >= 1) {

                $approvisionner->setUser($this->getUser());
                $em->persist($approvisionner);
                $i = 1;
                foreach ($approv as $ligne) {
                    $product = explode("{/",$ligne);
                    $id = $product[0];
                    $quantite= $product[1];
                    $lot= $product[2];
                    $peremption= $product[3];
                    $idfourn= $product[4];

                    $produit = $em->getRepository(Produit::class)->find($id);
                    $fournisseur = $em->getRepository(Fournisseur::class)->find($idfourn);
                    //creation de facture

                    if(in_array($idfourn, $listfournisseur)){
                        $fact[$idfourn] =[
                            "montant" => $fact[$idfourn]["montant"] + $produit->getPght() * $quantite,
                        ];

                    }else{
                        $fact[$idfourn] =[
                            "montant" => $produit->getPght() * $quantite,
                        ];
                        $listfournisseur[]= $idfourn;
                    }
                    // fin facture
                    $approvisionnenment = new Approvisionnement($produit, $approvisionner, $quantite, $fournisseur);
                    $approvisionnenment->setLot($lot);
                    $approvisionnenment->setPght($produit->getPght());
                    $approvisionnenment->setCession($produit->getPrix());
                    $approvisionnenment->setPeremption(new \DateTime($peremption));
                    $$i = new Stock($produit, $lot, $peremption, $quantite);
                    $em->persist($$i);
                    $archive = $produit->getStock();
                    $produit->setStock($archive + $quantite);
                    $em->persist($produit);
                    $em->persist($approvisionnenment);
                    $i++;
                    $em->flush();
                }
                foreach ($listfournisseur as $value){
                    $facture = new Facture();
                    $facture->setMontant($fact[$value]["montant"]);
                    $facture->setApprovisionner($approvisionner);
                    $facture->setFournisseur($em->getRepository(Fournisseur::class)->find($value));
                    $em->persist($facture);
                    $em->flush();
                }
                $em->flush();
                $session->remove("approv");
            }
            $this->addFlash('notice', 'Approvisionnement réussie');
            $response = $this->redirectToRoute('stock_show_print', ['id' => $approvisionner->getId()]);
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
            $response = $this->redirectToRoute('security_login');
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


    

    #[Route("/Details_pdf/{id}", name :"show_pdf", methods : ["GET"]) ]
    public function detailspdf(Approvisionner $approvisionner, ApprovisionnementRepository $repository, PdfService $pdfService): Response
    {
        if ($this->security->isGranted('ROLE_STOCK')) {
       
        return $pdfService->streamPdf(
            'approvisionnement/showpdf.html.twig', [
                'approvisionner' => $approvisionner,
                'approvisionnements' => $repository->findBy(['approvisionner' => $approvisionner]),
            ],
            sprintf('facture-%s.pdf',$approvisionner->getCommande()->getId())
        );
           
        } else {
            $response = $this->redirectToRoute('security_login');
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

    #[Route("/Details/{id}", name :"show", methods : ["GET"]) ]
    public function details(Approvisionner $approvisionner, ApprovisionnementRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_STOCK')) {

            $response = $this->render('approvisionnement/show.html.twig', [
                'approvisionner' => $approvisionner,
                'approvisionnements' => $repository->findBy(['approvisionner' => $approvisionner]),
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
            $response = $this->redirectToRoute('security_login');
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

    #[Route("/Details_print/{id}", name :"show_print", methods : ["GET"]) ]
    public function detailsprint(Approvisionner $approvisionner, ApprovisionnementRepository $repository): Response
    {
        if ($this->security->isGranted('ROLE_STOCK')) {

            $response = $this->render('approvisionnement/show_print.html.twig', [
                'approvisionner' => $approvisionner,
                'approvisionnements' => $repository->findBy(['approvisionner' => $approvisionner]),
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
            $response = $this->redirectToRoute('security_login');
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
