<?php

namespace App\Controller;

use App\Entity\Purchase;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends AbstractController
{
    /**
     * Crée une session de paiement Stripe à partir du panier.
     */
    #[Route('/checkout', name: 'app_stripe_payment', methods: ['POST'])]
    public function checkout(
        SessionInterface $session,
        CourseRepository $courseRepository,
        LessonRepository $lessonRepository,
        UrlGeneratorInterface $urlGenerator,
        ParameterBagInterface $params
    ): JsonResponse {
        // Configuration de la clé secrète Stripe
        Stripe::setApiKey($params->get('stripe_secret_key'));

        $cart = $session->get('cart', []);
        $lineItems = [];

        // Parcours des articles du panier pour préparer les articles Stripe
        foreach ($cart as $id => $item) {
            if ($item['type'] === 'course') {
                $product = $courseRepository->find($id);
            } else {
                $product = $lessonRepository->find($id);
            }

            if ($product) {
                $lineItems[] = [
                    'price_data' => [
                        'currency'     => 'eur',
                        'product_data' => [
                            'name' => $product->getTitle(),
                        ],
                        'unit_amount'  => $product->getPrice() * 100, // Prix en centimes
                    ],
                    'quantity' => $item['quantity'],
                ];
            }
        }

        // Si le panier est vide, on retourne une erreur
        if (empty($lineItems)) {
            return new JsonResponse(['error' => 'Votre panier est vide.'], JsonResponse::HTTP_BAD_REQUEST);
        }
        
        // Création de la session de paiement Stripe
        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => $urlGenerator->generate('app_payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url'           => $urlGenerator->generate('app_cart', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        // Réponse contenant l'ID de session Stripe à utiliser côté client
        return new JsonResponse(['id' => $checkoutSession->id]);
    }

    /**
     * Gère la redirection après un paiement réussi.
     * Enregistre les achats dans la base de données et vide le panier.
     */
    #[Route('/payment/success', name: 'app_payment_success')]
    public function success(
        SessionInterface $session,
        EntityManagerInterface $entityManager,
        CourseRepository $courseRepository,
        LessonRepository $lessonRepository
    ): Response {
        $cart = $session->get('cart', []);
        $user = $this->getUser();

        // Parcours des éléments du panier et création d’un enregistrement d’achat
        foreach ($cart as $id => $item) {
            $purchase = new Purchase();
            $purchase->setUser($user);

            if ($item['type'] === 'course') {
                $course = $courseRepository->find($id);
                $purchase->setCourse($course);
            } else {
                $lesson = $lessonRepository->find($id);
                $purchase->setLesson($lesson);
            }

            $entityManager->persist($purchase);
        }

        // Sauvegarde des achats en base de données
        $entityManager->flush();

        // Suppression du panier de la session
        $session->remove('cart');

        // Affichage de la page de confirmation
        return $this->render('cart/success.html.twig');
    }
}
