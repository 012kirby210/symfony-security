<?php

namespace App\Controller;

use App\Controller\BaseController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends BaseController
{
    #[Route('/api/me', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function apiMe()
    {
        return $this->json($this->getUser(), 200, [],
        [
            'groups' => ['user:read']
        ]);
    }
}