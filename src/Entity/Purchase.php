<?php

namespace App\Entity;

use App\Repository\PurchaseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité représentant un achat effectué par un utilisateur.
 * L'achat peut concerner soit un cours complet, soit une leçon individuelle.
 */
#[ORM\Entity(repositoryClass: PurchaseRepository::class)]
class Purchase
{
    /**
     * Identifiant unique de l'achat.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Utilisateur ayant effectué l'achat.
     */
    #[ORM\ManyToOne(inversedBy: 'purchases')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * Cours acheté (peut être null si l'achat concerne uniquement une leçon).
     */
    #[ORM\ManyToOne(inversedBy: 'purchases')]
    private ?Course $course = null;

    /**
     * Leçon achetée (peut être null si l'achat concerne un cours complet).
     */
    #[ORM\ManyToOne(inversedBy: 'purchases')]
    private ?Lesson $lesson = null;

    /**
     * Statut de l'achat (par défaut : 'pending').
     * Peut être utilisé pour suivre les paiements ou confirmations (ex: 'completed', 'failed').
     */
    #[ORM\Column(length: 255)]
    private ?string $status = 'pending';

    /**
     * Date et heure de création de l'achat.
     */
    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * Constructeur par défaut.
     * Initialise la date de création à maintenant et le statut à 'pending'.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = 'pending';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;
        return $this;
    }

    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    public function setLesson(?Lesson $lesson): static
    {
        $this->lesson = $lesson;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }
}
