<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends BaseController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('security/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
                'email' => $authenticationUtils->getLastUsername()
            ]
        );
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(AuthenticationUtils $authenticationUtils): Response
    {
        throw new \Exception('logout() should not be called!');
    }

    #[Route("/authenticate/2fa/enable", name: 'app_authenticate_2fa_enable')]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function enaable2fa(TotpAuthenticatorInterface $authenticator, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        if( !$user->isTotpAuthenticationEnabled()){
            $user->setTotpSecret($authenticator->generateSecret());
            $entityManager->flush();
        }

        dd($user);
    }

}
