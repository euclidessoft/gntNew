<?php

namespace App\Entity;

use App\Repository\ReclamationProduitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReclamationProduitRepository::class)]
class ReclamationProduit
{
     #[ORM\ManyToOne(targetEntity:"App\Entity\Produit") ]
    private $produit;
    
    #[ORM\ManyToOne(targetEntity:"App\Entity\Reclamation") ]
    private $reclamation;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Commande") ]
    private $commande;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type:"text", nullable : true) ]
    // #[Assert\NotBlank(  message : "Champ obligatoire") ]
    private $commentaire;

    #[ORM\Column(type:"string", length:255) ]
    //  #[Assert\NotBlank(  message : "Champ obligatoire") ]
    private $motif;

    
    #[ORM\Column(type:"integer") ]
    private $quantite;
    
    #[ORM\Column(type:"float") ]
    private $prix;
    
    #[ORM\Column(type:"float") ]
    private $prixpublic;
    
    #[ORM\Column(type:"float") ]
    private $tva;

     #[ORM\Column(type:"string", length:255) ]
    //  #[Assert\NotBlank(  message : "Champ obligatoire") ]
    private $lot;

     #[ORM\Column(type:"date") ]
    //  #[Assert\NotBlank(  message : "Champ obligatoire") ]
    private $peremption;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(string $motif): static
    {
        $this->motif = $motif;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;

        return $this;
    }

    public function getReclamation(): ?Reclamation
    {
        return $this->reclamation;
    }

    public function setReclamation(?Reclamation $reclamation): static
    {
        $this->reclamation = $reclamation;

        return $this;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): static
    {
        $this->commande = $commande;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getLot(): ?string
    {
        return $this->lot;
    }

    public function setLot(string $lot): static
    {
        $this->lot = $lot;

        return $this;
    }

    public function getPeremption(): ?\DateTime
    {
        return $this->peremption;
    }

    public function setPeremption(\DateTime $peremption): static
    {
        $this->peremption = $peremption;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getPrixpublic(): ?float
    {
        return $this->prixpublic;
    }

    public function setPrixpublic(float $prixpublic): static
    {
        $this->prixpublic = $prixpublic;

        return $this;
    }

    public function getTva(): ?float
    {
        return $this->tva;
    }

    public function setTva(float $tva): static
    {
        $this->tva = $tva;

        return $this;
    }

}
