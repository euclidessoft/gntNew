<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UserEnabledSubscriber implements EventSubscriberInterface
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.request' => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $user = $this->security->getUser();

        if (!$user) {
            return;
        }

        if (!$user->isEnabled()) {
            // 🔴 Déconnecter
            $this->security->logout(false);

            $response = new RedirectResponse('/fr/login');

            $event->setResponse($response);
        }
    }
}