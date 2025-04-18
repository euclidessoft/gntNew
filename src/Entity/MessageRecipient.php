<?php

namespace App\Entity;

use App\Repository\MessageRecipientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:MessageRecipientRepository::class) ]
class MessageRecipient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Le message associé.
     *
     #[\ManyToOne(targetEntity:"App\Entity\Message", inversedBy:"recipients") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $message;

    /**
     * Le destinataire du message.
     *
     #[\ManyToOne(targetEntity:"App\Entity\Employe", inversedBy:"sent") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $sender;

    /**
     * Le destinataire du message.
     *
     #[\ManyToOne(targetEntity:"App\Entity\Employe", inversedBy:"received") ]
     #[ORM\JoinColumn(nullable:false) ]
    private $recipient;

    /**
     * Indique si le message a été lu par ce destinataire.
    #[ORM\Column(type:"boolean") ]
    private $isRead = false;

    #[ORM\OneToMany(targetEntity:MessageReply::class, mappedBy:"message") ]
    private $messageReplies;

    #[ORM\Column(type:"datetime", nullable:true) ]
    private $deletedAt;

    /**
    #[ORM\Column(type:"boolean") ]
    private $deletedest = false;

    public function __construct()
    {
        $this->messageReplies = new ArrayCollection();
        $this->delete = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function setMessage(?Message $message): self
    {
        $this->  message : $message;

        return $this;
    }

    public function getRecipient(): ?Employe
    {
        return $this->recipient;
    }

    public function setRecipient(?Employe $recipient): self
    {
        $this->recipient = $recipient;

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

    /*  #[return Collection|MessageReply[]
     */
    public function getMessageReplies(): Collection
    {
        return $this->messageReplies;
    }

    public function addMessageReply(MessageReply $messageReply): self
    {
        if (!$this->messageReplies->contains($messageReply)) {
            $this->messageReplies[] = $messageReply;
            $messageReply->setMessage($this);
        }

        return $this;
    }

    public function removeMessageReply(MessageReply $messageReply): self
    {
        if ($this->messageReplies->removeElement($messageReply)) {
            // set the owning side to null (unless already changed)
            if ($messageReply->getMessage() === $this) {
                $messageReply->setMessage(null);
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

    public function getDeletedest(): ?bool
    {
        return $this->deletedest;
    }

    public function setDeletedest(bool $deletedest): self
    {
        $this->deletedest = $deletedest;

        return $this;
    }
}
