<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Vérifie l'état d'activation d'un utilisateur avant et après l'authentification.
 * Cette classe permet de bloquer l'accès si l'utilisateur n'a pas confirmé son adresse e-mail.
 */
class UserEnabledChecker implements UserCheckerInterface
{
    /**
     * Vérifie l'état de l'utilisateur avant l'authentification.
     *
     * Cette méthode est appelée automatiquement par Symfony avant que l'utilisateur ne soit authentifié.
     * Si l'utilisateur n'est pas encore vérifié (email non confirmé), une exception personnalisée est levée.
     *
     * @param UserInterface $user L'utilisateur à vérifier
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Empêche la connexion si l'utilisateur n'a pas confirmé son email
        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException(
                'Veuillez confirmer votre adresse e-mail avant de vous connecter.'
            );
        }
    }

    /**
     * Vérifie l'état de l'utilisateur après l'authentification.
     *
     * Cette méthode est utile pour valider l'accès même après le login (ex : blocage en cas de désactivation du compte).
     *
     * @param UserInterface $user L'utilisateur authentifié
     */
    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Si jamais un utilisateur non vérifié parvient à se connecter, on le bloque immédiatement
        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException(
                'Votre compte n\'est pas encore vérifié.'
            );
        }
    }
}
