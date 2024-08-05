<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class checkVerifiedUserSubsriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => [ 'onCheckPassport', -10 ]
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
            throw new CustomUserMessageAuthenticationException('Please verify your account before loggin in.');
        }
    }
}