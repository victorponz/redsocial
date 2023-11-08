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
    #[Route('/wall/@{username}', name: 'user_wall')]
    public function wall(ManagerRegistry $doctrine, string $username): Response
    {
        $repo = $doctrine->getRepository(User::class);
        $user = $repo->findOneByUsername($username);
        
        if (!$user){
            return new Response("Usuario no encontrado");
        }

        return $this->render('main/wall.html.twig', [
            'user' => $user
        ]);
    }
        
}
