<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Tests the user login process.
 */
class LoginTest extends WebTestCase
{
    /**
     * Tests if a user can log in successfully.
     *
     * @return void
     */
    public function testLogin(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        // ✅ Deleting existing users to avoid uniqueness errors
        $entityManager->createQuery('DELETE FROM App\Entity\User')->execute();

        // ✅ Creating a test user
        $user = new User();
        $user->setUsername('TestUser' . uniqid());
        $user->setEmail('test' . uniqid() . '@example.com');
        $user->setPassword($passwordHasher->hashPassword($user, 'password123'));
        $user->setRoles(['ROLE_USER']);

        $entityManager->persist($user);
        $entityManager->flush();

        // ✅ User login via Symfony
        $client->loginUser($user);

        // ✅ Access a protected page to verify login
        $client->request('GET', '/shop');

        // ✅ Connection verification successful (HTTP status 200 or redirection)
        $this->assertResponseIsSuccessful();
    }
}

