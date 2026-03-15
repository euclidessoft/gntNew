<?php

namespace App\Entity;

use App\Repository\RetourRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:RetourRepository::class) ]
class Retour
{
    
    #[ORM\ManyToOne(targetEntity:"App\Entity\Pharmacie") ]
    #[ORM\JoinColumn(nullable:true) ]
    private $pharmacie;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Commande") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $commande;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Avoir") ]
     #[ORM\JoinColumn(nullable:true) ]
    private $avoir;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(type:"datetime") ]
    private $date;


    public function __construct()
    {
        $this->date = new \DateTime();
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

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): self
    {
        $this->commande = $commande;

        return $this;
    }

    public function getAvoir(): ?Avoir
    {
        return $this->avoir;
    }

    public function setAvoir(?Avoir $avoir): static
    {
        $this->avoir = $avoir;

        return $this;
    }

    public function getPharmacie(): ?Pharmacie
    {
        return $this->pharmacie;
    }

    public function setPharmacie(?Pharmacie $pharmacie): static
    {
        $this->pharmacie = $pharmacie;

        return $this;
    }
}
