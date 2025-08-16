<?php


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity() ]
#[ORM\Table(name:"client") ]
class Client extends User implements UserInterface
{
     #[ORM\OneToMany(targetEntity:"App\Entity\Commande", mappedBy:"user") ]
    private $commandes;

    #[ORM\Column(type:"string",length:255, nullable:true) ]
    private $compte;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $niu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $boitepostale = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rccm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $siteweb = null;

    public function __construct()
    {
        parent::__construct();
        $this->commandes = new ArrayCollection();
    }

    public function getCompte(): ?string
    {
        return $this->compte;
    }

    public function setCompte(?string $compte): self
    {
        $this->compte = $compte;

        return $this;
    }

    public function getNiu(): ?string
    {
        return $this->niu;
    }

    public function setNiu(string $niu): static
    {
        $this->niu = $niu;

        return $this;
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

    public function getRccm(): ?string
    {
        return $this->rccm;
    }

    public function setRccm(?string $rccm): static
    {
        $this->rccm = $rccm;

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
     * @return Collection<int, Commande>
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): static
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes->add($commande);
            $commande->setUser($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): static
    {
        if ($this->commandes->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getUser() === $this) {
                $commande->setUser(null);
            }
        }

        return $this;
    }

    // public function getNumCommande(){
    //     return str_pad(count($this->commandes), 4, '0', STR_PAD_LEFT);
    // }
}
