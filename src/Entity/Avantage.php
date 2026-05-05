<?php

namespace App\Entity;

use App\Repository\AvantageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AvantageRepository::class)]
class Avantage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?AveCom $AveCom = null;


    #[ORM\Column]
    private ?float $ca = null;
    
    #[ORM\Column]
    private ?float $ristourne = null;

    #[ORM\Column]
    private ?float $commission = null;

    #[ORM\Column]
    private ?float $tva = null;

    #[ORM\Column]
    private ?float $achat = null;

    #[ORM\Column]
    private ?float $escompte = null;

    
    #[ORM\Column(type:"boolean") ]
    private $payer;

    #[ORM\ManyToOne]
    private ?Client $client = null;

    #[ORM\ManyToOne(inversedBy: 'avantages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Employe $employe = null;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->payer = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRistourne(): ?float
    {
        return $this->ristourne;
    }

    public function setRistourne(float $ristourne): static
    {
        $this->ristourne = $ristourne;

        return $this;
    }

    public function getCommission(): ?float
    {
        return $this->commission;
    }

    public function setCommission(float $commission): static
    {
        $this->commission = $commission;

        return $this;
    }

    public function getEscompte(): ?float
    {
        return $this->escompte;
    }

    public function setEscompte(float $escompte): static
    {
        $this->escompte = $escompte;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getEmploye(): ?Employe
    {
        return $this->employe;
    }

    public function setEmploye(?Employe $employe): static
    {
        $this->employe = $employe;

        return $this;
    }

    public function getCa(): ?float
    {
        return $this->ca;
    }

    public function setCa(float $ca): static
    {
        $this->ca = $ca;

        return $this;
    }

    public function getAchat(): ?float
    {
        return $this->achat;
    }

    public function setAchat(float $achat): static
    {
        $this->achat = $achat;

        return $this;
    }

    public function getAveCom(): ?AveCom
    {
        return $this->AveCom;
    }

    public function setAveCom(?AveCom $AveCom): static
    {
        $this->AveCom = $AveCom;

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

    public function isPayer(): ?bool
    {
        return $this->payer;
    }

    public function setPayer(bool $payer): static
    {
        $this->payer = $payer;

        return $this;
    }
}
