<?php

namespace App\Entity;

use App\Repository\ReponseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:ReponseRepository::class) ]
class Reponse
{
    #[ORM\ManyToOne(targetEntity:"App\Entity\User") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $user;

    #[ORM\ManyToOne(targetEntity:"App\Entity\Reclamation") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $reclamation;

    #[ORM\Column(type:"datetime") ]
    private $date;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type:"text") ]
    private $message;




    public function __construct(User $user, Reclamation $reclamation)
    {
        $this->user = $user;
        $this->reclamation = $reclamation;
        $this->date = new \Datetime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getReclamation(): ?Reclamation
    {
        return $this->reclamation;
    }

    public function setReclamation(?Reclamation $reclamation): self
    {
        $this->reclamation = $reclamation;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

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
}
