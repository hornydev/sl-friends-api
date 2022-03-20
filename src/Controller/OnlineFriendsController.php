<?php

namespace App\Controller;

use App\OnlineFriends\Parser\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OnlineFriendsController extends AbstractController
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    #[Route('/api/online-friends', name: 'online-friends')]
    public function __invoke(Request $request): Response
    {
        $username = $request->headers->get('php-auth-user');
        $password = $request->headers->get('php-auth-pw');

        if (null === $username || null === $password) {
            return new JsonResponse(['error' => 'Auth token missing.'], Response::HTTP_FORBIDDEN);
        }

        $viewModels = ($this->parser)($username, $password);

        return $this->json(['friends' => $viewModels]);
    }
}
