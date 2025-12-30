<?php

namespace App\Entity;

use App\Repository\EtatStockRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EtatStockRepository::class)]
class EtatStock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $annee = null;

    #[ORM\Column]
    private ?float $stockinitial = null;

    #[ORM\Column]
    private ?float $stockfinal = null;

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

    public function getStockinitial(): ?float
    {
        return $this->stockinitial;
    }

    public function setStockinitial(float $stockinitial): static
    {
        $this->stock = $stockinitial;

        return $this;
    }

    public function getStockfinal(): ?float
    {
        return $this->stockfinal;
    }

    public function setStockfinal(float $stockfinal): static
    {
        $this->stock = $stockfinal;

        return $this;
    }
}
