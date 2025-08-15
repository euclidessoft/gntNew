<?php

namespace App\Entity;

use App\Repository\LivrerResteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:LivrerResteRepository::class) ]
class LivrerReste
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Client") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $client;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Produit") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $produit;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Livrer") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $livrer;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Commande") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $commande;

    #[ORM\Column(type:"date") ]
    private $date;

    #[ORM\Column(type:"integer") ]
    private $quantite;

    #[ORM\Column(type:"integer") ]
    private $quantitelivre;

    private $stock;

    #[ORM\Column(type:"float") ]
    private $session;

    #[ORM\Column]
    private ?bool $credit = null;

    #[ORM\Column(nullable: true)]
    private ?bool $tva = null;// tamp creance pour comppte de resultat et bilan

    /**
     * Constructor
     */
    public function __construct(Livrer $livrer, Commande $commande, Produit $produit, $quantite, $quantitelivre, User $client)
    {
        $this->date = new \Datetime();
        $this->produit = $produit;
        $this->commande = $commande;
        $this->quantitelivre = $quantitelivre;
        $this->quantite = $quantite;
        $this->livrer = $livrer;
        $this->client = $client;
        $this->credit = false;
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

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getQuantitelivre(): ?int
    {
        return $this->quantitelivre;
    }

    public function setQuantitelivre(int $quantitelivre): self
    {
        $this->quantitelivre = $quantitelivre;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;

        return $this;
    }

    public function getLivrer(): ?Livrer
    {
        return $this->livrer;
    }

    public function setLivrer(?Livrer $livrer): self
    {
        $this->livrer = $livrer;

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

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function reste()
    {


        return $this->quantite - $this->quantitelivre;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSession(): ?float
    {
        return $this->session;
    }

    public function setSession(float $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function isCredit(): ?bool
    {
        return $this->credit;
    }

    public function setCredit(bool $credit): static
    {
        $this->credit = $credit;

        return $this;
    }

    public function getTva(): ?bool
    {
        return $this->tva;
    }

    public function setTva(?bool $tva): static
    {
        $this->tva = $tva;

        return $this;
    }

}
