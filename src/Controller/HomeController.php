<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CourseRepository;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(CourseRepository $courseRepository): Response
    {
        // Récupère le cours de la BDD
        $courses = $courseRepository->findAll();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'courses' => $courses, // Envoie le cours à la Vue Twig
        ]);
    }
}