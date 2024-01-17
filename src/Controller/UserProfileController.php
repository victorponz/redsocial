<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\Persistence\ManagerRegistry;

class UserProfileController extends AbstractController
{
    #[Route('/user/profile', name: 'app_user_profile')]
    public function index(): Response
    {
        return $this->render('user_profile/index.html.twig', [
            'controller_name' => 'UserProfileController',
        ]);
    }

    #[Route('/user/change/{nombre}', name: 'cambiar_nombre')]
    public function cambiarNombre(Request $request, ManagerRegistry $doctrine, string $nombre): Response
    {
        $repo = $doctrine->getRepository(User::class);
        $user = $this->getUser();
        $user->setUserName($nombre);
        $entityManager = $doctrine->getManager();
        $userFromRepo = $repo->find($user->getId());
        $userFromRepo->setUserName($nombre);

        $entityManager->persist($userFromRepo);
        $entityManager->flush();
        return $this->redirectToRoute("index");
    }

    #[Route('/user/change_dos/{nombre}', name: 'cambiar_nombre_dos')]
    public function cambiarNombreDos(Request $request, ManagerRegistry $doctrine, string $nombre): Response
    {
        $user = $this->getUser();
        $user->setUserName($nombre);
        $this->container->get(TokenStorageInterface::class)->setToken(
            new UsernamePasswordToken($user, null, 'main', ["user"=>$user])
        );
        return $this->redirectToRoute("index");
        
        $repo = $doctrine->getRepository(User::class);
        $user = $this->getUser();
        $user->setUserName($nombre);
        $entityManager = $doctrine->getManager();
        $userFromRepo = $repo->find($user->getId());
        $userFromRepo->setUserName($nombre);

        $entityManager->persist($userFromRepo);
        $entityManager->flush();
        return $this->redirectToRoute("index");
    }
    
}
