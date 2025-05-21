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
#[IsGranted('ROLE_ADMIN')] // Accès réservé aux utilisateurs ayant le rôle ADMIN
class AdminController extends AbstractController
{
    /**
     * Page d'accueil du dashboard admin
     * Affiche le nombre total d'utilisateurs, de cours, de leçons et d'achats.
     */
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

    /**
     * Affiche la liste des utilisateurs
     */
    #[Route('/users', name: 'admin_users')]
    public function manageUsers(UserRepository $userRepository): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * Supprime un utilisateur (sauf s'il a le rôle ADMIN)
     */
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

    /**
     * Affiche la liste des cours
     */
    #[Route('/courses', name: 'admin_courses')]
    public function manageCourses(CourseRepository $courseRepository): Response
    {
        return $this->render('admin/courses.html.twig', [
            'courses' => $courseRepository->findAll(),
        ]);
    }

    /**
     * Permet d’ajouter un cours via un formulaire
     * Traite l’upload d’image et l’enregistre dans /public/uploads/courses/
     */
    #[Route('/courses/add', name: 'admin_course_add')]
    public function addCourse(Request $request, EntityManagerInterface $entityManager): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $newFilename = 'course_' . uniqid() . '.' . $imageFile->guessExtension();

                // Déplace l'image dans le répertoire public/uploads/courses
                $imageFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads/courses',
                    $newFilename
                );

                $course->setImage($newFilename);
            } else {
                // Utilise une image par défaut si aucune image n'est envoyée
                $course->setImage('default.jpeg'); 
            }

            $entityManager->persist($course);
            $entityManager->flush();

            $this->addFlash('success', 'Cours ajouté avec succès !');
            return $this->redirectToRoute('admin_courses');
        }

        return $this->render('admin/course_add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Supprime un cours
     */
    #[Route('/courses/delete/{id}', name: 'admin_course_delete')]
    public function deleteCourse(Course $course, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($course);
        $entityManager->flush();
        $this->addFlash('success', 'Cours supprimé.');
        return $this->redirectToRoute('admin_courses');
    }

    /**
     * Affiche toutes les leçons
     */
    #[Route('/lessons', name: 'admin_lessons')]
    public function manageLessons(LessonRepository $lessonRepository): Response
    {
        return $this->render('admin/lessons.html.twig', [
            'lessons' => $lessonRepository->findAll(),
        ]);
    }

    /**
     * Permet d’ajouter une leçon via un formulaire
     */
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

    /**
     * Supprime une leçon
     */
    #[Route('/lessons/delete/{id}', name: 'admin_lesson_delete')]
    public function deleteLesson(Lesson $lesson, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($lesson);
        $entityManager->flush();
        $this->addFlash('success', 'Leçon supprimée.');
        return $this->redirectToRoute('admin_lessons');
    }

    /**
     * Affiche les achats effectués par les utilisateurs
     */
    #[Route('/purchases', name: 'admin_purchases')]
    public function managePurchases(PurchaseRepository $purchaseRepository): Response
    {
        return $this->render('admin/purchases.html.twig', [
            'purchases' => $purchaseRepository->findAll(),
        ]);
    }
}
