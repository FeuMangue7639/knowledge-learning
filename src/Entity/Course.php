<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Représente un cours composé de plusieurs leçons.
 * Chaque cours appartient à une catégorie et peut être acheté par les utilisateurs.
 */
#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course
{
    /**
     * Identifiant unique du cours.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Titre du cours.
     */
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    /**
     * Prix du cours (en euros).
     */
    #[ORM\Column]
    private ?float $price = null;

    /**
     * Catégorie du cours (ex. : Musique, Informatique, etc.).
     */
    #[ORM\Column(length: 255)]
    private ?string $category = null;

    /**
     * Description détaillée du cours (facultative).
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * Date de création du cours (initialisée automatiquement).
     */
    #[ORM\Column(type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * Liste des leçons appartenant à ce cours.
     */
    #[ORM\OneToMany(targetEntity: Lesson::class, mappedBy: 'course', orphanRemoval: true)]
    private Collection $lessons;

    /**
     * Liste des achats de ce cours par les utilisateurs.
     */
    #[ORM\OneToMany(targetEntity: Purchase::class, mappedBy: 'course', orphanRemoval: true)]
    private Collection $purchases;

    /**
     * Liste des certifications obtenues pour ce cours.
     */
    #[ORM\OneToMany(targetEntity: Certification::class, mappedBy: 'course', orphanRemoval: true)]
    private Collection $certifications;

    /**
     * Nom du fichier image associé à ce cours.
     */
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $image = null;

    public function __construct()
    {
        $this->lessons = new ArrayCollection();
        $this->purchases = new ArrayCollection();
        $this->certifications = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
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

    /**
     * Retourne toutes les leçons associées à ce cours.
     */
    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    public function addLesson(Lesson $lesson): static
    {
        if (!$this->lessons->contains($lesson)) {
            $this->lessons->add($lesson);
            $lesson->setCourse($this);
        }

        return $this;
    }

    public function removeLesson(Lesson $lesson): static
    {
        if ($this->lessons->removeElement($lesson)) {
            if ($lesson->getCourse() === $this) {
                $lesson->setCourse(null);
            }
        }

        return $this;
    }

    /**
     * Retourne tous les achats associés à ce cours.
     */
    public function getPurchases(): Collection
    {
        return $this->purchases;
    }

    public function addPurchase(Purchase $purchase): static
    {
        if (!$this->purchases->contains($purchase)) {
            $this->purchases->add($purchase);
            $purchase->setCourse($this);
        }

        return $this;
    }

    public function removePurchase(Purchase $purchase): static
    {
        if ($this->purchases->removeElement($purchase)) {
            if ($purchase->getCourse() === $this) {
                $purchase->setCourse(null);
            }
        }

        return $this;
    }

    /**
     * Retourne toutes les certifications associées à ce cours.
     */
    public function getCertifications(): Collection
    {
        return $this->certifications;
    }

    public function addCertification(Certification $certification): static
    {
        if (!$this->certifications->contains($certification)) {
            $this->certifications->add($certification);
            $certification->setCourse($this);
        }

        return $this;
    }

    public function removeCertification(Certification $certification): static
    {
        if ($this->certifications->removeElement($certification)) {
            if ($certification->getCourse() === $this) {
                $certification->setCourse(null);
            }
        }

        return $this;
    }

    /**
     * Retourne le nom du fichier image associé au cours.
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * Définit le nom du fichier image pour ce cours.
     */
    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }
}
