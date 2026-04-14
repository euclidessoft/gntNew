<?php


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity() ]
#[ORM\Table(name:"laboratoire") ]
class Laboratoire extends User implements UserInterface
{
    #[ORM\OneToMany(targetEntity:"App\Entity\Produit", mappedBy:"laboratoire") ]
    private $produits;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $boitepostale = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $siteweb = null;

    public function __construct()
    {
        parent::__construct();
        $this->produits = new ArrayCollection();
    }

    public function getBoitepostale(): ?string
    {
        return $this->boitepostale;
    }

    public function setBoitepostale(?string $boitepostale): static
    {
        $this->boitepostale = $boitepostale;

        return $this;
    }
    public function getSiteweb(): ?string
    {
        return $this->siteweb;
    }

    public function setSiteweb(?string $siteweb): static
    {
        $this->siteweb = $siteweb;

        return $this;
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produit $produit): static
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
            $produit->setProduit($this);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): static
    {
        if ($this->produits->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getProduit() === $this) {
                $produit->setProduit(null);
            }
        }

        return $this;
    }
}
