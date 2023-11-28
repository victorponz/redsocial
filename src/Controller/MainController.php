<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Tweet;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $repo = $doctrine->getRepository(Tweet::class);
        $tweets = $repo->findAll();
        return $this->render('main/index.html.twig', [
            'tweets' => $tweets,
        ]);
    }
    #[Route('/user/@{username}', name: 'user_profile')]
    public function wall(ManagerRegistry $doctrine, string $username): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $repo = $doctrine->getRepository(User::class);
        $user = $repo->findOneByUsername($username);

        if (!$user){
            throw $this->createNotFoundException("Usuario no encontrado");
        }

        return $this->render('main/profile.html.twig', [
            'user' => $user
        ]);
    }
    #[Route('/user/@{username}/follow', name: 'user_follow')]
    public function follow(ManagerRegistry $doctrine, string $username): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        
        $repo = $doctrine->getRepository(User::class);
        $user = $repo->findOneByUsername($username);

        if (!$user){
            throw $this->createNotFoundException("Usuario no encontrado");
        }

        return $this->render('main/profile.html.twig', [
            'user' => $user
        ]);
    }     
}
