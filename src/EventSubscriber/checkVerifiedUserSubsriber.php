<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Security\AccountNotVerifiedAuthenticationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

class checkVerifiedUserSubsriber implements EventSubscriberInterface
{

    public function __construct(private RouterInterface $router)
    {  }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => [ 'onCheckPassport', -10 ],
            LoginFailureEvent::class => [ 'onLoginFailure' ],
        ];
    }

    public function onCheckPassport(CheckPassportEvent $event)
    {
        $passport = $event->getPassport();

        $user = $passport->getUser();
        if ( !$user instanceof User) {
            throw new \Exception('Unexpected user type');
        }

        if (!$user->isVerified()){
            throw new AccountNotVerifiedAuthenticationException();
        }
    }

    public function onLoginFailure(LoginFailureEvent $event)
    {
        if ( ! $event->getException() instanceof AccountNotVerifiedAuthenticationException ){
            return;
        }

        $response = new RedirectResponse(
            $this->router->generate('app_verify_resend_email')
        );
        $event->setResponse($response);
    }
}