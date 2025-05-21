<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Classe de test pour vérifier le processus d'inscription d'un nouvel utilisateur.
 */
class UserRegistrationTest extends WebTestCase
{
    /**
     * Teste si un utilisateur peut s'inscrire avec succès.
     *
     * Étapes du test :
     * - Nettoyage de la base utilisateurs pour éviter les doublons
     * - Accès au formulaire d'inscription
     * - Soumission du formulaire avec des données valides
     * - Vérification que l'utilisateur est bien enregistré en base
     *
     * @return void
     */
    public function testUserRegistration(): void
    {
        // ✅ Création du client HTTP pour simuler un navigateur
        $client = static::createClient();
        $container = static::getContainer();

        // ✅ Récupération des services nécessaires
        $entityManager = $container->get(EntityManagerInterface::class);
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        // ✅ Suppression des utilisateurs existants pour éviter les erreurs de contrainte UNIQUE
        $entityManager->createQuery('DELETE FROM App\Entity\User')->execute();

        // ✅ Requête GET vers la page d'inscription
        $crawler = $client->request('GET', '/register');

        // ✅ Vérification que la page de formulaire s'affiche correctement (statut HTTP 200)
        $this->assertResponseIsSuccessful();

        // ✅ Récupération du formulaire et remplissage avec des données valides
        $form = $crawler->selectButton('Créer un compte')->form([
            'registration_form[username]' => 'newuser',
            'registration_form[email]' => 'newuser@example.com',
            'registration_form[address]' => '10 rue des tests',
            'registration_form[plainPassword]' => 'Password123!', // mot de passe conforme aux contraintes
        ]);

        // ✅ Soumission du formulaire
        $client->submit($form);

        // ✅ Vérification que l'utilisateur a bien été enregistré en base
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'newuser@example.com']);
        $this->assertNotNull($user, 'L\'utilisateur doit être enregistré en base.');
    }
}
