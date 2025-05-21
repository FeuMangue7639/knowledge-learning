<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * Classe de gestion de la vérification d'adresse email utilisateur.
 * Elle permet d'envoyer des liens de confirmation et de valider ces liens.
 */
class EmailVerifier
{
    /**
     * Constructeur injectant les services nécessaires.
     *
     * @param VerifyEmailHelperInterface $verifyEmailHelper Service pour générer/valider les liens signés
     * @param MailerInterface $mailer Service de messagerie Symfony
     * @param EntityManagerInterface $entityManager Accès à la base de données via Doctrine
     */
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Envoie l'email de confirmation d'inscription à l'utilisateur.
     *
     * @param string $verifyEmailRouteName Nom de la route de vérification (ex: app_verify_email)
     * @param User $user Utilisateur à vérifier
     * @param TemplatedEmail $email Email préparé avec le template et le destinataire
     */
    public function sendEmailConfirmation(string $verifyEmailRouteName, User $user, TemplatedEmail $email): void
    {
        // Génère une URL de confirmation contenant l’ID et l’email de l’utilisateur
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()] // Clé critique pour lier la vérification à l’utilisateur
        );

        // Intègre les données utiles (URL, expiration, etc.) dans le contexte du mail
        $context = $email->getContext();
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

        $email->context($context);

        // Envoie le mail via le service mailer
        $this->mailer->send($email);
    }

    /**
     * Traite la vérification de l'adresse email via le lien cliqué.
     *
     * @param Request $request Requête HTTP contenant l'URL signée
     * @param User $user Utilisateur à activer
     *
     * @throws VerifyEmailExceptionInterface En cas d’URL invalide ou expirée
     */
    public function handleEmailConfirmation(Request $request, User $user): void
    {
        // Valide le lien de confirmation
        $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
            $request,
            $user->getId(),
            $user->getEmail()
        );

        // Marque l'utilisateur comme vérifié
        $user->setIsVerified(true);

        // Sauvegarde en base de données
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
