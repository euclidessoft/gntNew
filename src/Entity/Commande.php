<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:CommandeRepository::class) ]
class Commande
{
    #[ORM\OneToMany(targetEntity:"App\Entity\Versement", mappedBy:"commande") ]
    private $versements;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Client") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $user;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Employe") ]
#[ORM\JoinColumn(nullable:true) ]
    private $admin;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Employe") ]
#[ORM\JoinColumn(nullable:true) ]
    private $livreur;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Paiement") ]
#[ORM\JoinColumn(nullable:true) ]
    private $paiement;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type:"date") ]
    private $date;

    #[ORM\Column(type:"float") ]
    private $Montant;

    #[ORM\Column(type:"float") ]
    private $versement;

    #[ORM\Column(type:"float") ]
    private $reduction;

    #[ORM\Column(type:"boolean") ]
    private $suivi;

    #[ORM\Column(type:"boolean") ]
    private $livraison;

    #[ORM\Column(type:"date", nullable:true) ]
    private $datelivrer;

    #[ORM\Column(type:"float") ]
    private $tva;

    #[ORM\Column(type:"boolean") ]
    private $credit;

    #[ORM\Column(type:"boolean") ]
    private $payer;
    #[ORM\Column(type:"boolean") ]
    private $livrer;

    #[ORM\Column(type:"datetime", nullable:true) ]
    private $dateefectlivraison;

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: CommandeProduit::class)]
    private Collection $commandeProduits;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->date = new \Datetime();
        $this->suivi = false;
        $this->credit = false;
        $this->livraison = false;
        $this->reduction = 0;
        $this->tva = 0;
        $this->versements = new ArrayCollection();
        $this->commandeProduits = new ArrayCollection();
        $this->versement = 0;
        $this->payer = false;
        $this->livrer = false;
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): self
    {
        $this->ref = $ref;

        return $this;
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

    public function getUser(): ?Client
    {
        return $this->user;
    }

    public function setUser(?Client $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->Montant;
    }

    public function setMontant(float $Montant): self
    {
        $this->Montant = $Montant;

        return $this;
    }

    public function getSuivi(): ?bool
    {
        return $this->suivi;
    }

    public function setSuivi(bool $suivi): self
    {
        $this->suivi = $suivi;

        return $this;
    }

    public function getLivreur(): ?Employe
    {
        return $this->livreur;
    }

    public function setLivreur(?Employe $livreur): self
    {
        $this->livreur = $livreur;

        return $this;
    }

    public function getLivraison(): ?bool
    {
        return $this->livraison;
    }

    public function setLivraison(bool $livraison): self
    {
        $this->livraison = $livraison;

        return $this;
    }

    public function getDatelivrer(): ?\DateTimeInterface
    {
        return $this->datelivrer;
    }

    public function setDatelivrer(\DateTimeInterface $datelivrer): self
    {
        $this->datelivrer = $datelivrer;

        return $this;
    }

    public function getPaiement(): ?Paiement
    {
        return $this->paiement;
    }

    public function setPaiement(?Paiement $paiement): self
    {
        $this->paiement = $paiement;

        return $this;
    }

    public function getReduction(): ?float
    {
        return $this->reduction;
    }

    public function setReduction(?float $reduction): self
    {
        $this->reduction = $reduction;

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

    public function getCredit(): ?bool
    {
        return $this->credit;
    }

    public function setCredit(bool $credit): self
    {
        $this->credit = $credit;

        return $this;
    }

    /*  #[return Collection|Versement[]
     */
    public function getVersements(): Collection
    {
        return $this->versements;
    }

    public function addVersement(Versement $versement): self
    {
        if (!$this->versements->contains($versement)) {
            $this->versements[] = $versement;
            $versement->setCommande($this);
        }

        return $this;
    }

    public function removeVersement(Versement $versement): self
    {
        if ($this->versements->removeElement($versement)) {
            // set the owning side to null (unless already changed)
            if ($versement->getCommande() === $this) {
                $versement->setCommande(null);
            }
        }

        return $this;
    }

    public function getVersement(): ?float
    {
        return $this->versement;
    }

    public function setVersement(float $versement): self
    {
        $this->versement = $versement;

        return $this;
    }

    public function getAdmin(): ?Employe
    {
        return $this->admin;
    }

    public function setAdmin(?Employe $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function getPayer(): ?bool
    {
        return $this->payer;
    }

    public function setPayer(bool $payer): self
    {
        $this->payer = $payer;

        return $this;
    }

    public function getLivrer(): ?bool
    {
        return $this->livrer;
    }

    public function setLivrer(bool $livrer): self
    {
        $this->livrer = $livrer;

        return $this;
    }

    public function getDateefectlivraison(): ?\DateTimeInterface
    {
        return $this->dateefectlivraison;
    }

    public function setDateefectlivraison(?\DateTimeInterface $dateefectlivraison): self
    {
        $this->dateefectlivraison = $dateefectlivraison;

        return $this;
    }
}
