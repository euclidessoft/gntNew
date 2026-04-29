<?php

namespace App\Entity;

use App\Repository\CommandeProduitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:CommandeProduitRepository::class) ]
class CommandeProduit
{
    #[ORM\ManyToOne(targetEntity:"App\Entity\Promotion") ]
#[ORM\JoinColumn(nullable:true) ]
    private $promotion;

    #[ORM\Id]
     #[ORM\ManyToOne(targetEntity:"App\Entity\Produit", inversedBy: 'commandeProduits') ]
    #[ORM\JoinColumn(nullable:false) ]
     private $produit;

     #[ORM\Id]
     #[ORM\ManyToOne(targetEntity:"App\Entity\Commande", inversedBy: 'commandeProduits') ]
    #[ORM\JoinColumn(nullable:false) ]
     private $commande;

    #[ORM\Column(type:"date") ]
    private $date;

    #[ORM\Column(type:"float") ]
    private $session;

    #[ORM\Column(type:"float") ]
    private $publique;

    #[ORM\Column(type:"integer") ]
    private $quantite;

    
    #[ORM\Column(type:"integer", nullable: true) ]
    private $quantitecommande;

    #[ORM\Column(type:"integer", nullable: true) ]
    private $ug;

    private $stock;
    #[ORM\Column(type:"float") ]
    private $tva;

    #[ORM\Column]
    private ?float $pght = null;
    
    #[ORM\Column(type:"boolean", nullable: true) ]
    private $extranet;

    /**
     * Constructor
     */
    public function __construct(Produit $produit, Commande $commande, $session, $publique, $quantite)
    {
        $this->date = new \Datetime();
        $this->produit = $produit;
        $this->commande = $commande;
        $this->session = $session;
        $this->publique = $publique;
        $this->quantite = $quantite;
        $this->ug = 0;
    }


    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getSession(): ?float
    {
        return $this->session;
    }

    public function setSession(float $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function getPublique(): ?float
    {
        return $this->publique;
    }

    public function setPublique(float $publique): self
    {
        $this->publique = $publique;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;

        return $this;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): self
    {
        $this->commande = $commande;

        return $this;
    }
    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function getPromotion(): ?Promotion
    {
        return $this->promotion;
    }

    public function setPromotion(?Promotion $promotion): self
    {
        $this->promotion = $promotion;

        return $this;
    }

    public function getTva(): ?float
    {
        return $this->tva;
    }

    public function setTva(float $tva): self
    {
        $this->tva = $tva;

        return $this;
    }

    public function getPght(): ?float
    {
        return $this->pght;
    }

    public function setPght(float $pght): static
    {
        $this->pght = $pght;

        return $this;
    }

    public function getQuantitecommande(): ?int
    {
        return $this->quantitecommande;
    }

    public function setQuantitecommande(int $quantitecommande): static
    {
        $this->quantitecommande = $quantitecommande;

        return $this;
    }

    public function isExtranet(): ?bool
    {
        return $this->extranet;
    }

    public function setExtranet(bool $extranet): static
    {
        $this->extranet = $extranet;

        return $this;
    }

    public function getUg(): ?int
    {
        return $this->ug;
    }

    public function setUg(?int $ug): static
    {
        $this->ug = $ug;

        return $this;
    }
}
