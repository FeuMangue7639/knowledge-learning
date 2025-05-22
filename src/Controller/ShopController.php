<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Lesson;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShopController extends AbstractController
{
    /**
     * Affiche la boutique avec tous les cours et toutes les leçons.
     * Cette méthode récupère tous les enregistrements de l'entité Course et Lesson
     * et les transmet au template Twig `shop/index.html.twig`.
     */
    #[Route('/shop', name: 'app_shop')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $courses = $entityManager->getRepository(Course::class)->findAll();
        $lessons = $entityManager->getRepository(Lesson::class)->findAll();

        return $this->render('shop/index.html.twig', [
            'courses' => $courses,
            'lessons' => $lessons,
        ]);
    }

    /**
     * Affiche les détails d'un cours ainsi que ses leçons associées.
     * Vérifie si l'utilisateur possède déjà le cours
     */
    #[Route('/shop/course/{id}', name: 'app_course_detail')]
public function courseDetail(Course $course, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();
    $hasCourse = false;

    if ($user) {
        $purchaseRepo = $entityManager->getRepository(\App\Entity\Purchase::class);
        $purchase = $purchaseRepo->findOneBy(['user' => $user, 'course' => $course]);
        $hasCourse = $purchase !== null;
    }

    return $this->render('shop/detail.html.twig', [
        'course' => $course,
        'lessons' => $course->getLessons(),
        'hasCourse' => $hasCourse
    ]);
}


    /**
     * Affiche les détails d'une leçon individuelle.
     * Utile si une leçon est achetée séparément d’un cours.
     */
    #[Route('/shop/lesson/{id}', name: 'app_lesson_detail')]
    public function lessonDetail(Lesson $lesson): Response
    {
        return $this->render('shop/detail.html.twig', [
            'lesson' => $lesson,
        ]);
    }
}
