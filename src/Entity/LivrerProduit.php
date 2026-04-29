<?php

namespace App\Entity;

use App\Repository\LivrerProduitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:LivrerProduitRepository::class) ]
class LivrerProduit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Retour") ]
#[ORM\JoinColumn(nullable:true) ]
    private $retour;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Promotion") ]
#[ORM\JoinColumn(nullable:true) ]
    private $promotion;


    #[ORM\ManyToOne(targetEntity:"App\Entity\Commande") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $commande;


    #[ORM\ManyToOne(targetEntity:"App\Entity\Produit") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $produit;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Livrer") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $livrer;

    
    #[ORM\Column(type:"integer", nullable: true) ]
    private $ug;

    #[ORM\Column(type:"string", length:255) ]
    private $lot;

    #[ORM\Column(type:"date") ]
    private $peremption;

//    /**
//     #[\Id
//     #[\ManyToOne(targetEntity:"App\Entity\Commande")
//     */
//    private $commande;
    #[ORM\Column(type:"boolean") ]
    private $reste;

    #[ORM\Column(type:"date") ]
    private $date;

    #[ORM\Column(type:"integer") ]
    private $quantitelivrer;
    #[ORM\Column(type:"integer") ]
    private $restealivrer;

    #[ORM\Column(type:"integer") ]

    private $quantitecommander;

    #[ORM\Column(type:"integer") ]
    private $archive;

    /**
     * Constructor
     */
    public function __construct(Livrer $livrer, Produit $produit, $quantitecommander, $quantitelivrer, $archive, Commande $commande, $lot, $peremption)
    {
        $this->date = new \Datetime();
        $this->produit = $produit;
        $this->commande = $commande;
        $this->quantitecommander = $quantitecommander;
        $this->archive = $archive;
        $this->quantitelivrer = $quantitelivrer;
        $this->livrer = $livrer;
        $this->lot = $lot;
        $this->peremption = $peremption;
        $this->reste = false;
        $this->restealivrer = 0;
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


    public function getArchive(): ?int
    {
        return $this->archive;
    }

    public function setArchive(int $archive): self
    {
        $this->archive = $archive;

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

//    public function getCommande(): ?Commande
//    {
//        return $this->commande;
//    }
//
//    public function setCommande(?Commande $commande): self
//    {
//        $this->commande = $commande;
//
//        return $this;
//    }

    public function getLivrer(): ?Livrer
    {
        return $this->livrer;
    }

    public function setLivrer(?Livrer $livrer): self
    {
        $this->livrer = $livrer;

        return $this;
    }

    public function getQuantitelivrer(): ?int
    {
        return $this->quantitelivrer;
    }

    public function setQuantitelivrer(int $quantitelivrer): self
    {
        $this->quantitelivrer = $quantitelivrer;

        return $this;
    }

    public function getQuantitecommander(): ?int
    {
        return $this->quantitecommander;
    }

    public function setQuantitecommander(int $quantitecommander): self
    {
        $this->quantitecommander = $quantitecommander;

        return $this;
    }

    public function getReste(): ?bool
    {
        return $this->reste;
    }

    public function setReste(bool $reste): self
    {
        $this->reste = $reste;

        return $this;
    }

    public function getRestealivrer(): ?int
    {
        return $this->restealivrer;
    }

    public function setRestealivrer(int $restealivrer): self
    {
        $this->restealivrer = $restealivrer;

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

    public function getPromotion(): ?Promotion
    {
        return $this->promotion;
    }

    public function setPromotion(?Promotion $promotion): self
    {
        $this->promotion = $promotion;

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

    public function getId(): ?int
    {
        return $this->id;
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

    public function isReste(): ?bool
    {
        return $this->reste;
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
