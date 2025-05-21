<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Classe AppFixtures
 * 
 * Cette classe est utilisée pour charger des données de test dans la base de données
 * lors du développement. Elle est utile pour pré-remplir certaines entités avec
 * des données fictives (utilisateurs, cours, leçons, etc.).
 * 
 * Pour exécuter ces fixtures, on utilise la commande :
 * php bin/console doctrine:fixtures:load
 */
class AppFixtures extends Fixture
{
    /**
     * Cette méthode est automatiquement appelée par Doctrine pour injecter des données.
     * 
     * @param ObjectManager $manager L’ObjectManager permet de persister les entités dans la base de données.
     */
    public function load(ObjectManager $manager): void
    {
        // 👉 Ici, on peut créer et enregistrer des entités à insérer dans la base.
        // Exemple :
        // $user = new User();
        // $user->setEmail('test@example.com');
        // $manager->persist($user);

        // Valide les entités persistées et les écrit en base de données
        $manager->flush();
    }
}
