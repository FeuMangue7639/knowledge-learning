<?php

namespace App\Controller;

use App\Entity\LessonValidation;
use App\Entity\Certification;
use App\Entity\Lesson;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Repository\LessonValidationRepository;
use App\Repository\PurchaseRepository;
use App\Repository\CertificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class CourseController extends AbstractController
{
    /**
     * Affiche tous les cours que l'utilisateur a achetés ou auxquels il a accès via des leçons.
     * Regroupe les achats de cours complets ou leçons individuelles.
     */
    #[Route('/my-courses', name: 'app_my_courses')]
    public function myCourses(PurchaseRepository $purchaseRepository, CertificationRepository $certificationRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $purchases = $purchaseRepository->findBy(['user' => $user]);
        $certifications = $certificationRepository->findBy(['user' => $user]);

        $coursesWithLessons = [];

        // Regroupe les cours et leçons achetés
        foreach ($purchases as $purchase) {
            // Achat d’un cours complet
            if ($course = $purchase->getCourse()) {
                $courseId = $course->getId();
                $coursesWithLessons[$courseId] = [
                    'course' => $course,
                    'lessons' => $course->getLessons()->toArray(),
                    'purchasedDirectly' => true
                ];
            }

            // Achat d’une leçon individuelle
            if ($lesson = $purchase->getLesson()) {
                $course = $lesson->getCourse();
                $courseId = $course->getId();

                // Crée une entrée si le cours n’est pas encore ajouté
                if (!isset($coursesWithLessons[$courseId])) {
                    $coursesWithLessons[$courseId] = [
                        'course' => $course,
                        'lessons' => [],
                        'purchasedDirectly' => false
                    ];
                }

                // Ajoute la leçon seulement si elle n’a pas encore été ajoutée
                if (!in_array($lesson, $coursesWithLessons[$courseId]['lessons'], true)) {
                    $coursesWithLessons[$courseId]['lessons'][] = $lesson;
                }
            }
        }

        return $this->render('courses/my_courses.html.twig', [
            'coursesWithLessons' => $coursesWithLessons,
            'certifications' => $certifications,
        ]);
    }

    /**
     * Affiche les détails d’un cours spécifique.
     * Montre les leçons accessibles et si l'utilisateur possède le cours complet.
     */
    #[Route('/my-courses/course/{id}', name: 'app_my_course_detail')]
    public function courseDetail(
        int $id,
        CourseRepository $courseRepository,
        PurchaseRepository $purchaseRepository,
        LessonValidationRepository $lessonValidationRepository
    ): Response {
        $user = $this->getUser();
        $course = $courseRepository->find($id);

        if (!$course) {
            throw $this->createNotFoundException('Cours introuvable.');
        }

        $purchases = $purchaseRepository->findBy(['user' => $user]);
        $hasCourse = false;
        $accessibleLessonIds = [];

        // Vérifie les leçons ou cours achetés
        foreach ($purchases as $purchase) {
            // L'utilisateur a acheté tout le cours
            if ($purchase->getCourse()?->getId() === $course->getId()) {
                $hasCourse = true;
                foreach ($course->getLessons() as $lesson) {
                    $accessibleLessonIds[] = $lesson->getId();
                }
                break;
            }

            // L'utilisateur a acheté une leçon de ce cours
            if ($purchase->getLesson()?->getCourse()->getId() === $course->getId()) {
                $lesson = $purchase->getLesson();
                $accessibleLessonIds[] = $lesson->getId();
            }
        }

        // Récupère toutes les leçons validées de l'utilisateur
        $completedLessons = $lessonValidationRepository->findBy([
            'user' => $user,
            'isCompleted' => true
        ]);

        return $this->render('courses/course_detail.html.twig', [
            'course' => $course,
            'completedLessons' => $completedLessons,
            'hasCourse' => $hasCourse,
            'accessibleLessonIds' => $accessibleLessonIds
        ]);
    }

    /**
     * Affiche le contenu d'une leçon, seulement si l'utilisateur l'a achetée ou a acheté le cours.
     */
    #[Route('/my-courses/lesson/{id}', name: 'app_my_lesson_detail')]
    public function lessonDetail(int $id, LessonRepository $lessonRepository, PurchaseRepository $purchaseRepository, LessonValidationRepository $lessonValidationRepository): Response
    {
        $user = $this->getUser();
        $lesson = $lessonRepository->find($id);

        // Vérifie si l'utilisateur a accès à cette leçon
        $lessonPurchase = $purchaseRepository->findOneBy(['user' => $user, 'lesson' => $lesson]);
        $coursePurchase = $purchaseRepository->findOneBy(['user' => $user, 'course' => $lesson->getCourse()]);

        if (!$lessonPurchase && !$coursePurchase) {
            return $this->redirectToRoute('app_my_courses');
        }

        // Vérifie si la leçon est déjà validée
        $lessonCompleted = $lessonValidationRepository->findOneBy([
            'user' => $user,
            'lesson' => $lesson,
            'isCompleted' => true
        ]);

        return $this->render('courses/lesson_detail.html.twig', [
            'lesson' => $lesson,
            'lessonCompleted' => $lessonCompleted
        ]);
    }

    /**
     * Marque une leçon comme validée par l'utilisateur.
     * Si toutes les leçons du cours sont validées, crée une certification.
     */
    #[Route('/my-courses/lesson/{id}/validate', name: 'app_my_lesson_validate')]
    public function validateLesson(
        int $id,
        LessonRepository $lessonRepository,
        LessonValidationRepository $lessonValidationRepository,
        CertificationRepository $certificationRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $lesson = $lessonRepository->find($id);

        if (!$lesson) {
            return $this->redirectToRoute('app_my_courses');
        }

        // Évite la double validation
        $existingValidation = $lessonValidationRepository->findOneBy([
            'user' => $user,
            'lesson' => $lesson
        ]);

        // Crée la validation de la leçon si elle n'existe pas
        if (!$existingValidation) {
            $lessonValidation = new LessonValidation();
            $lessonValidation->setUser($user);
            $lessonValidation->setLesson($lesson);
            $lessonValidation->setIsCompleted(true);
            $entityManager->persist($lessonValidation);
            $entityManager->flush();
        }

        $course = $lesson->getCourse();
        if ($course) {
            $allLessonIds = array_map(fn($l) => $l->getId(), $course->getLessons()->toArray());

            // Compte les validations de leçons du cours
            $completedLessons = $lessonValidationRepository->createQueryBuilder('lv')
                ->select('COUNT(lv.id)')
                ->where('lv.user = :user')
                ->andWhere('lv.lesson IN (:lessons)')
                ->andWhere('lv.isCompleted = true')
                ->setParameter('user', $user)
                ->setParameter('lessons', $allLessonIds)
                ->getQuery()
                ->getSingleScalarResult();

            // Si toutes les leçons sont validées, crée une certification
            if ($completedLessons == count($allLessonIds)) {
                $existingCertification = $certificationRepository->findOneBy([
                    'user' => $user,
                    'course' => $course
                ]);

                if (!$existingCertification) {
                    $certification = new Certification();
                    $certification->setUser($user);
                    $certification->setCourse($course);
                    $entityManager->persist($certification);
                    $entityManager->flush();

                    // Message flash avec trophée
                    $this->addFlash('trophy', '🏆 Félicitations ! Vous avez obtenu une certification pour ce cours.');
                    return $this->redirectToRoute('app_my_courses');
                }
            }
        }

        // Message flash pour validation simple
        $this->addFlash('lesson_validated', 'Leçon validée avec succès.');
        return $this->redirectToRoute('app_my_course_detail', ['id' => $lesson->getCourse()->getId()]);
    }

    /**
     * Affiche la page publique de détail d’une leçon depuis la boutique.
     * Vérifie si l'utilisateur possède déjà cette leçon ou le cours associé.
     */
    #[Route('/shop/lesson/{id}', name: 'app_shop_lesson_detail')]
    public function publicLessonDetail(Lesson $lesson, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $hasLesson = false;

        if ($user) {
            $purchaseRepo = $entityManager->getRepository(\App\Entity\Purchase::class);
            $lessonPurchase = $purchaseRepo->findOneBy(['user' => $user, 'lesson' => $lesson]);
            $coursePurchase = $purchaseRepo->findOneBy(['user' => $user, 'course' => $lesson->getCourse()]);
            $hasLesson = $lessonPurchase !== null || $coursePurchase !== null;
        }

        return $this->render('shop/lesson_detail.html.twig', [
            'lesson' => $lesson,
            'hasLesson' => $hasLesson,
        ]);
    }
}
