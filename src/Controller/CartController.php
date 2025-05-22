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
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Contrôleur pour gérer les actions liées au panier :
 * - Visualiser son contenu
 * - Ajouter/Supprimer un élément
 * - Passer au paiement
 */
class CartController extends AbstractController
{
    /**
     * Affiche le contenu du panier avec les cours et leçons ajoutés.
     * Les objets sont récupérés en base de données à partir des IDs stockés en session.
     */
    #[Route('/cart', name: 'app_cart')]
    public function index(SessionInterface $session, CourseRepository $courseRepository, LessonRepository $lessonRepository): Response
    {
        $cart = $session->get('cart', []); // Récupère le panier stocké dans la session
        $cartItems = [];

        // Reconstitue les objets (course/lesson) à partir des IDs et du type
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
     * Ajoute un cours ou une leçon au panier.
     * Si l'élément est déjà présent, affiche un message et redirige vers la page précédente.
     */
    #[Route('/cart/add/{id}/{type}', name: 'app_cart_add')]
    public function add(
        int $id,
        string $type,
        SessionInterface $session,
        CourseRepository $courseRepository,
        LessonRepository $lessonRepository,
        RequestStack $requestStack
    ): Response {
        $cart = $session->get('cart', []);

        // Initialise les libellés à afficher dans les messages selon le type
        if ($type === 'course') {
            $label = 'Ce cours';
            $ajout = 'ajouté';
            $entity = $courseRepository->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('Le cours demandé n\'existe pas.');
            }
        } elseif ($type === 'lesson') {
            $label = 'Cette leçon';
            $ajout = 'ajoutée';
            $entity = $lessonRepository->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('La leçon demandée n\'existe pas.');
            }
        } else {
            throw $this->createNotFoundException('Type inconnu.');
        }

        // Si l'élément est déjà dans le panier, affiche un message et retourne à la page précédente
        if (isset($cart[$id])) {
            $this->addFlash('warning', $label . ' est déjà dans votre panier.');
            $referer = $requestStack->getCurrentRequest()->headers->get('referer');
            return $this->redirect($referer ?: $this->generateUrl('app_shop'));
        }

        // Ajout de l'élément au panier
        $cart[$id] = [
            'type' => $type,
            'quantity' => 1,
        ];
        $session->set('cart', $cart);

        // Message de confirmation affiché sur la page panier
        $this->addFlash('cart_success', $label . ' a été ' . $ajout . ' au panier !');

        return $this->redirectToRoute('app_cart');
    }

    /**
     * Supprime un élément du panier (cours ou leçon) via son ID.
     */
    #[Route('/cart/remove/{id}', name: 'app_cart_remove')]
    public function remove(int $id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            $session->set('cart', $cart);
        }

        return $this->redirectToRoute('app_cart');
    }

    /**
     * Vide complètement le panier en supprimant la variable de session.
     */
    #[Route('/cart/clear', name: 'app_cart_clear')]
    public function clear(SessionInterface $session): Response
    {
        $session->remove('cart');
        return $this->redirectToRoute('app_cart');
    }

    /**
     * Affiche la page de paiement avec la clé publique Stripe.
     */
    #[Route('/cart/payment', name: 'app_payment')]
    public function payment(ParameterBagInterface $params): Response
    {
        $stripePublicKey = $params->get('stripe_public_key');

        return $this->render('cart/payment.html.twig', [
            'stripe_public_key' => $stripePublicKey,
        ]);
    }
}
