<?php

namespace App\Controller;

use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
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
    #[Route('/checkout', name: 'app_stripe_payment', methods: ['POST'])]
    public function checkout(
        SessionInterface $session,
        CourseRepository $courseRepository,
        LessonRepository $lessonRepository,
        UrlGeneratorInterface $urlGenerator,
        ParameterBagInterface $params
    ): JsonResponse {
        // ✅ Utiliser la clé Stripe depuis les variables d'environnement
        Stripe::setApiKey($params->get('stripe_secret_key'));

        $cart = $session->get('cart', []);
        $lineItems = [];

        // ✅ Parcourir le panier pour récupérer les articles
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
                        'unit_amount'  => $product->getPrice() * 100, // Convertir en centimes
                    ],
                    'quantity' => $item['quantity'],
                ];
            }
        }

        // ✅ Vérification si le panier est vide
        if (empty($lineItems)) {
            return new JsonResponse(['error' => 'Votre panier est vide.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // ✅ Créer la session de paiement Stripe
        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => $urlGenerator->generate('app_payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url'           => $urlGenerator->generate('app_cart', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return new JsonResponse(['id' => $checkoutSession->id]);
    }

    #[Route('/payment/success', name: 'app_payment_success')]
    public function success(SessionInterface $session): Response
    {
        // ✅ Vider le panier après paiement réussi
        $session->remove('cart');
        return $this->render('cart/success.html.twig');
    }
}
