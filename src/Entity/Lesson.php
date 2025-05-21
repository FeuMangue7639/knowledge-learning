<?php

namespace App\Entity;

use App\Repository\LessonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Représente une leçon appartenant à un cours.
 * Une leçon contient du contenu, un prix, et peut être validée par les utilisateurs.
 */
#[ORM\Entity(repositoryClass: LessonRepository::class)]
class Lesson
{
    /**
     * Identifiant unique de la leçon.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Titre de la leçon.
     */
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    /**
     * Contenu pédagogique de la leçon.
     */
    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    /**
     * Prix individuel de la leçon (en euros).
     * Ce champ est optionnel car une leçon peut être achetée via un cours.
     */
    #[ORM\Column(nullable: true)]
    private ?float $price = null;

    /**
     * Cours auquel cette leçon est rattachée.
     */
    #[ORM\ManyToOne(inversedBy: 'lessons')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    /**
     * @var Collection<int, LessonValidation>
     * Liste des validations de cette leçon par les utilisateurs.
     */
    #[ORM\OneToMany(targetEntity: LessonValidation::class, mappedBy: 'lesson', orphanRemoval: true)]
    private Collection $lessonValidations;

    public function __construct()
    {
        $this->lessonValidations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): static
    {
        $this->price = $price;
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

    /**
     * Retourne la collection des validations de cette leçon.
     */
    public function getLessonValidations(): Collection
    {
        return $this->lessonValidations;
    }

    /**
     * Ajoute une validation à la leçon.
     */
    public function addLessonValidation(LessonValidation $lessonValidation): static
    {
        if (!$this->lessonValidations->contains($lessonValidation)) {
            $this->lessonValidations->add($lessonValidation);
            $lessonValidation->setLesson($this);
        }

        return $this;
    }

    /**
     * Supprime une validation de la leçon.
     */
    public function removeLessonValidation(LessonValidation $lessonValidation): static
    {
        if ($this->lessonValidations->removeElement($lessonValidation)) {
            // Supprimer la relation côté LessonValidation si elle est toujours liée à cette leçon
            if ($lessonValidation->getLesson() === $this) {
                $lessonValidation->setLesson(null);
            }
        }

        return $this;
    }
}
