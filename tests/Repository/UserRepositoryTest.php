<?php

namespace App\Tests\Repository;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Tests the functionality of the UserRepository.
 */
class UserRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * Initializes the environment and cleans the database.
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        // ✅ Cleaning the base to avoid conflicts
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
    }

    /**
     * Check that a user can be retrieved by email.
     *
     * @return void
     */
    public function testFindUserByEmail(): void
    {
        // ✅ Creating a test user
        $user = new User();
        $user->setUsername('TestUser');
        $user->setEmail('testuser@example.com');
        $user->setAddress('10 rue de la base');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // ✅ Retrieving from the repository
        $userRepository = $this->entityManager->getRepository(User::class);
        $foundUser = $userRepository->findOneBy(['email' => 'testuser@example.com']);

        // ✅ Verifications
        $this->assertNotNull($foundUser, "L'utilisateur doit être trouvé en base.");
        $this->assertEquals('TestUser', $foundUser->getUsername(), "Le nom d'utilisateur doit correspondre.");
        $this->assertEquals('10 rue de la base', $foundUser->getAddress(), "L'adresse doit correspondre.");
    }

    /**
     * Checks that a user's password is secure.
     *
     * @return void
     */
    public function testSecurityNoAccessToPassword(): void
    {
        // ✅ Creating a user
        $user = new User();
        $user->setUsername('SecureUser');
        $user->setEmail('secureuser@example.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'SecurePass!'));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // ✅ Security Check
        $foundUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'secureuser@example.com']);
        $this->assertStringStartsWith('$2y$', $foundUser->getPassword(), "Le mot de passe doit être hashé.");
    }

    /**
     * Cleans the database after testing.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
