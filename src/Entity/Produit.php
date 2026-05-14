<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass:ProduitRepository::class) ]
class Produit
{
    
    #[ORM\ManyToOne(targetEntity:"App\Entity\Laboratoire", inversedBy:"produits") ]
    #[ORM\JoinColumn(nullable:true) ]
    private $laboratoire;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Promotion") ]
    #[ORM\JoinColumn(nullable:true) ]
    private $promotion;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type:"string", length:255, nullable:true) ]
    private $reference;// code CIP

     #[ORM\Column(type:"string", length:255, nullable:true) ]
    private $alternatif;// code CIp alternatif

    //  #[ORM\Column(type:"string", length:255, nullable:true) ]
    // private $interne;// code interne

     #[ORM\Column(type:"string", length:255, nullable:true) ]
    private $dci;

    #[ORM\Column(type:"string", length:255) ]
     #[Assert\NotBlank(  message : "Champ obligatoire") ]
    private $desigantion;

    #[ORM\Column(type:"string", length:255, nullable:true) ]
    private $adresse;

    #[ORM\Column(type:"string", length:255, nullable:true) ]
    private $telephone;

    #[ORM\Column(type:"string", length:255, nullable:true) ]
    private $description;

    #[ORM\Column(type:"float") ]
      #[Assert\NotBlank(  message : "Champ obligatoire") ]
    private $prix;

    #[ORM\Column(type:"string", length:255, nullable:true) ]
      #[Assert\NotBlank(  message : "Champ obligatoire") ]
    private $mincommande;

    #[ORM\Column(type:"string", length:255, nullable:true) ]
      #[Assert\NotBlank(  message : "Champ obligatoire") ]
    private $fabriquant;


    private $quantite;
    private $lot;
    private $peremption;

    #[ORM\Column(type:"float") ]
      #[Assert\NotBlank(  message : "Champ obligatoire") ]
    private $prixpublic;

    #[ORM\Column(type:"integer") ]
    private $stock;

    
    #[ORM\Column(type:"integer", nullable: true) ]
    private $colisage;

    #[ORM\Column(type:"date") ]
    private $creation;

    #[ORM\Column(type:"boolean") ]
    private $tva;

//    /**
//     #[\OneToMany(targetEntity:FournisseurProduit::class, mappedBy:"produit")
//     */
//    private $fournisseurProduits;

    #[ORM\ManyToMany(targetEntity:"App\Entity\Fournisseur", inversedBy:"produits") ]
#[ORM\JoinTable(name:"produit_fournisseur") ]
    private $fournisseurs;

    #[ORM\Column(type:"float") ]
    private $pght;

    #[ORM\ManyToMany(targetEntity:"App\Entity\Promotion", mappedBy:"produits") ]
    private $promotions;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: CommandeProduit::class)]
    private Collection $commandeProduits;

    /**
     * @var Collection<int, Inventaire>
     */
    #[ORM\OneToMany(targetEntity: Inventaire::class, mappedBy: 'produit')]
    private Collection $inventaires;


    public function __construct()
    {
        $this->stock = 0;
        $this->creation = new \Datetime();
        $this->tva = false;
//        $this->fournisseurProduits = new ArrayCollection();
        $this->fournisseurs = new ArrayCollection();
        // $this->promotionProduits = new ArrayCollection();
        $this->commandeProduits = new ArrayCollection();
        $this->promotions = new ArrayCollection();
        $this->inventaires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getDesigantion(): ?string
    {
        return $this->desigantion;
    }

    public function setDesigantion(string $desigantion): self
    {
        $this->desigantion = $desigantion;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getMincommande(): ?string
    {
        return $this->mincommande;
    }

    public function setMincommande(?string $mincommande): self
    {
        $this->mincommande = $mincommande;

        return $this;
    }

    public function getFabriquant(): ?string
    {
        return $this->fabriquant;
    }

    public function setFabriquant(?string $fabriquant): self
    {
        $this->fabriquant = $fabriquant;

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
    public function getLot()
    {
        return $this->lot;
    }

    public function setLot($lot)
    {
        $this->lot = $lot;

        return $this;
    }
    public function getPeremption()
    {
        return $this->peremption;
    }

    public function setPeremption($peremption)
    {
        $this->peremption = $peremption;

        return $this;
    }

    public function getPrixpublic(): ?float
    {
        return $this->prixpublic;
    }

    public function setPrixpublic(float $prixpublic): self
    {
        $this->prixpublic = $prixpublic;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function approvisionner(int $quantite): self
    {
        $this->stock = $this->stock + $quantite;

        return $this;
    }

    public function livraison(int $quantite)
    {
        $this->stock = $this->stock - $quantite;
        $res = false;
        if($this->stock >= 0 ){
            $res = true;
        }

        return $res;
    }

    public function getCreation(): ?\DateTimeInterface
    {
        return $this->creation;
    }

    public function setCreation(\DateTimeInterface $creation): self
    {
        $this->creation = $creation;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function getTva(): ?bool
    {
        return $this->tva;
    }

    public function setTva(bool $tva): self
    {
        $this->tva = $tva;

        return $this;
    }

//    /**
//     * @return Collection|FournisseurProduit[]
//     */
//    public function getFournisseurProduits(): Collection
//    {
//        return $this->fournisseurProduits;
//    }
//
//    public function addFournisseurProduit(FournisseurProduit $fournisseurProduit): self
//    {
//        if (!$this->fournisseurProduits->contains($fournisseurProduit)) {
//            $this->fournisseurProduits[] = $fournisseurProduit;
//            $fournisseurProduit->setProduit($this);
//        }
//
//        return $this;
//    }
//
//    public function removeFournisseurProduit(FournisseurProduit $fournisseurProduit): self
//    {
//        if ($this->fournisseurProduits->removeElement($fournisseurProduit)) {
//            // set the owning side to null (unless already changed)
//            if ($fournisseurProduit->getProduit() === $this) {
//                $fournisseurProduit->setProduit(null);
//            }
//        }
//
//        return $this;
//    }

    public function getPght(): ?float
    {
        return $this->pght;
    }

    public function setPght(float $pght): self
    {
        $this->pght = $pght;

        return $this;
    }

    /*  #[return Collection|Fournisseur[]
     */
    public function getFournisseurs(): Collection
    {
        return $this->fournisseurs;
    }

    public function addFournisseur(Fournisseur $fournisseur): self
    {
        if (!$this->fournisseurs->contains($fournisseur)) {
            $this->fournisseurs[] = $fournisseur;
        }

        return $this;
    }

    public function removeFournisseur(Fournisseur $fournisseur): self
    {
        $this->fournisseurs->removeElement($fournisseur);

        return $this;
    }

    public function isTva(): ?bool
    {
        return $this->tva;
    }

    /**
     * @return Collection<int, Promotion>
     */
    public function getPromotions(): Collection
    {
        return $this->promotions;
    }

    public function addPromotion(Promotion $promotion): static
    {
        if (!$this->promotions->contains($promotion)) {
            $this->promotions->add($promotion);
            $promotion->addProduit($this);
        }

        return $this;
    }

    public function removePromotion(Promotion $promotion): static
    {
        if ($this->promotions->removeElement($promotion)) {
            $promotion->removeProduit($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, CommandeProduit>
     */
    public function getCommandeProduits(): Collection
    {
        return $this->commandeProduits;
    }

    public function addCommandeProduit(CommandeProduit $commandeProduit): static
    {
        if (!$this->commandeProduits->contains($commandeProduit)) {
            $this->commandeProduits->add($commandeProduit);
            $commandeProduit->setProduit($this);
        }

        return $this;
    }

    public function removeCommandeProduit(CommandeProduit $commandeProduit): static
    {
        if ($this->commandeProduits->removeElement($commandeProduit)) {
            // set the owning side to null (unless already changed)
            if ($commandeProduit->getProduit() === $this) {
                $commandeProduit->setProduit(null);
            }
        }

        return $this;
    }

    public function getDci(): ?string
    {
        return $this->dci;
    }

    public function setDci(?string $dci): static
    {
        $this->dci = $dci;

        return $this;
    }

    public function getAlternatif(): ?string
    {
        return $this->alternatif;
    }

    public function setAlternatif(?string $alternatif): static
    {
        $this->alternatif = $alternatif;

        return $this;
    }

    // public function getInterne(): ?string
    // {
    //     return $this->interne;
    // }

    // public function setInterne(?string $interne): static
    // {
    //     $this->interne = $interne;

    //     return $this;
    // }

    public function getLaboratoire(): ?Laboratoire
    {
        return $this->laboratoire;
    }

    public function setLaboratoire(?Laboratoire $laboratoire): static
    {
        $this->laboratoire = $laboratoire;

        return $this;
    }

    public function getColisage(): ?int
    {
        return $this->colisage;
    }

    public function setColisage(?int $colisage): static
    {
        $this->colisage = $colisage;

        return $this;
    }

    /**
     * @return Collection<int, Inventaire>
     */
    public function getInventaires(): Collection
    {
        return $this->inventaires;
    }

    public function addInventaire(Inventaire $inventaire): static
    {
        if (!$this->inventaires->contains($inventaire)) {
            $this->inventaires->add($inventaire);
            $inventaire->setProduit($this);
        }

        return $this;
    }

    public function removeInventaire(Inventaire $inventaire): static
    {
        if ($this->inventaires->removeElement($inventaire)) {
            // set the owning side to null (unless already changed)
            if ($inventaire->getProduit() === $this) {
                $inventaire->setProduit(null);
            }
        }

        return $this;
    }
}
