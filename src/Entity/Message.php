<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:MessageRepository::class) ]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type:"string", length:255) ]
    private $title;

    #[ORM\Column(type:"text") ]
    private $message;

    #[ORM\Column(type:"datetime") ]
    private $created_at;

    #[ORM\ManyToOne(targetEntity:Employe::class, inversedBy:"sent") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $sender;

    /**
     * Destinataires associés (relation avec MessageRecipient).
     */
     #[ORM\OneToMany(targetEntity:"App\Entity\MessageRecipient", mappedBy:"message", cascade:["persist", "remove"]) ]
    private $recipients;

    #[ORM\Column(type:"datetime", nullable:true) ]
    private $deletedAt;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->recipients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = strip_tags($message);

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getSender(): ?Employe
    {
        return $this->sender;
    }

    public function setSender(?Employe $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    /*  #[return Collection|MessageRecipient[]
     */
    public function getRecipients(): Collection
    {
        return $this->recipients;
    }

    public function addRecipient(MessageRecipient $recipient): self
    {
        if (!$this->recipients->contains($recipient)) {
            $this->recipients[] = $recipient;
            $recipient->setMessage($this);
        }

        return $this;
    }

    public function removeRecipient(MessageRecipient $recipient): self
    {
        if ($this->recipients->removeElement($recipient)) {
            // set the owning side to null (unless already changed)
            if ($recipient->getMessage() === $this) {
                $recipient->setMessage(null);
            }
        }

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function delete(): void
    {
        $this->deletedAt = new \DateTime();
    }

    public function restore(): void
    {
        $this->deletedAt = null;
    }

}
