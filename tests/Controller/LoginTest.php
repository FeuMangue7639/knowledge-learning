<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Classe de test pour vérifier le processus de connexion d'un utilisateur.
 */
class LoginTest extends WebTestCase
{
    /**
     * Vérifie qu'un utilisateur peut se connecter correctement.
     *
     * Étapes du test :
     * - Création d'un client simulateur de requêtes HTTP
     * - Création et sauvegarde d'un utilisateur fictif
     * - Connexion via `loginUser()`
     * - Requête vers une page sécurisée pour valider que la session est active
     *
     * @return void
     */
    public function testLogin(): void
    {
        // Création du client pour simuler une session HTTP
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        // ✅ Nettoyage : suppression de tous les utilisateurs pour éviter les doublons d'e-mail
        $entityManager->createQuery('DELETE FROM App\Entity\User')->execute();

        // ✅ Création d'un utilisateur de test
        $user = new User();
        $user->setUsername('TestUser' . uniqid()); // nom unique
        $user->setEmail('test' . uniqid() . '@example.com'); // email unique
        $user->setPassword($passwordHasher->hashPassword($user, 'password123')); // mot de passe chiffré
        $user->setRoles(['ROLE_USER']);

        // Enregistrement dans la base de données
        $entityManager->persist($user);
        $entityManager->flush();

        // ✅ Simulation de la connexion de l'utilisateur
        $client->loginUser($user);

        // ✅ Accès à une page protégée pour vérifier que l'utilisateur est bien connecté
        $client->request('GET', '/shop');

        // ✅ On vérifie que la réponse est un succès (statut HTTP 200, ou redirection si authentifié)
        $this->assertResponseIsSuccessful();
    }
}
