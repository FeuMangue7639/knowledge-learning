<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(SessionInterface $session, CourseRepository $courseRepository, LessonRepository $lessonRepository): Response
    {
        $cart = $session->get('cart', []);

        $cartItems = [];

        foreach ($cart as $id => $item) {
            if ($item['type'] === 'course') {
                $course = $courseRepository->find($id);
                if ($course) {
                    $cartItems[] = [
                        'type' => 'course',
                        'item' => $course,
                        'quantity' => $item['quantity'],
                    ];
                }
            } elseif ($item['type'] === 'lesson') {
                $lesson = $lessonRepository->find($id);
                if ($lesson) {
                    $cartItems[] = [
                        'type' => 'lesson',
                        'item' => $lesson,
                        'quantity' => $item['quantity'],
                    ];
                }
            }
        }

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
        ]);
    }

    #[Route('/cart/add/{id}/{type}', name: 'app_cart_add')]
    public function add(int $id, string $type, SessionInterface $session,
     CourseRepository $courseRepository,
      LessonRepository $lessonRepository): Response
    {
        $cart = $session->get('cart', []);

        if ($type === 'course') {
            $course = $courseRepository->find($id);
            if (!$course) {
                throw $this->createNotFoundException('Le cours demandé n\'existe pas.');
            }
        } elseif ($type === 'lesson') {
            $lesson = $lessonRepository->find($id);
            if (!$lesson) {
                throw $this->createNotFoundException('La leçon demandée n\'existe pas.');
            }
        } else {
            throw $this->createNotFoundException('Type inconnu.');
        }

        // Add or increment the item in the cart
        if (isset($cart[$id])) {
            $cart[$id]['quantity'] += 1;
        } else {
            $cart[$id] = [
                'type' => $type,
                'quantity' => 1,
            ];
        }

        $session->set('cart', $cart);
        $this->addFlash('success', ucfirst($type) . ' ajouté(e) au panier !');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove')]
    public function remove(int $id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            $session->set('cart', $cart);
            $this->addFlash('success', 'Élément retiré du panier.');
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/clear', name: 'app_cart_clear')]
    public function clear(SessionInterface $session): Response
    {
        $session->remove('cart');
        $this->addFlash('success', 'Panier vidé.');
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/payment', name: 'app_payment')]
    public function payment(ParameterBagInterface $params): Response
    {
        // Retrieving Stripe Public Key from Settings
        $stripePublicKey = $params->get('stripe_public_key');

        return $this->render('cart/payment.html.twig', [
            'stripe_public_key' => $stripePublicKey,
        ]);
    }
}
