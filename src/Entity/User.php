<?php
// src/Entity/User.php

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


 #[ORM\Entity(repositoryClass: UserRepository::class) ]
#[ORM\Table(name:"user") ]
#[ORM\InheritanceType("JOINED") ]
#[ORM\DiscriminatorColumn(name:"typeuser", type:"string") ]
#[ORM\DiscriminatorMap([
    'client' => Client::class,
    'employe' => Employe::class,
    'laboratoie' => Laboratoire::class,
])]
#[UniqueEntity(
    fields: ['email'],
    message: 'Cet email existe déjà.'
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{

    const jobs = [

        'ADMINISTRATEUR' => 'ADMINISTRATEUR',
        'SUPERVISEUR' => 'SUPERVISEUR',
        'FINANCE' => 'FINANCE' ,
        'CAISSIERE' => 'CAISSIERE' ,
        'RH' => 'RH' ,
        'STOCK' => 'STOCK' ,
        'LIVREUR' => 'LIVREUR' ,
        'EMPLOYE' => 'EMPLOYE' ,
    ];

    const jobsimple = [

        'FINANCE' => 'FINANCE' ,
        'RH' => 'RH' ,
        'STOCK' => 'STOCK' ,
        'LIVREUR' => 'LIVREUR' ,
        'EMPLOYE' => 'EMPLOYE' ,
    ];

    #[ORM\ManyToOne(targetEntity:Pharmacie::class) ]
    #[ORM\JoinColumn(nullable:true) ]
    private $pharmacie;


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type:"string", length:255, nullable:true) ] 
    private $username;

    #[ORM\Column(type:"string", length:255) ]
    #[Assert\Length(min:"6", minMessage:"trop court, 6 caracrteres mini")]
    private $password;

    #[Assert\EqualTo(propertyPath:"password", message:"non conforme") ]
    public $confirm;

    public $test;
    public $niveau2;

    #[ORM\Column(type:"string", length:255) ]
    #[Assert\NotBlank(message:"champ obligatoire") ]
    #[Assert\Email(message:"email invalide") ]
    private $email;

    #[ORM\Column(type:"string", length:255, nullable:true) ] 
    #[Assert\NotBlank(message:"champ obligatoire") ]
    private $prenom;

    #[ORM\Column(type:"string", length:255, nullable:true) ]
    #[Assert\NotBlank(message:"champ obligatoire") ]
    private $nom;


    #[ORM\Column(type:"string", length:255) ]
    #[Assert\NotBlank(message:"champ obligatoire")]
    private $phone;                   

    #[ORM\Column(type:"string", length:255, nullable:true) ]
    #[Assert\NotBlank(message:"champ obligatoire")]
    private $adresse;

    #[ORM\Column(type:"string", length:255) ]
    private $fonction;

    #[ORM\Column(type:"array", nullable:true) ] 
    protected $roles = [];

    #[ORM\Column(type:"string", length:255, nullable:true) ] 
    private $resetToken;

    #[ORM\Column(type:"boolean") ]
    private $enabled;

    #[ORM\Column(type:"boolean") ]
    private $client;

    #[ORM\Column(type:"boolean") ]
    private $livreur;


    #[ORM\OneToMany(targetEntity:Transfert::class, mappedBy:"user") ]
    private $transferts;

    #[ORM\OneToMany(targetEntity:Depense::class, mappedBy:"user") ]
    private $depenses;

    #[ORM\OneToMany(targetEntity:Message::class, mappedBy:"sender", orphanRemoval:true) ]
    private $sent;

    #[ORM\OneToMany(targetEntity:MessageRecipient::class, mappedBy:"recipient", orphanRemoval:true) ]
    private $received;


    public function __construct()
    {
        $this->enabled = false;
        $this->client = false;
        $this->livreur = false;
        $this->transferts = new ArrayCollection();
        $this->depenses = new ArrayCollection();
        $this->sent = new ArrayCollection();
        $this->received = new ArrayCollection();
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function setFonction(?string $fonction): self
    {
        $this->fonction = $fonction;

        return $this;
    }

    public function getsalt(): ?array
    {
        return null;
    }

    public function eraseCredentials()
    {
        return null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * The public representation of the user (e.g. a username, an email address, etc.)
     *
     * @see UserInterface
    */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
    */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getTest(): ?string
    {
        return $this->test;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getClient(): ?bool
    {
        return $this->client;
    }

    public function setClient(bool $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getLivreur(): ?bool
    {
        return $this->livreur;
    }

    public function setLivreur(bool $livreur): self
    {
        $this->livreur = $livreur;

        return $this;
    }

/*    /*  #[return Collection|Transfert[] ] */
    public function getTransferts(): Collection
    {
        return $this->transferts;
    }

    public function addTransfert(Transfert $transfert): self
    {
        if (!$this->transferts->contains($transfert)) {
            $this->transferts[] = $transfert;
            $transfert->setUser($this);
        }

        return $this;
    }

    public function removeTransfert(Transfert $transfert): self
    {
        if ($this->transferts->removeElement($transfert)) {
            // set the owning side to null (unless already changed)
            if ($transfert->getUser() === $this) {
                $transfert->setUser(null);
            }
        }

        return $this;
    }

/*    /*  #[return Collection|Depense[] */
    public function getDepenses(): Collection
    {
        return $this->depenses;
    }

    public function addDepense(Depense $depense): self
    {
        if (!$this->depenses->contains($depense)) {
            $this->depenses[] = $depense;
            $depense->setUser($this);
        }

        return $this;
    }

    public function removeDepense(Depense $depense): self
    {
        if ($this->depenses->removeElement($depense)) {
            // set the owning side to null (unless already changed)
            if ($depense->getUser() === $this) {
                $depense->setUser(null);
            }
        }

        return $this;
    }


/*    /*  #[return Collection|MessageRecipient[] */
    public function getReceived(): Collection
    {
        return $this->received;
    }

    public function addReceived(MessageRecipient $received): self
    {
        if (!$this->received->contains($received)) {
            $this->received[] = $received;
            $received->setRecipient($this);
        }

        return $this;
    }

    public function removeReceived(MessageRecipient $received): self
    {
        if ($this->received->removeElement($received)) {
            // set the owning side to null (unless already changed)
            if ($received->getRecipient() === $this) {
                $received->setRecipient(null);
            }
        }

        return $this;
    }

/*   /*  #[return Collection|Message[] */
    public function getSent(): Collection
    {
        return $this->sent;
    }

    public function addSent(Message $sent): self
    {
        if (!$this->sent->contains($sent)) {
            $this->sent[] = $sent;
            $sent->setSender($this);
        }

        return $this;
    }

    public function removeSent(Message $sent): self
    {
        if ($this->sent->removeElement($sent)) {
            // set the owning side to null (unless already changed)
            if ($sent->getSender() === $this) {
                $sent->setSender(null);
            }
        }

        return $this;
    }

    
    public function setNiveau2(string $niveau2): self
    {
        $this->niveau2 = $niveau2;

        return $this;
    }

    public function getniveau2(): ?string
    {
        return $this->niveau2;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function isClient(): ?bool
    {
        return $this->client;
    }

    public function isLivreur(): ?bool
    {
        return $this->livreur;
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