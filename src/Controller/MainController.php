<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Tweet;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class MainController extends AbstractController
{
    use TargetPathTrait;
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
    public function userProfile(ManagerRegistry $doctrine, string $username): Response
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
    public function follow(Request $request, ManagerRegistry $doctrine, string $username, string $firewallName = 'main'): JsonResponse
    {
        $this->saveTargetPath($request->getSession(), $firewallName, $this->generateUrl("user_follow", ['username' => $username]));
        $this->denyAccessUnlessGranted("ROLE_USER");

        $repo = $doctrine->getRepository(User::class);

        $userToFollow = $repo->findOneByUsername($username);
        $userWhoFollows = $repo->findOneByUsername($this->getUser()->getUsername());
       
        if ($userToFollow != $userWhoFollows){
            $entityManager = $doctrine->getManager();
            $userToFollow->addUserWhoFollows($userWhoFollows);
            $entityManager->persist($userToFollow);            
            $entityManager->flush();            
        }
        //Devolvemos un array vacío tanto si ha ido correcto como si no
        $data = [];
        return new JsonResponse($data, Response::HTTP_OK);
    }     

    #[Route('/user/@{username}/following', name: 'user_following')]
    public function followers(Request $request, ManagerRegistry $doctrine, string $username, string $firewallName = 'main'): JsonResponse
    {
        $this->saveTargetPath($request->getSession(), $firewallName, $this->generateUrl("user_following", ['username' => $username]));
        $this->denyAccessUnlessGranted("ROLE_USER");

        $repo = $doctrine->getRepository(User::class);

        $userToFollow = $repo->findOneByUsername($username);
        $data = [];
        if ($userToFollow){            
            foreach($userToFollow->getFollows()  as $following){
                $data[] = [
                    "id"=> $following->getId(),
                    "username" => ($following->getUserName()),
                ];
            }
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }     


    #[Route('/user/@{username}/followers', name: 'user_followers')]
    public function following(Request $request, ManagerRegistry $doctrine, string $username, string $firewallName = 'main'): JsonResponse
    {
        $this->saveTargetPath($request->getSession(), $firewallName, $this->generateUrl("user_followers", ['username' => $username]));
        $this->denyAccessUnlessGranted("ROLE_USER");

        $repo = $doctrine->getRepository(User::class);

        $userToFollow = $repo->findOneByUsername($username);
        $data = [];
        if ($userToFollow){            
            foreach($userToFollow->getUsersWhoFollow()  as $follower){
                $data[] = [
                    "id"=> $follower->getId(),
                    "username" => ($follower->getUserName()),
                ];
            }
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }  
}
