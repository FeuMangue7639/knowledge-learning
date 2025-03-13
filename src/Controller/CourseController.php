<?php

namespace App\Controller;

use App\Entity\LessonValidation;
use App\Entity\Certification;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Repository\LessonValidationRepository;
use App\Repository\PurchaseRepository;
use App\Repository\CertificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use function array_map;
use function count;


class CourseController extends AbstractController
{
    #[Route('/my-courses', name: 'app_my_courses')]
    public function myCourses(PurchaseRepository $purchaseRepository, CertificationRepository $certificationRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour voir vos cours.');
            return $this->redirectToRoute('app_login');
        }

        $purchases = $purchaseRepository->findBy(['user' => $user]);
        $certifications = $certificationRepository->findBy(['user' => $user]);

        return $this->render('courses/my_courses.html.twig', [
            'purchases' => $purchases,
            'certifications' => $certifications,
        ]);
    }

    #[Route('/my-courses/course/{id}', name: 'app_my_course_detail')]
    public function courseDetail(int $id, CourseRepository $courseRepository, PurchaseRepository $purchaseRepository, LessonValidationRepository $lessonValidationRepository): Response
    {
        $user = $this->getUser();
        $course = $courseRepository->find($id);
        $purchase = $purchaseRepository->findOneBy(['user' => $user, 'course' => $course]);

        if (!$purchase) {
            $this->addFlash('error', 'Vous n\'avez pas accès à ce cours.');
            return $this->redirectToRoute('app_my_courses');
        }

        $completedLessons = $lessonValidationRepository->findBy([
            'user' => $user,
            'isCompleted' => true
        ]);

        return $this->render('courses/course_detail.html.twig', [
            'course' => $course,
            'completedLessons' => $completedLessons,
        ]);
    }

    #[Route('/my-courses/lesson/{id}', name: 'app_my_lesson_detail')]
    public function lessonDetail(int $id, LessonRepository $lessonRepository, PurchaseRepository $purchaseRepository, LessonValidationRepository $lessonValidationRepository): Response
    {
        $user = $this->getUser();
        $lesson = $lessonRepository->find($id);

        $lessonPurchase = $purchaseRepository->findOneBy(['user' => $user, 'lesson' => $lesson]);
        $coursePurchase = $purchaseRepository->findOneBy(['user' => $user, 'course' => $lesson->getCourse()]);

        if (!$lessonPurchase && !$coursePurchase) {
            $this->addFlash('error', 'Vous n\'avez pas accès à cette leçon.');
            return $this->redirectToRoute('app_my_courses');
        }

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
            $this->addFlash('error', 'Leçon non trouvée.');
            return $this->redirectToRoute('app_my_courses');
        }

        // Vérifier si la leçon est déjà validée
        $existingValidation = $lessonValidationRepository->findOneBy([
            'user' => $user,
            'lesson' => $lesson
        ]);

        if (!$existingValidation) {
            $lessonValidation = new LessonValidation();
            $lessonValidation->setUser($user);
            $lessonValidation->setLesson($lesson);
            $lessonValidation->setIsCompleted(true);
            $entityManager->persist($lessonValidation);
            $entityManager->flush();
        }

        // Vérifier si toutes les leçons du cours sont validées
        $course = $lesson->getCourse();
        if ($course) {
            $allLessonIds = array_map(fn($l) => $l->getId(), $course->getLessons()->toArray());

            $completedLessons = $lessonValidationRepository->createQueryBuilder('lv')
                ->select('COUNT(lv.id)')
                ->where('lv.user = :user')
                ->andWhere('lv.lesson IN (:lessons)')
                ->andWhere('lv.isCompleted = true')
                ->setParameter('user', $user)
                ->setParameter('lessons', $allLessonIds)
                ->getQuery()
                ->getSingleScalarResult();

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
                }
            }
        }

        $this->addFlash('success', 'Leçon validée avec succès.');
        return $this->redirectToRoute('app_my_lesson_detail', ['id' => $id]);
    }
}
