<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user->isEnabled()) {
            throw new CustomUserMessageAccountStatusException('Votre compte est désactivé.');
        }
    }

    public function checkPostAuth(UserInterface $user): void {}
}