<?php

namespace App\Entity;

use App\Repository\CertificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité représentant une certification obtenue par un utilisateur après avoir terminé un cours.
 */
#[ORM\Entity(repositoryClass: CertificationRepository::class)]
class Certification
{
    /**
     * Identifiant unique de la certification (clé primaire).
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Utilisateur auquel est liée cette certification.
     * Un utilisateur peut avoir plusieurs certifications.
     */
    #[ORM\ManyToOne(inversedBy: 'certifications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * Cours correspondant à cette certification.
     * Un cours peut être certifié par plusieurs utilisateurs.
     */
    #[ORM\ManyToOne(inversedBy: 'certifications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    /**
     * Date de délivrance de la certification.
     * Par défaut, elle est initialisée à la date actuelle.
     */
    #[ORM\Column(type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $issuedAt = null;

    /**
     * Constructeur : initialise automatiquement la date de délivrance.
     */
    public function __construct()
    {
        $this->issuedAt = new \DateTimeImmutable();
    }

    /**
     * Retourne l'identifiant de la certification.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne l'utilisateur lié à la certification.
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Définit l'utilisateur pour cette certification.
     */
    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Retourne le cours associé à cette certification.
     */
    public function getCourse(): ?Course
    {
        return $this->course;
    }

    /**
     * Définit le cours pour cette certification.
     */
    public function setCourse(?Course $course): static
    {
        $this->course = $course;

        return $this;
    }

    /**
     * Retourne la date d’émission de la certification.
     */
    public function getIssuedAt(): ?\DateTimeImmutable
    {
        return $this->issuedAt;
    }

    /**
     * Définit manuellement la date d’émission de la certification.
     */
    public function setIssuedAt(\DateTimeImmutable $issuedAt): static
    {
        $this->issuedAt = $issuedAt;

        return $this;
    }
}
