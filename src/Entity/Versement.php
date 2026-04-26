<?php

namespace App\Entity;

use App\Repository\VersementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:VersementRepository::class) ]
class Versement
{
    #[ORM\ManyToOne(targetEntity:"App\Entity\Commande", inversedBy:"versements") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $commande;

    #[ORM\Column(type:"date") ]
    private $date;

    #[ORM\ManyToOne(targetEntity:"App\Entity\User") ]
#[ORM\JoinColumn(nullable:true) ]
    private $user;
    

    #[ORM\ManyToOne(targetEntity:"App\Entity\Client") ]
    #[ORM\JoinColumn(nullable:true) ]
    private $Client;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type:"string", length:255) ]
    private $type;

    #[ORM\Column(type:"integer", nullable:true) ]
    private $numero;

    #[ORM\Column(type:"float") ]
    private $montant;

    #[ORM\ManyToOne(targetEntity:Banque::class, inversedBy:"versements") ]
    private $banque;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->date = new \Datetime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->commande->getUser();
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

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): self
    {
        $this->commande = $commande;

        return $this;
    }

    public function getBanque(): ?Banque
    {
        return $this->banque;
    }

    public function setBanque(?Banque $banque): self
    {
        $this->banque = $banque;

        return $this;
    }

    public function setClient(?Client $Client): static
    {
        $this->Client = $Client;

        return $this;
    }
}
