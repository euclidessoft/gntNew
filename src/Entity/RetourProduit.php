<?php

namespace App\Entity;

use App\Repository\RetourProduitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:RetourProduitRepository::class) ]
class RetourProduit
{


    #[ORM\ManyToOne(targetEntity:"App\Entity\Retour") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $retour;


    #[ORM\ManyToOne(targetEntity:"App\Entity\Commande") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $commande;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Produit") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $produit;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type:"datetime") ]
    private $date;

    #[ORM\Column(type:"integer") ]
    private $quantite;

    #[ORM\Column(type:"string", length:255) ]
    private $motif;

    #[ORM\Column(type:"string", length:255) ]
    private $lot;

    #[ORM\Column(type:"date") ]
    private $peremption;

    #[ORM\Column(type:"boolean") ]
    private $reapprovisionner;

    #[ORM\Column(type:"boolean") ]
    private $rembourser;

    #[ORM\Column(type:"boolean") ]
    private $avoir;

    #[ORM\Column(type:"boolean") ]
    private $valider;
     
    #[ORM\Column(type:"float") ]
    private $prix;
    
    #[ORM\Column(type:"float") ]
    private $prixpublic;
    
    #[ORM\Column(type:"float") ]
    private $tva;


    private $stock;



    public function __construct()
    {
        $this->date = new \DateTime();
        $this->reapprovisionner = false;
        $this->rembourser = false;
        $this->avoir = false;
        $this->valider = false;
    }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }
    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(string $motif): self
    {
        $this->motif = $motif;

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

    public function getRetour(): ?Retour
    {
        return $this->retour;
    }

    public function setRetour(?Retour $retour): self
    {
        $this->retour = $retour;

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

    public function getLot(): ?string
    {
        return $this->lot;
    }

    public function setLot(string $lot): self
    {
        $this->lot = $lot;

        return $this;
    }

    public function getPeremption(): ?\DateTimeInterface
    {
        return $this->peremption;
    }

    public function setPeremption(\DateTimeInterface $peremption): self
    {
        $this->peremption = $peremption;

        return $this;
    }

    public function getReapprovisionner(): ?bool
    {
        return $this->reapprovisionner;
    }

    public function setReapprovisionner(bool $reapprovisionner): self
    {
        $this->reapprovisionner = $reapprovisionner;

        return $this;
    }

    public function getRembourser(): ?bool
    {
        return $this->rembourser;
    }

    public function setRembourser(bool $rembourser): self
    {
        $this->rembourser = $rembourser;

        return $this;
    }

    public function getAvoir(): ?bool
    {
        return $this->avoir;
    }

    public function setAvoir(bool $avoir): self
    {
        $this->avoir = $avoir;

        return $this;
    }

    public function getValider(): ?bool
    {
        return $this->valider;
    }

    public function setValider(bool $valider): self
    {
        $this->valider = $valider;

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

    public function isReapprovisionner(): ?bool
    {
        return $this->reapprovisionner;
    }

    public function isRembourser(): ?bool
    {
        return $this->rembourser;
    }

    public function isAvoir(): ?bool
    {
        return $this->avoir;
    }

    public function isValider(): ?bool
    {
        return $this->valider;
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
