<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Tests the registration of a new user.
 */
class UserRegistrationTest extends WebTestCase
{
    /**
     * Tests if a user can register successfully.
     *
     * @return void
     */
    public function testUserRegistration(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        // ✅ Deleting existing users to avoid uniqueness errors
        $entityManager->createQuery('DELETE FROM App\Entity\User')->execute();

        // ✅ Access the registration form
        $crawler = $client->request('GET', '/register');

        // ✅ Check that the registration page is displayed correctly
        $this->assertResponseIsSuccessful();

        // ✅ Form selection and submission
        $form = $crawler->selectButton('Créer un compte')->form([
            'registration_form[username]' => 'newuser',
            'registration_form[email]' => 'newuser@example.com',
            'registration_form[address]' => '10 rue des tests',
            'registration_form[plainPassword]' => 'Password123!',
        ]);

        $client->submit($form);

        // ✅ Check that the user has been registered in the database
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'newuser@example.com']);
        $this->assertNotNull($user, 'L\'utilisateur doit être enregistré en base.');
    }
}
