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

    #[Route('/shop/course/{id}', name: 'app_course_detail')]
    public function courseDetail(Course $course): Response
    {
        // ✅ We retrieve the lessons associated with the course
        return $this->render('shop/detail.html.twig', [
            'course' => $course,
            'lessons' => $course->getLessons(), // Send the course lessons
        ]);
    }

    #[Route('/shop/lesson/{id}', name: 'app_lesson_detail')]
    public function lessonDetail(Lesson $lesson): Response
    {
        return $this->render('shop/detail.html.twig', [
            'lesson' => $lesson,
        ]);
    }
}
