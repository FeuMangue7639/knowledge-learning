<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CourseControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        // ✅ Crée un client AVANT de récupérer l'EntityManager
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);

        // ✅ Supprime tous les utilisateurs avant chaque test
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->entityManager->flush();
    }

    public function testAccessMyCourses(): void
    {
        // ✅ Création d'un utilisateur unique pour éviter les conflits
        $user = new User();
        $user->setEmail('testuser' . uniqid() . '@example.com'); // Evite les doublons
        $user->setUsername('TestUser' . uniqid()); // Evite les doublons
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // ✅ Connexion de l'utilisateur
        $this->client->loginUser($user);

        // ✅ Accès à la page "Mes Cours"
        $this->client->request('GET', '/my-courses');

        // ✅ Vérification que la réponse est bien 200 (OK)
        $this->assertResponseIsSuccessful();
    }
}
