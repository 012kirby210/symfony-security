<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class QrCodeController extends AbstractController
{
    #[Route('/authentication/2fa/qr-code', name: 'app_qr_code', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function displayGoogleAuthenticatorQrCode(TotpAuthenticatorInterface $authenticator): Response
    {
        $user = $this->getUser();
        if ( ! $user instanceof User) {
            throw new \LogicException("Etrange utilisateur");
        }

        $data = $authenticator->getQRContent($user);
        $qrCode = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($data)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();

        return new Response($qrCode->getString(), 200, ['Content-Type' => $qrCode->getMimeType()]);
    }
}
