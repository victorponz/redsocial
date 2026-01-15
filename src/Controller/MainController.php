<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Tweet;
use App\Repository\UserRepository;
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
    public function userProfile(Request $request, ManagerRegistry $doctrine, string $username, string $firewallName = 'main'): Response
    {
        $this->saveTargetPath($request->getSession(), $firewallName, $this->generateUrl("user_profile", ['username' => $username]));

        $this->denyAccessUnlessGranted("ROLE_USER");

        /**
         * @var UserRepository $repo
         */
        $repo = $doctrine->getRepository(User::class);
        $user = $repo->findOneByUsername($username);

        if (!$user) {
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

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        /** @var UserRepository $repo */
        $repo = $doctrine->getRepository(User::class);

        // Recargamos desde BD para asegurar que está "managed" y evitar errores de asociación en colecciones
        $user = $repo->find($currentUser->getId());

        $userToFollow = $repo->findOneBy(['username' => $username]);

        if (!$userToFollow) {
            throw $this->createNotFoundException("Usuario no encontrado");
        }
        // Si el usuario al que se quiere seguir no es el mismo que el logeado
        if ($userToFollow->getId() != $user->getId()) {
            $entityManager = $doctrine->getManager();
            $userToFollow->addFollowing($user);
            $entityManager->persist($userToFollow);
            $entityManager->flush();
        }

        return new JsonResponse([], Response::HTTP_OK);
    }


    #[Route('/user/@{username}/following', name: 'user_following')]
    public function following(Request $request, ManagerRegistry $doctrine, string $username, string $firewallName = 'main'): Response
    {
        $this->saveTargetPath($request->getSession(), $firewallName, $this->generateUrl("user_following", ['username' => $username]));
        $this->denyAccessUnlessGranted("ROLE_USER");
        /**
         * @var UserRepository $repo
         */
        $repo = $doctrine->getRepository(User::class);

        $user = $repo->findOneByUsername($username);
        return $this->render('user/following.html.twig', [
            'usersFollowing' => $user->getFollowing()
        ]);

    }


    #[Route('/user/@{username}/following/json', name: 'user_following_json')]
    public function followingJson(Request $request, ManagerRegistry $doctrine, string $username, string $firewallName = 'main'): JsonResponse
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        /**
         * @var UserRepository $repo
         */
        $repo = $doctrine->getRepository(User::class);

        $user = $repo->findOneByUsername($username);
        $data = [];
        if ($user) {
            foreach ($user->getFollowing() as $following) {
                $data[] = [
                    "id" => $following->getId(),
                    "username" => ($following->getUserName()),
                ];
            }
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }


    #[Route('/user/@{username}/followers', name: 'user_followers')]
    public function followers(Request $request, ManagerRegistry $doctrine, string $username, string $firewallName = 'main'): JsonResponse
    {
        $this->saveTargetPath($request->getSession(), $firewallName, $this->generateUrl("user_followers", ['username' => $username]));
        $this->denyAccessUnlessGranted("ROLE_USER");
        /**
         * @var UserRepository $repo
         */
        $repo = $doctrine->getRepository(User::class);

        $user = $repo->findOneByUsername($username);
        // TODO Falta la plantilla
        $data = [];
        if ($user) {
            foreach ($user->getFollowers() as $follower) {
                $data[] = [
                    "id" => $follower->getId(),
                    "username" => ($follower->getUserName()),
                ];
            }
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }
    #[Route('/user/@{username}/followers/json', name: 'user_followers_json')]
    public function followersJson(Request $request, ManagerRegistry $doctrine, string $username, string $firewallName = 'main'): JsonResponse
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        /**
         * @var UserRepository $repo
         */
        $repo = $doctrine->getRepository(User::class);

        $user = $repo->findOneByUsername($username);
        $data = [];
        if ($user) {
            foreach ($user->getFollowers() as $follower) {
                $data[] = [
                    "id" => $follower->getId(),
                    "username" => ($follower->getUserName()),
                ];
            }
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }
}
