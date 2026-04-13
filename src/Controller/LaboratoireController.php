<?php

namespace App\Controller;

use App\Entity\Laboratoire;
use App\Entity\Produit;
use App\Repository\LivrerProduitRepository;
use App\Form\LaboratoireForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

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

        return $this->render('laboratoire/index.html.twig', [
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

        return $this->render('laboratoire/new.html.twig', [
            'laboratoire' => $laboratoire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_laboratoire_show', methods: ['GET'])]
    public function show(Laboratoire $laboratoire): Response
    {
        return $this->render('laboratoire/show.html.twig', [
            'laboratoire' => $laboratoire,
        ]);
    }

    #[Route('/laboratoireProduit/{id}', name: 'laboratoire_produit_show', methods: ['GET'])]
    public function produitshow(Produit $produit, livrerProduitRepository $repository): Response
    {
        $vendu =0;
        $ventes = $repository->findBy(['produit' => $produit]);
        foreach($ventes as $vente){
            $vendu += $vente->getQuantitelivrer();
        }
        return $this->render('laboratoire/produit_show.html.twig', [
            'produit' => $produit,
            'vendu' => $vendu,
        ]);
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

        return $this->render('laboratoire/edit.html.twig', [
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
