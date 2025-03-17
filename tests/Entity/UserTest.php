<?php

namespace App\Tests\Entity;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Tests the creation and registration of a user in the database.
 */
class UserTest extends KernelTestCase
{
    /**
     * Tests if a user can be created and saved in the database.
     *
     * @return void
     */
    public function testCreateUser(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        // ✅ Remove old users to avoid uniqueness errors
        $entityManager->createQuery('DELETE FROM App\Entity\User')->execute();

        // ✅ Creating a unique user
        $user = new User();
        $user->setUsername('TestUser' . uniqid()); // 🔄 Generating a unique username
        $user->setEmail('test' . uniqid() . '@example.com'); // 🔄 Generating a unique email
        $user->setPassword($passwordHasher->hashPassword($user, 'password123'));
        $user->setRoles(['ROLE_USER']);

        // ✅ Database registration
        $entityManager->persist($user);
        $entityManager->flush();

        // ✅ Check that the user exists in the database
        $savedUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
        $this->assertNotNull($savedUser, 'L\'utilisateur doit être enregistré en base de données');
        $this->assertSame($user->getEmail(), $savedUser->getEmail());
        $this->assertSame($user->getUsername(), $savedUser->getUsername());
    }
}
