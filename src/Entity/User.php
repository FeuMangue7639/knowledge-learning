<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Représente un utilisateur de l'application.
 * Un utilisateur peut avoir plusieurs rôles (utilisateur, admin...), faire des achats,
 * obtenir des certifications et valider des leçons.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * Identifiant unique de l'utilisateur.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Nom d'utilisateur (doit être unique).
     */
    #[ORM\Column(type: "string", length: 180, unique: true)]
    private ?string $username = null;

    /**
     * Adresse e-mail de l'utilisateur (doit être unique).
     */
    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    /**
     * Mot de passe encodé de l'utilisateur.
     */
    #[ORM\Column(length: 255)]
    private ?string $password = null;

    /**
     * Statut indiquant si le compte a été vérifié par l'utilisateur (ex: via e-mail).
     */
    #[ORM\Column(type: "boolean")]
    private ?bool $isVerified = false;

    /**
     * Tableau des rôles attribués à l'utilisateur (par défaut : ROLE_USER).
     */
    #[ORM\Column(type: "json")]
    private array $roles = ["ROLE_USER"];

    /**
     * Date de création du compte utilisateur.
     */
    #[ORM\Column(type: "datetime_immutable", options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Adresse postale de l'utilisateur (optionnelle).
     */
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $address = null;

    /**
     * Liste des achats effectués par l'utilisateur.
     */
    #[ORM\OneToMany(targetEntity: Purchase::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $purchases;

    /**
     * Liste des certifications obtenues par l'utilisateur.
     */
    #[ORM\OneToMany(targetEntity: Certification::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $certifications;

    /**
     * Liste des leçons validées par l'utilisateur.
     */
    #[ORM\OneToMany(targetEntity: LessonValidation::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $lessonValidations;

    /**
     * Constructeur : initialise les collections et la date de création.
     */
    public function __construct()
    {
        $this->purchases = new ArrayCollection();
        $this->certifications = new ArrayCollection();
        $this->lessonValidations = new ArrayCollection();
        $this->roles = ["ROLE_USER"];
        $this->createdAt = new \DateTimeImmutable();
        $this->isVerified = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;
        return $this;
    }

    /**
     * Méthode obligatoire par UserInterface : identifiant unique pour la sécurité.
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * Méthode obligatoire par PasswordAuthenticatedUserInterface.
     * Permettrait de nettoyer les données sensibles si nécessaire.
     */
    public function eraseCredentials(): void
    {
    }

    public function getPurchases(): Collection
    {
        return $this->purchases;
    }

    public function addPurchase(Purchase $purchase): static
    {
        if (!$this->purchases->contains($purchase)) {
            $this->purchases->add($purchase);
            $purchase->setUser($this);
        }
        return $this;
    }

    public function removePurchase(Purchase $purchase): static
    {
        if ($this->purchases->removeElement($purchase)) {
            if ($purchase->getUser() === $this) {
                $purchase->setUser(null);
            }
        }
        return $this;
    }

    public function getCertifications(): Collection
    {
        return $this->certifications;
    }

    public function addCertification(Certification $certification): static
    {
        if (!$this->certifications->contains($certification)) {
            $this->certifications->add($certification);
            $certification->setUser($this);
        }
        return $this;
    }

    public function removeCertification(Certification $certification): static
    {
        if ($this->certifications->removeElement($certification)) {
            if ($certification->getUser() === $this) {
                $certification->setUser(null);
            }
        }
        return $this;
    }

    public function getLessonValidations(): Collection
    {
        return $this->lessonValidations;
    }

    public function addLessonValidation(LessonValidation $lessonValidation): static
    {
        if (!$this->lessonValidations->contains($lessonValidation)) {
            $this->lessonValidations->add($lessonValidation);
            $lessonValidation->setUser($this);
        }
        return $this;
    }

    public function removeLessonValidation(LessonValidation $lessonValidation): static
    {
        if ($this->lessonValidations->removeElement($lessonValidation)) {
            if ($lessonValidation->getUser() === $this) {
                $lessonValidation->setUser(null);
            }
        }
        return $this;
    }
}
