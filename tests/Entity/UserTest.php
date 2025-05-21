<?php

namespace App\Tests\Entity;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Classe de test pour vérifier la création et l'enregistrement d'un utilisateur dans la base de données.
 */
class UserTest extends KernelTestCase
{
    /**
     * Teste si un utilisateur peut être correctement créé et sauvegardé en BDD.
     *
     * Étapes :
     * - Initialisation du noyau Symfony
     * - Récupération des services requis
     * - Suppression des anciens utilisateurs
     * - Création d'un utilisateur unique
     * - Enregistrement dans la base
     * - Vérification que l'utilisateur est bien présent avec les bonnes données
     *
     * @return void
     */
    public function testCreateUser(): void
    {
        // ✅ Démarre le noyau Symfony pour accéder aux services
        self::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        // ✅ Supprime les utilisateurs existants pour éviter les doublons d'email ou de nom d'utilisateur
        $entityManager->createQuery('DELETE FROM App\Entity\User')->execute();

        // ✅ Création d'un utilisateur avec des identifiants uniques
        $user = new User();
        $user->setUsername('TestUser' . uniqid()); // 🔄 Nom d'utilisateur unique
        $user->setEmail('test' . uniqid() . '@example.com'); // 🔄 Email unique
        $user->setPassword($passwordHasher->hashPassword($user, 'password123'));
        $user->setRoles(['ROLE_USER']);

        // ✅ Persistance et sauvegarde de l'utilisateur dans la base
        $entityManager->persist($user);
        $entityManager->flush();

        // ✅ Vérification que l'utilisateur a bien été enregistré
        $savedUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
        $this->assertNotNull($savedUser, 'L\'utilisateur doit être enregistré en base de données');
        $this->assertSame($user->getEmail(), $savedUser->getEmail());
        $this->assertSame($user->getUsername(), $savedUser->getUsername());
    }
}
