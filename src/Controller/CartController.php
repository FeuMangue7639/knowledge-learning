<?php

namespace App\Controller;

use App\Entity\Course;
use App\Repository\CourseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(SessionInterface $session, CourseRepository $courseRepository): Response
    {
        // Récupérer le panier depuis la session
        $cart = $session->get('cart', []);

        // Récupérer les cours associés aux IDs du panier
        $cartItems = [];
        foreach ($cart as $id => $quantity) {
            $course = $courseRepository->find($id);
            if ($course) {
                $cartItems[] = [
                    'course' => $course,
                    'quantity' => $quantity
                ];
            }
        }

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    public function add(int $id, SessionInterface $session, CourseRepository $courseRepository): Response
    {
        $cart = $session->get('cart', []);

        // Vérifier si le cours existe
        $course = $courseRepository->find($id);
        if (!$course) {
            throw $this->createNotFoundException('Le cours demandé n\'existe pas.');
        }

        // Ajouter le cours au panier (incrémentation)
        $cart[$id] = ($cart[$id] ?? 0) + 1;
        $session->set('cart', $cart);

        // ⚠️ Changement : utiliser une clé 'cart' pour éviter l'affichage sur d'autres pages
        $this->addFlash('cart', 'Cours ajouté au panier !');
        return $this->redirectToRoute('app_shop'); // Redirection vers la boutique
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove')]
    public function remove(int $id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            $session->set('cart', $cart);
            $this->addFlash('cart', 'Cours retiré du panier.');
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/clear', name: 'app_cart_clear')]
    public function clear(SessionInterface $session): Response
    {
        $session->remove('cart');
        $this->addFlash('cart', 'Panier vidé.');
        return $this->redirectToRoute('app_cart');
    }
}
