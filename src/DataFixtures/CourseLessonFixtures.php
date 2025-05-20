<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Lesson;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CourseLessonFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
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

        // 📸 Association des images par titre
        $courseImages = [
            "Cursus d'initiation à la guitare" => "guitare.jpg",
            "Cursus d'initiation au piano" => "piano.jpg",
            "Cursus d'initiation au développement web" => "developpeur.jpg",
            "Cursus d'initiation au jardinage" => "jardinage.jpg",
            "Cursus d'initiation à la cuisine" => "cuisine.jpg",
            "Cursus d'initiation à l’art du dressage culinaire" => "dressage.jpg"
        ];

        foreach ($data as $category => $courses) {
            foreach ($courses as $courseData) {
                $title = $courseData["title"];
                $course = new Course();
                $course->setTitle($title)
                       ->setPrice($courseData["price"])
                       ->setCategory($category)
                       ->setCreatedAt(new \DateTimeImmutable())
                       ->setImage($courseImages[$title] ?? 'placeholder.jpg'); // 🔁 image par défaut si absente

                $manager->persist($course);

                foreach ($courseData["lessons"] as $lessonData) {
                    $lesson = new Lesson();
                    $lesson->setTitle($lessonData["title"])
                           ->setPrice($lessonData["price"])
                           ->setCourse($course)
                           ->setContent("Contenu de la leçon...");

                    $manager->persist($lesson);
                }
            }
        }

        $manager->flush();
    }
}
