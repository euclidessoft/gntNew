<?php

namespace App\Entity;

use App\Repository\ReserveRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReserveRepository::class)]
class Reserve
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $annee = null;

    #[ORM\Column(length: 255)]
    private ?string $compte = null;

    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\Column]
    private ?bool $capitalise = null;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->capitalise = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnnee(): ?string
    {
        return $this->annee;
    }

    public function setAnnee(string $annee): static
    {
        $this->annee = $annee;

        return $this;
    }

    public function getCompte(): ?string
    {
        return $this->compte;
    }

    public function setCompte(string $compte): static
    {
        $this->compte = $compte;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function isCapitalise(): ?bool
    {
        return $this->capitalise;
    }

    public function setCapitalise(bool $capitalise): static
    {
        $this->capitalise = $capitalise;

        return $this;
    }
}
