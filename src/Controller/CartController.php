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
    /**
     * Affiche le contenu du panier
     * - Récupère les éléments (cours ou leçons) présents dans la session
     * - Reconstitue les objets complets depuis la base de données
     */
    #[Route('/cart', name: 'app_cart')]
    public function index(SessionInterface $session, CourseRepository $courseRepository, LessonRepository $lessonRepository): Response
    {
        $cart = $session->get('cart', []); // Panier stocké en session
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

    /**
     * Ajoute un élément (cours ou leçon) au panier
     * - Vérifie que l'élément existe
     * - L'ajoute ou incrémente la quantité si déjà présent
     */
    #[Route('/cart/add/{id}/{type}', name: 'app_cart_add')]
    public function add(int $id, string $type, SessionInterface $session, CourseRepository $courseRepository, LessonRepository $lessonRepository): Response
    {
        $cart = $session->get('cart', []);

        // Vérifie l'existence de l'élément en base
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

        // Ajout ou incrémentation dans le panier
        if (isset($cart[$id])) {
            $cart[$id]['quantity'] += 1;
        } else {
            $cart[$id] = [
                'type' => $type,
                'quantity' => 1,
            ];
        }

        $session->set('cart', $cart);
        $this->addFlash('success', mb_convert_case($type, MB_CASE_TITLE, "UTF-8") . ' ajouté(e) au panier !');

        return $this->redirectToRoute('app_cart');
    }

    /**
     * Supprime un élément du panier
     */
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

    /**
     * Vide complètement le panier
     */
    #[Route('/cart/clear', name: 'app_cart_clear')]
    public function clear(SessionInterface $session): Response
    {
        $session->remove('cart');
        $this->addFlash('success', 'Panier vidé.');
        return $this->redirectToRoute('app_cart');
    }

    /**
     * Affiche la page de paiement
     * - Transmet la clé publique Stripe (récupérée via les paramètres)
     */
    #[Route('/cart/payment', name: 'app_payment')]
    public function payment(ParameterBagInterface $params): Response
    {
        $stripePublicKey = $params->get('stripe_public_key'); // Clé définie dans .env

        return $this->render('cart/payment.html.twig', [
            'stripe_public_key' => $stripePublicKey,
        ]);
    }
}
