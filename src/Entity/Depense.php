<?php

namespace App\Entity;

use App\Repository\DepenseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass:DepenseRepository::class) ]
class Depense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type:"string", length:255) ]
     #[Assert\NotBlank(message: "Champ obligatoire") ]
    private $libelle;

    #[ORM\Column(type:"datetime") ]
    private $date;

    #[ORM\ManyToOne(targetEntity:Categorie::class, inversedBy:"depenses") ]
     #[Assert\NotBlank(message: "Champ obligatoire") ]
    private $categorie;

    #[ORM\Column(type:"integer") ]
     #[Assert\NotBlank(message: "Champ obligatoire") ]
    private $montant;

    #[ORM\Column(type:"string", length:255) ]
    private $statut;

    #[ORM\Column(type:"integer", nullable:true) ]
    private $numero;

    #[ORM\Column(type:"string", length:255) ]
    private $type;


    #[ORM\Column(type:"integer") ]
    private $compte;

    #[ORM\ManyToOne(targetEntity:Employe::class, inversedBy:"depenses") ]
    private $user;

    #[ORM\ManyToOne(targetEntity:Banque::class, inversedBy:"depenses") ]
#[ORM\JoinColumn(nullable:true) ]
    private $banque;

   
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->date = new \Datetime();
        $this->statut= "éffectuée";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
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


    public function getMontant(): ?int
    {
        return $this->montant;
    }

    public function setMontant(int $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }


    public function getCompte(): ?int
    {
        return $this->compte;
    }

    public function setCompte(int $compte): self
    {
        $this->compte = $compte;
        return $this;
    }

    
    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(?int $numero): self
    {
        $this->numero = $numero;

        return $this;
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

    public function getUser(): ?Employe
    {
        return $this->user;
    }

    public function setUser(?Employe $user): self
    {
        $this->user = $user;

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

}
