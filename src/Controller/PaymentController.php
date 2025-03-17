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
    #[Route('/checkout', name: 'app_stripe_payment', methods: ['POST'])]
    public function checkout(
        SessionInterface $session,
        CourseRepository $courseRepository,
        LessonRepository $lessonRepository,
        UrlGeneratorInterface $urlGenerator,
        ParameterBagInterface $params
    ): JsonResponse {
        Stripe::setApiKey($params->get('stripe_secret_key'));

        $cart = $session->get('cart', []);
        $lineItems = [];

        // Iterate over cart items and prepare Stripe checkout line items.
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
                        'unit_amount'  => $product->getPrice() * 100, 
                    ],
                    'quantity' => $item['quantity'],
                ];
            }
        }

        // If cart is empty, return an error response.
        if (empty($lineItems)) {
            return new JsonResponse(['error' => 'Votre panier est vide.'], JsonResponse::HTTP_BAD_REQUEST);
        }
        
        // Create a Stripe checkout session.
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
    public function success(SessionInterface $session, EntityManagerInterface $entityManager, CourseRepository $courseRepository, LessonRepository $lessonRepository): Response
    {
        $cart = $session->get('cart', []);
        $user = $this->getUser();

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

        // Save all purchases in the database.
        $entityManager->flush();

        // Remove the cart from the session.
        $session->remove('cart');

        // Render the success page.
        return $this->render('cart/success.html.twig');
    }
}
