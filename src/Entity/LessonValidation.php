<?php

namespace App\Entity;

use App\Repository\LessonValidationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité représentant la validation d'une leçon par un utilisateur.
 * Elle permet de savoir si un utilisateur a terminé une leçon donnée.
 */
#[ORM\Entity(repositoryClass: LessonValidationRepository::class)]
class LessonValidation
{
    /**
     * Identifiant unique de la validation.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Utilisateur ayant validé la leçon.
     */
    #[ORM\ManyToOne(inversedBy: 'lessonValidations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * Leçon concernée par la validation.
     */
    #[ORM\ManyToOne(inversedBy: 'lessonValidations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lesson $lesson = null;

    /**
     * Indique si la leçon a été complétée (true = validée, false = non validée).
     */
    #[ORM\Column]
    private ?bool $isCompleted = false;

    /**
     * Constructeur par défaut initialisant la leçon comme non validée.
     */
    public function __construct()
    {
        $this->isCompleted = false;
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

    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    public function setLesson(?Lesson $lesson): static
    {
        $this->lesson = $lesson;
        return $this;
    }

    public function isCompleted(): ?bool
    {
        return $this->isCompleted;
    }

    public function setIsCompleted(bool $isCompleted): static
    {
        $this->isCompleted = $isCompleted;
        return $this;
    }
}
