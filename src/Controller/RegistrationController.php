<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request,
                             UserPasswordHasherInterface $userPasswordHasher,
                             EntityManagerInterface $entityManager,
                             VerifyEmailHelperInterface $verifyEmailHelper

    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            $signatureComponent = $verifyEmailHelper->generateSignature(
                'app_verify_email',
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );

            // TODO: send this as an email
            $this->addFlash('success','Confirm your email at : ' . $signatureComponent->getSignedUrl());

            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route("/verify", name:"app_verify_email")]
    public function verifyUserEmail(Request $request,
                                    VerifyEmailHelperInterface $verifyEmailHelper,
                                    UserRepository $userRepository,
                                    EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->findOneBy(['id' => $request->query->get('id')]);
        if( null === $user ){
            $this->createNotFoundException();
        }

        try{
            $verifyEmailHelper->validateEmailConfirmationFromRequest($request, $user->getId(), $user->getEmail());
        }catch(VerifyEmailExceptionInterface $e){
            $this->addFlash('error',$e->getReason());

            return $this->redirectToRoute('app_register');
        }

        $user->isVerified(true);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success','Account verified! You can now log in!');
        return $this->redirectToRoute('app_login');
    }

    #[Route("/verify/resend", name:"app_verify_resend_email")]
    public function resendverifyEmail(): Response
    {
        return $this->render('registration/resend_verify_email.html.twig');
    }
}
