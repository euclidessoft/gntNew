<?php

namespace App\Entity;

use App\Repository\AvoirRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:AvoirRepository::class) ]
class Avoir
{

    #[ORM\ManyToOne(targetEntity:"App\Entity\Employe") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $admin;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Client") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $client;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Commande") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $commande;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Reclamation") ]
#[ORM\JoinColumn(nullable:true) ]
    private $reclamation;

    #[ORM\ManyToOne(targetEntity:"App\Entity\LivrerReste") ]
#[ORM\JoinColumn(nullable:true) ]
    private $reste;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type:"date") ]
    private $date;

    #[ORM\Column(type:"float") ]
    private $Montant;

    #[ORM\Column(type:"float") ]
    private $prelevement;

    #[ORM\Column(type:"float") ]
    private $tva;

    #[ORM\Column(type:"boolean") ]
    private $rebourser;

    #[ORM\ManyToOne(targetEntity:Retour::class) ]
     #[ORM\JoinColumn(nullable:true) ]
    private $retour;

    #[ORM\OneToOne(targetEntity:Remboursement::class, mappedBy:"avoir", cascade:["persist", "remove"]) ]
    private $remboursement;

    /**
     * Constructor
     */
    public function __construct(User $client, User $admin, Commande $commande)
    {
        $this->date = new \Datetime();
        $this->client = $client;
        $this->admin = $admin;
        $this->commande = $commande;
        $this->Montant = 0;
        $this->rebourser = 0;
        $this->prelevement = 0;
        $this->tva = 0;
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

    public function getMontant(): ?float
    {
        return $this->Montant;
    }

    public function setMontant(float $Montant): self
    {
        $this->Montant = $this->acompte($Montant);

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

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

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

    public function getReclamation(): ?Reclamation
    {
        return $this->reclamation;
    }

    public function setReclamation(?Reclamation $reclamation): self
    {
        $this->reclamation = $reclamation;

        return $this;
    }

    public function getReste(): ?LivrerReste
    {
        return $this->reste;
    }

    public function setReste(?LivrerReste $reste): self
    {
        $this->reste = $reste;

        return $this;
    }

    public function getRebourser(): ?bool
    {
        return $this->rebourser;
    }

    public function setRebourser(bool $rebourser): self
    {
        $this->rebourser = $rebourser;

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

    public function getRemboursement(): ?Remboursement
    {
        return $this->remboursement;
    }

    public function setRemboursement(?Remboursement $remboursement): self
    {
        // unset the owning side of the relation if necessary
        if ($remboursement === null && $this->remboursement !== null) {
            $this->remboursement->setAvoir(null);
        }

        // set the owning side of the relation if necessary
        if ($remboursement !== null && $remboursement->getAvoir() !== $this) {
            $remboursement->setAvoir($this);
        }

        $this->remboursement = $remboursement;

        return $this;
    }

    public function acompte($montant){
        if ($this->commande->getAcompte() != 0){
            $prelevement= $montant * 0.02;
            $montant += $prelevement;
            $this->setPrelevement($prelevement);
        }
        return $montant;
    }

    public function getPrelevement(): ?float
    {
        return $this->prelevement;
    }

    public function setPrelevement(float $prelevement): static
    {
        $this->prelevement = $prelevement;

        return $this;
    }

    public function isRebourser(): ?bool
    {
        return $this->rebourser;
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
