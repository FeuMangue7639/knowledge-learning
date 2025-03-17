<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\Purchase;
use App\Form\CourseType;
use App\Form\LessonType;
use App\Repository\UserRepository;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')] // ✅ Reserve acces to admin
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(UserRepository $userRepository, CourseRepository $courseRepository, LessonRepository $lessonRepository, PurchaseRepository $purchaseRepository): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'users_count' => count($userRepository->findAll()),
            'courses_count' => count($courseRepository->findAll()),
            'lessons_count' => count($lessonRepository->findAll()),
            'purchases_count' => count($purchaseRepository->findAll()),
        ]);
    }

    // ✅ user management
    #[Route('/users', name: 'admin_users')]
    public function manageUsers(UserRepository $userRepository): Response
    {
        return $this->render('admin/users.html.twig', ['users' => $userRepository->findAll()]);
    }

    #[Route('/users/delete/{id}', name: 'admin_user_delete')]
    public function deleteUser(User $user, EntityManagerInterface $entityManager): Response
    {
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->addFlash('error', 'Impossible de supprimer un administrateur.');
            return $this->redirectToRoute('admin_users');
        }

        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('success', 'Utilisateur supprimé.');
        return $this->redirectToRoute('admin_users');
    }

    // ✅ Courses management
    #[Route('/courses', name: 'admin_courses')]
    public function manageCourses(CourseRepository $courseRepository): Response
    {
        return $this->render('admin/courses.html.twig', ['courses' => $courseRepository->findAll()]);
    }

    #[Route('/courses/add', name: 'admin_course_add')]
    public function addCourse(Request $request, EntityManagerInterface $entityManager): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($course);
            $entityManager->flush();
            $this->addFlash('success', 'Cours ajouté avec succès !');

            return $this->redirectToRoute('admin_courses');
        }

        return $this->render('admin/course_add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/courses/delete/{id}', name: 'admin_course_delete')]
    public function deleteCourse(Course $course, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($course);
        $entityManager->flush();
        $this->addFlash('success', 'Cours supprimé.');
        return $this->redirectToRoute('admin_courses');
    }

    // ✅ Lessons management
    #[Route('/lessons', name: 'admin_lessons')]
    public function manageLessons(LessonRepository $lessonRepository): Response
    {
        return $this->render('admin/lessons.html.twig', ['lessons' => $lessonRepository->findAll()]);
    }

    #[Route('/lessons/add', name: 'admin_lesson_add')]
    public function addLesson(Request $request, EntityManagerInterface $entityManager): Response
    {
        $lesson = new Lesson();
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($lesson);
            $entityManager->flush();
            $this->addFlash('success', 'Leçon ajoutée avec succès !');

            return $this->redirectToRoute('admin_lessons');
        }

        return $this->render('admin/lesson_add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/lessons/delete/{id}', name: 'admin_lesson_delete')]
    public function deleteLesson(Lesson $lesson, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($lesson);
        $entityManager->flush();
        $this->addFlash('success', 'Leçon supprimée.');
        return $this->redirectToRoute('admin_lessons');
    }

    // ✅ purchases management
    #[Route('/purchases', name: 'admin_purchases')]
    public function managePurchases(PurchaseRepository $purchaseRepository): Response
    {
        return $this->render('admin/purchases.html.twig', ['purchases' => $purchaseRepository->findAll()]);
    }
}
