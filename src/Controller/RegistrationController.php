<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification si l'email existe déjà
            if ($userRepository->findOneBy(['email' => $user->getEmail()])) {
                $this->addFlash('danger', 'Cet email est déjà utilisé.');
                return $this->redirectToRoute('app_register');
            }

            // Vérification si le username est déjà utilisé
            if ($userRepository->findOneBy(['username' => $user->getUsername()])) {
                $this->addFlash('danger', 'Ce nom d’utilisateur est déjà pris.');
                return $this->redirectToRoute('app_register');
            }

            // Hash du mot de passe
            $user->setPassword($userPasswordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            ));

            $entityManager->persist($user);
            $entityManager->flush();

            // Envoi de l'e-mail de confirmation
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('coelhohugo2003@gmail.com', 'Hugo')) // Adresse expéditeur
                    ->to($user->getEmail()) // Adresse entrée dans le formulaire
                    ->subject('Veuillez confirmer votre email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            $this->addFlash('info', 'Votre compte a été créé avec succès ! Veuillez vérifier votre e-mail pour activer votre compte.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        // Récupération de l'utilisateur via l'ID présent dans l'URL
        $userId = $request->query->get('id');

        if (!$userId || !($user = $userRepository->find($userId))) {
            $this->addFlash('verify_email_error', 'Lien invalide ou utilisateur introuvable.');
            return $this->redirectToRoute('app_register');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());
            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Votre adresse email a bien été vérifiée.');
        return $this->redirectToRoute('app_home');
    }
}