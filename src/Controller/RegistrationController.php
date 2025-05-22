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
    // Injection du service EmailVerifier dans le constructeur
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    /**
     * Gère le formulaire d'inscription d'un nouvel utilisateur.
     * - Crée un utilisateur
     * - Vérifie que l'email et le nom ne sont pas déjà utilisés
     * - Hash le mot de passe
     * - Enregistre l'utilisateur
     * - Envoie un e-mail de confirmation
     */
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

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $hasError = false;

            // Vérifie si l'email est déjà enregistré
            if ($userRepository->findOneBy(['email' => $user->getEmail()])) {
                $this->addFlash('danger', 'Cet email est déjà utilisé.');
                $hasError = true;
            }

            // Vérifie si le nom d'utilisateur est déjà utilisé
            if ($userRepository->findOneBy(['username' => $user->getUsername()])) {
                $this->addFlash('danger', 'Ce nom d’utilisateur est déjà pris.');
                $hasError = true;
            }

            // Si l'un des deux champs est déjà utilisé, on retourne sur le formulaire
            if ($hasError) {
                return $this->redirectToRoute('app_register');
            }

            // Hash du mot de passe saisi
            $user->setPassword($userPasswordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            ));

            // Enregistrement de l'utilisateur en base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Envoie un e-mail de confirmation avec un lien sécurisé
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('coelhohugo2003@gmail.com', 'Hugo')) // Adresse d’envoi
                    ->to($user->getEmail()) // Adresse du destinataire
                    ->subject('Veuillez confirmer votre email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // Message flash de succès + redirection vers la page de connexion
            $this->addFlash('info', 'Votre compte a été créé avec succès ! Veuillez vérifier votre e-mail pour activer votre compte.');
            return $this->redirectToRoute('app_login');
        }

        // Affiche le formulaire d'inscription
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * Gère la vérification de l'e-mail après clic sur le lien reçu.
     * - Vérifie l'ID utilisateur
     * - Valide l'adresse e-mail via le service EmailVerifier
     */
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        // Récupère l’ID utilisateur depuis l’URL du mail
        $userId = $request->query->get('id');

        // Si l'identifiant est absent OU qu'aucun utilisateur ne correspond à cet ID, on redirige vers l'inscription
        if (!$userId || !($user = $userRepository->find($userId))) {
            return $this->redirectToRoute('app_register');
        }

        try {
            // Si tout est bon, on confirme l'email 
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            // En cas d'erreur (ex: lien expiré ou modifié), on affiche un message d'erreur et on redirige vers l'inscription
            $this->addFlash('verify_email_error', $exception->getReason());
            return $this->redirectToRoute('app_register');
        }

        // Si tout s'est bien passé, on affiche un message de succès et on redirige vers la page de connexion
        $this->addFlash('succes', 'Votre adresse email a bien été vérifiée.');
        return $this->redirectToRoute('app_login');
    }
}
