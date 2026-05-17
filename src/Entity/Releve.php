<?php

namespace App\Entity;

use App\Repository\ReleveRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReleveRepository::class)]
class Releve
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\ManyToOne(inversedBy: 'releves')]
    private ?Client $client = null;

    #[ORM\Column(length: 255)]
    private ?string $periode = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $commandes = null;

    // #[ORM\Column(type: Types::TEXT)]
    // private ?string $avoir = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $avantage = null;

    #[ORM\Column]
    private ?float $avance = null;

    #[ORM\Column]
    private ?float $reste = null;

    #[ORM\Column]
    private ?float $prelevement = null;

    #[ORM\Column]
    private ?float $prelevementavoir = null;

    #[ORM\Column]
    private ?float $tva = null;

    #[ORM\Column]
    private ?float $tvaavoir = null;

    #[ORM\Column]
    private ?float $avoir = null;

    #[ORM\Column]
    private ?float $ht = null;

    #[ORM\Column]
    private ?float $htavoir = null;

    #[ORM\Column]
    private ?float $total = null;

    #[ORM\Column]
    private ?int $quinzaine = null;

    #[ORM\Column]
    private ?int $numero = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommandes(): ?string
    {
        return $this->commandes;
    }

    public function setCommandes(string $commandes): static
    {
        $this->commandes = $commandes;

        return $this;
    }

    // public function getAvoir(): ?string
    // {
    //     return $this->avoir;
    // }

    // public function setAvoir(string $avoir): static
    // {
    //     $this->avoir = $avoir;

    //     return $this;
    // }

    public function getAvantage(): ?string
    {
        return $this->avantage;
    }

    public function setAvantage(string $avantage): static
    {
        $this->avantage = $avantage;

        return $this;
    }

    public function getAvance(): ?float
    {
        return $this->avance;
    }

    public function setAvance(float $avance): static
    {
        $this->avance = $avance;

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

    public function getPrelevement(): ?float
    {
        return $this->prelevement;
    }

    public function setPrelevement(float $prelevement): static
    {
        $this->prelevement = $prelevement;

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

    public function getHt(): ?float
    {
        return $this->ht;
    }

    public function setHt(float $ht): static
    {
        $this->ht = $ht;

        return $this;
    }

    public function getPeriode(): ?string
    {
        return $this->periode;
    }

    public function setPeriode(string $periode): static
    {
        $this->periode = $periode;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): static
    {
        $this->total = $total;

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

    public function getQuinzaine(): ?int
    {
        return $this->quinzaine;
    }

    public function setQuinzaine(int $quinzaine): static
    {
        $this->quinzaine = $quinzaine;

        return $this;
    }

    public function getPrelevementavoir(): ?float
    {
        return $this->prelevementavoir;
    }

    public function setPrelevementavoir(float $prelevementavoir): static
    {
        $this->prelevementavoir = $prelevementavoir;

        return $this;
    }

    public function getTvaavoir(): ?float
    {
        return $this->tvaavoir;
    }

    public function setTvaavoir(float $tvaavoir): static
    {
        $this->tvaavoir = $tvaavoir;

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

    public function getHtavoir(): ?float
    {
        return $this->htavoir;
    }

    public function setHtavoir(float $htavoir): static
    {
        $this->htavoir = $htavoir;

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): static
    {
        $this->numero = $numero;

        return $this;
    }
}
