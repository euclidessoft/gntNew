<?php

namespace App\Entity;

use App\Repository\ApprovisionnerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:ApprovisionnerRepository::class) ]
class Approvisionner
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Employe") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $user;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Retour") ]
     #[ORM\JoinColumn(nullable:true) ]
    private $retour;

    #[ORM\Column(type:"date") ]
    private $date;

    #[ORM\OneToMany(targetEntity:Facture::class, mappedBy:"approvisionner") ]
    private $factures;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->date = new \Datetime();
        $this->factures = new ArrayCollection();
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

    /*  #[return Collection|Facture[]
     */
    public function getFactures(): Collection
    {
        return $this->factures;
    }

    public function addFacture(Facture $facture): self
    {
        if (!$this->factures->contains($facture)) {
            $this->factures[] = $facture;
            $facture->setApprovisionner($this);
        }

        return $this;
    }

    public function removeFacture(Facture $facture): self
    {
        if ($this->factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getApprovisionner() === $this) {
                $facture->setApprovisionner(null);
            }
        }

        return $this;
    }

    public function getRetour(): ?Retour
    {
        return $this->retour;
    }

    public function setRetour(?Retour $retour): static
    {
        $this->retour = $retour;

        return $this;
    }
}
