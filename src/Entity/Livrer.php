<?php

namespace App\Entity;

use App\Repository\LivrerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:LivrerRepository::class) ]
class Livrer
{

    #[ORM\OneToMany(targetEntity:"App\Entity\LivrerProduit", mappedBy:"livrer") ]
    private $livraison;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Employe") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $user;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Retour") ]
#[ORM\JoinColumn(nullable:true) ]
    private $retour;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Employe") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $livreur;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Commande") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $commande;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type:"date") ]
    private $date;

    #[ORM\Column(type:"boolean") ]
    private $reste;

    #[ORM\Column(type:"boolean") ]
    private $livrer;

    #[ORM\Column(type:"datetime", nullable:true) ]
    private $dateefectlivraison;

    #[ORM\Column(type:"string", length:255, nullable:true) ]
    private $signature;

     #[ORM\ManyToOne(targetEntity:"App\Entity\Client") ]
     #[ORM\JoinColumn(nullable:true) ]
    private $recepteur;

    #[ORM\Column(type:"string", length:255, nullable:true) ]
    private $nom;
    
    #[ORM\Column(type:"string", length:255, nullable:true) ]
    private $phone;

    /**
     * Constructor
     */
    public function __construct(Commande $commande, User $user)
    {
        $this->date = new \Datetime();
        $this->commande = $commande;
        $this->user = $user;
        $this->reste = false;
        $this->livraison = new ArrayCollection();
        $this->livrer = false;
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

    public function getUser(): ?Employe
    {
        return $this->user;
    }

    public function setUser(?Employe $user): self
    {
        $this->user = $user;

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

    public function getReste(): ?bool
    {
        return $this->reste;
    }

    public function setReste(bool $reste): self
    {
        $this->reste = $reste;

        return $this;
    }

    /*  #[return Collection|LivrerProduit[]
     */
    public function getLivraison(): Collection
    {
        return $this->livraison;
    }

    public function addLivraison(LivrerProduit $livraison): self
    {
        if (!$this->livraison->contains($livraison)) {
            $this->livraison[] = $livraison;
            $livraison->setLivrer($this);
        }

        return $this;
    }

    public function removeLivraison(LivrerProduit $livraison): self
    {
        if ($this->livraison->removeElement($livraison)) {
            // set the owning side to null (unless already changed)
            if ($livraison->getLivrer() === $this) {
                $livraison->setLivrer(null);
            }
        }

        return $this;
    }

    public function getLivreur(): ?Employe
    {
        return $this->livreur;
    }

    public function setLivreur(?Employe $livreur): self
    {
        $this->livreur = $livreur;

        return $this;
    }

    public function getLivrer(): ?bool
    {
        return $this->livrer;
    }

    public function setLivrer(bool $livrer): self
    {
        $this->livrer = $livrer;

        return $this;
    }

    public function getDateefectlivraison(): ?\DateTimeInterface
    {
        return $this->dateefectlivraison;
    }

    public function setDateefectlivraison(?\DateTimeInterface $dateefectlivraison): self
    {
        $this->dateefectlivraison = $dateefectlivraison;

        return $this;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): self
    {
        $this->signature = $signature;

        return $this;
    }

    public function getRetour(): ?Retour
    {
        return $this->retour;
    }

    public function setRetour(?Retour $retour): self
    {
        $this->retour = $retour;

        return $this;
    }

    public function isReste(): ?bool
    {
        return $this->reste;
    }

    public function isLivrer(): ?bool
    {
        return $this->livrer;
    }

    public function getRecepteur(): ?Client
    {
        return $this->recepteur;
    }

    public function setRecepteur(?Client $recepteur): static
    {
        $this->recepteur = $recepteur;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }
}
