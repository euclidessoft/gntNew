<?php

namespace App\Entity;

use App\Repository\RepportRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RepportRepository::class)]
class Repport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $annee = null;

    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\Column]
    private ?float $stockinitial = null;

    #[ORM\Column]
    private ?float $stockfinal = null;
    
    #[ORM\Column]
    private ?float $reste = null;

    #[ORM\Column]
    private ?float $creance = null;
    
    #[ORM\Column]
    private ?float $avoir = null;
    
    #[ORM\Column]
    private ?float $dette = null;
    
    #[ORM\Column]
    private ?float $avanceclient = null;
    
    #[ORM\Column]
    private ?float $autrecharge = null;
    
    #[ORM\Column]
    private ?float $vente = null;
  
    #[ORM\Column]
    private ?float $achat = null;

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

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getStockinitial(): ?float
    {
        return $this->stockinitial;
    }

    public function setStockinitial(float $stockinitial): static
    {
        $this->stockinitial = $stockinitial;

        return $this;
    }

    public function getStockfinal(): ?float
    {
        return $this->stockfinal;
    }

    public function setStockfinal(float $stockfinal): static
    {
        $this->stockfinal = $stockfinal;

        return $this;
    }

    public function getReste(): ?float
    {
        return $this->reste;
    }

    public function setReste(float $reste): static
    {
        $this->reste = $reste;

        return $this;
    }

    public function getCreance(): ?float
    {
        return $this->creance;
    }

    public function setCreance(float $creance): static
    {
        $this->creance = $creance;

        return $this;
    }

    public function getAvoir(): ?float
    {
        return $this->avoir;
    }

    public function setAvoir(float $avoir): static
    {
        $this->avoir = $avoir;

        return $this;
    }

    public function getDette(): ?float
    {
        return $this->dette;
    }

    public function setDette(float $dette): static
    {
        $this->dette = $dette;

        return $this;
    }

    public function getAvanceclient(): ?float
    {
        return $this->avanceclient;
    }

    public function setAvanceclient(float $avanceclient): static
    {
        $this->avanceclient = $avanceclient;

        return $this;
    }

    public function getAutrecharge(): ?float
    {
        return $this->autrecharge;
    }

    public function setAutrecharge(float $autrecharge): static
    {
        $this->autrecharge = $autrecharge;

        return $this;
    }

    public function getVente(): ?float
    {
        return $this->vente;
    }

    public function setVente(float $vente): static
    {
        $this->vente = $vente;

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
}
