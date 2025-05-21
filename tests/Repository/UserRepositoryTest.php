<?php

namespace App\Tests\Repository;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Classe de test pour valider le comportement du UserRepository.
 */
class UserRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * Initialisation de l'environnement de test et nettoyage de la base de données.
     */
    protected function setUp(): void
    {
        // Démarrage du noyau Symfony
        self::bootKernel();

        // Récupération des services nécessaires
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        // Suppression des utilisateurs existants pour éviter les conflits
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
    }

    /**
     * Vérifie que l'on peut retrouver un utilisateur en base via son adresse email.
     */
    public function testFindUserByEmail(): void
    {
        // ✅ Création d'un utilisateur de test
        $user = new User();
        $user->setUsername('TestUser');
        $user->setEmail('testuser@example.com');
        $user->setAddress('10 rue de la base');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Recherche de l'utilisateur via son email
        $userRepository = $this->entityManager->getRepository(User::class);
        $foundUser = $userRepository->findOneBy(['email' => 'testuser@example.com']);

        // ✅ Vérifications
        $this->assertNotNull($foundUser, "L'utilisateur doit être trouvé en base.");
        $this->assertEquals('TestUser', $foundUser->getUsername(), "Le nom d'utilisateur doit correspondre.");
        $this->assertEquals('10 rue de la base', $foundUser->getAddress(), "L'adresse doit correspondre.");
    }

    /**
     * Vérifie que le mot de passe d'un utilisateur est bien sécurisé (hashé).
     */
    public function testSecurityNoAccessToPassword(): void
    {
        // ✅ Création d'un utilisateur avec mot de passe sécurisé
        $user = new User();
        $user->setUsername('SecureUser');
        $user->setEmail('secureuser@example.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'SecurePass!'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Vérification que le mot de passe est bien hashé
        $foundUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'secureuser@example.com']);
        $this->assertStringStartsWith('$2y$', $foundUser->getPassword(), "Le mot de passe doit être hashé.");
    }

    /**
     * Nettoyage de la base et fermeture de l'EntityManager après les tests.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
