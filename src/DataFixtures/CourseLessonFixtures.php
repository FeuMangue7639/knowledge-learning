<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Lesson;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Classe CourseLessonFixtures
 * 
 * Cette fixture permet de préremplir la base de données avec un ensemble
 * de cours et de leçons. Elle est utile pour les tests et le développement.
 */
class CourseLessonFixtures extends Fixture
{
    /**
     * Méthode appelée automatiquement par Doctrine pour charger les données
     * de test dans la base de données.
     */
    public function load(ObjectManager $manager): void
    {
        // Données structurées par catégorie → chaque catégorie contient des cours,
        // et chaque cours contient des leçons.
        $data = [
            "Musique" => [
                [
                    "title" => "Cursus d'initiation à la guitare",
                    "price" => 50,
                    "lessons" => [
                        ["title" => "Découverte de l'instrument", "price" => 26],
                        ["title" => "Les accords et les gammes", "price" => 26]
                    ]
                ],
                [
                    "title" => "Cursus d'initiation au piano",
                    "price" => 50,
                    "lessons" => [
                        ["title" => "Découverte de l'instrument", "price" => 26],
                        ["title" => "Les accords et les gammes", "price" => 26]
                    ]
                ]
            ],
            "Informatique" => [
                [
                    "title" => "Cursus d'initiation au développement web",
                    "price" => 60,
                    "lessons" => [
                        ["title" => "Les langages Html et CSS", "price" => 32],
                        ["title" => "Dynamiser votre site avec Javascript", "price" => 32]
                    ]
                ]
            ],
            "Jardinage" => [
                [
                    "title" => "Cursus d'initiation au jardinage",
                    "price" => 30,
                    "lessons" => [
                        ["title" => "Les outils du jardinier", "price" => 16],
                        ["title" => "Jardiner avec la lune", "price" => 16]
                    ]
                ]
            ],
            "Cuisine" => [
                [
                    "title" => "Cursus d'initiation à la cuisine",
                    "price" => 44,
                    "lessons" => [
                        ["title" => "Les modes de cuisson", "price" => 23],
                        ["title" => "Les saveurs", "price" => 23]
                    ]
                ],
                [
                    "title" => "Cursus d'initiation à l’art du dressage culinaire",
                    "price" => 48,
                    "lessons" => [
                        ["title" => "Mettre en œuvre le style dans l’assiette", "price" => 26],
                        ["title" => "Harmoniser un repas à quatre plats", "price" => 26]
                    ]
                ]
            ]
        ];

        // Association des titres de cours avec des noms d’images.
        $courseImages = [
            "Cursus d'initiation à la guitare" => "guitare.jpg",
            "Cursus d'initiation au piano" => "piano.jpg",
            "Cursus d'initiation au développement web" => "developpeur.jpg",
            "Cursus d'initiation au jardinage" => "jardinage.jpg",
            "Cursus d'initiation à la cuisine" => "cuisine.jpg",
            "Cursus d'initiation à l’art du dressage culinaire" => "dressage.jpg"
        ];

        // Parcours des données pour créer les entités
        foreach ($data as $category => $courses) {
            foreach ($courses as $courseData) {
                $title = $courseData["title"];

                // Création du cours
                $course = new Course();
                $course->setTitle($title)
                       ->setPrice($courseData["price"])
                       ->setCategory($category)
                       ->setCreatedAt(new \DateTimeImmutable())
                       ->setImage($courseImages[$title] ?? 'placeholder.jpg'); // Image par défaut

                $manager->persist($course); // Enregistrement du cours

                // Création des leçons liées à ce cours
                foreach ($courseData["lessons"] as $lessonData) {
                    $lesson = new Lesson();
                    $lesson->setTitle($lessonData["title"])
                           ->setPrice($lessonData["price"])
                           ->setCourse($course)
                           ->setContent("Contenu de la leçon..."); // Exemple de contenu par défaut

                    $manager->persist($lesson);
                }
            }
        }

        // Écriture des entités en base de données
        $manager->flush();
    }
}
