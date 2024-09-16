<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        // On vérifie si l'utilisateur est une instance de notre classe User
        if (!$user instanceof User) {
            return;
        }

        // Si l'utilisateur n'a pas vérifié son email, on lève une exception
        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException('Votre compte n\'est pas vérifié. Veuillez vérifier votre email.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Aucune vérification post-authentification nécessaire pour le moment
    }
}