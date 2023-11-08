<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class TweetController extends AbstractController
{
    use TargetPathTrait;

    #[Route('/tweet/add', name: 'tweet_add')]
    public function index(Request $request, string $firewallName = 'main'): Response
    {

        $this->saveTargetPath($request->getSession(), $firewallName, $this->generateUrl("tweet_add"));
        $this->denyAccessUnlessGranted("ROLE_USER");

        return $this->render('tweet/index.html.twig', [
            'controller_name' => 'TweeController',
        ]);
    }
}
