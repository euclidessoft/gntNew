<?php

namespace App\Entity;

use App\Repository\InventaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventaireRepository::class)]
class Inventaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inventaires')]
    private ?Produit $produit = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $motif = null;

    #[ORM\Column]
    private ?int $anciennequantite = null;

    #[ORM\Column]
    private ?int $nouvellequantite = null;

    #[ORM\Column]
    private ?float $pght = null;

    #[ORM\Column]
    private ?float $cession = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\ManyToOne(inversedBy: 'inventaires')]
    private ?Employe $employe = null;

    #[ORM\Column(length: 255)]
    private ?string $lot = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $peremption = null;

     /**
     * Constructor
     */
    public function __construct(Produit $produit, Employe $employe,$motif, $anciennequantite, $nouvellequantite)
    {
        $this->date = new \Datetime();
        $this->produit = $produit;
        $this->employe = $employe;
        $this->motif = $motif;
        $this->anciennequantite = $anciennequantite;
        $this->nouvellequantite = $nouvellequantite;
        $this->pght = $produit->getPght();
        $this->cession = $produit->getPrix();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;

        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(string $motif): static
    {
        $this->motif = $motif;

        return $this;
    }

    public function getAnciennequantite(): ?int
    {
        return $this->anciennequantite;
    }

    public function setAnciennequantite(int $anciennequantite): static
    {
        $this->anciennequantite = $anciennequantite;

        return $this;
    }

    public function getNouvellequantite(): ?int
    {
        return $this->nouvellequantite;
    }

    public function setNouvellequantite(int $nouvellequantite): static
    {
        $this->nouvellequantite = $nouvellequantite;

        return $this;
    }

    public function getPght(): ?float
    {
        return $this->pght;
    }

    public function setPght(float $pght): static
    {
        $this->pght = $pght;

        return $this;
    }

    public function getCession(): ?float
    {
        return $this->cession;
    }

    public function setCession(float $cession): static
    {
        $this->cession = $cession;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

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

    public function getLot(): ?string
    {
        return $this->lot;
    }

    public function setLot(string $lot): static
    {
        $this->lot = $lot;

        return $this;
    }

    public function getPeremption(): ?\DateTime
    {
        return $this->peremption;
    }

    public function setPeremption(\DateTime $peremption): static
    {
        $this->peremption = $peremption;

        return $this;
    }
}
