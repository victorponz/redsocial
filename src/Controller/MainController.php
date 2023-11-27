<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
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
