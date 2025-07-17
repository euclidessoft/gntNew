<?php

namespace App\Entity;

use App\Repository\EcritureRepartitionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:EcritureRepartitionRepository::class) ]
class EcritureRepartition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

   

    #[ORM\Column(type:"date") ]
    private $date;

    #[ORM\Column(type:"string", length:255, nullable:true) ]
    private $comptedebit;

    #[ORM\Column(type:"string", length:255, nullable:true) ]
    private $annee;

    #[ORM\Column(type:"string", length:255, nullable:true) ]
    private $comptecredit;

    #[ORM\Column(type:"string", length:255, nullable:true) ]
    private $libelle;

    #[ORM\Column(type:"float") ]
    private $montant;

    #[ORM\Column(type:"string", length:255, nullable:true) ]
    private $libellecomptedebit;

    #[ORM\Column(type:"string", length:255, nullable:true) ]
    private $libellecomptecredit;


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
    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getComptedebit(): ?string
    {
        return $this->comptedebit;
    }

    public function setComptedebit(string $comptedebit): self
    {
        $this->comptedebit = $comptedebit;

        return $this;
    }

    public function getComptecredit(): ?string
    {
        return $this->comptecredit;
    }

    public function setComptecredit(?string $comptecredit): self
    {
        $this->comptecredit = $comptecredit;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): self
    {
        $this->libelle = $libelle;

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

    public function getLibellecomptedebit(): ?string
    {
        return $this->libellecomptedebit;
    }

    public function setLibellecomptedebit(?string $libellecomptedebit): self
    {
        $this->libellecomptedebit = $libellecomptedebit;

        return $this;
    }

    public function getLibellecomptecredit(): ?string
    {
        return $this->libellecomptecredit;
    }

    public function setLibellecomptecredit(?string $libellecomptecredit): self
    {
        $this->libellecomptecredit = $libellecomptecredit;

        return $this;
    }

    public function getAnnee(): ?string
    {
        return $this->annee;
    }

    public function setAnnee(?string $annee): self
    {
        $this->annee = $annee;

        return $this;
    }
}
