<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Tweet;
use App\Entity\User;
use App\Repository\TweetRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use App\Form\TweetFormType;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


class TweetController extends AbstractController
{
    use TargetPathTrait;

    #[Route('/tweet/add', name: 'tweet_add')]
    public function tweetAdd(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger, string $firewallName = 'main'): Response
    {

        // Guardar la URL de la página actual para redirigir al usuario después de la operación
        // si el usuario no está autenticado
        $this->saveTargetPath($request->getSession(), $firewallName, $this->generateUrl("tweet_add"));
        $this->denyAccessUnlessGranted("ROLE_USER");
        $tweet = new Tweet();

        $form = $this->createForm(TweetFormType::class, $tweet);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tweet = $form->getData();
            $image = $form->get('image')->getData();
            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

                // Move the file to the directory where images are stored
                try {

                    $image->move($this->getParameter('images_directory'), $newFilename);


                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    new Response("Se ha producido un error al procesar la imagen" . $e->getMessage());
                }

                // updates the 'file$filename' property to store the PDF file name
                // instead of its contents
                $tweet->setImage($newFilename);
            }

            $repo = $doctrine->getRepository(User::class);
            $userTweet = $repo->find($this->getUser()->getId());

            $tweet->setUser($userTweet);
            $tweet->setLikes(0);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($tweet);
            $entityManager->flush();
            return $this->redirect($this->generateUrl("user_tweets_timeline", []));
        }
        return $this->render('tweet/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/tweets/user/@{username}', name: 'user_tweets')]
    public function userTweets(Request $request, ManagerRegistry $doctrine, string $username): Response
    {
        /**
         * @var UserRepository $repoUser
         */
        $repoUser = $doctrine->getRepository(User::class);
        $tweetUser = $repoUser->findOneByUsername($username);
        $tweets = null;
        if ($tweetUser)
            $tweets = $tweetUser->getTweets();

        return $this->render('tweet/user_tweets.html.twig', [
            'tweetUser' => $tweetUser,
            'tweets' => $tweets
        ]);
    }

    #[Route('/tweets/hashtag/{hashtag}', name: 'hashtag_tweets')]
    public function hashtagTweets(Request $request, ManagerRegistry $doctrine, string $hashtag): Response
    {
        /**
         * @var TweetRepository $repo
         */
        $repo = $doctrine->getRepository(Tweet::class);

        $tweets = $repo->getAllByHashtag($hashtag);

        return $this->render('tweet/hashtag_tweets.html.twig', [
            'hashtag' => $hashtag,
            'tweets' => $tweets
        ]);
    }

    #[Route('/tweet/{id}/like', name: 'tweet_like', requirements: ['id' => '\d+'])]
    public function like(Request $request, ManagerRegistry $doctrine, int $id, string $firewallName = 'main'): JsonResponse
    {
        $this->saveTargetPath($request->getSession(), $firewallName, $this->generateUrl("tweet_like", ['id' => $id]));
        $this->denyAccessUnlessGranted("ROLE_USER");

        $repo = $doctrine->getRepository(Tweet::class);

        $tweet = $repo->find($id);
        $numLikes = 0;
        if ($tweet) {
            //No seamos narcisistas!!
            $numLikes = $tweet->getLikes();
            /**
             * @var UserRepository $repoUser
             */
            $repoUser = $doctrine->getRepository(User::class);
            $userTMP = $repoUser->findOneByUsername($this->getUser()->getUsername());
            if ($userTMP != $tweet->getUser()) {
                //Comprobar que no haya dado ya like
                $repoLikes = $doctrine->getRepository(Like::class);
                $tmpLike = $repoLikes->findOneBy(['user' => $this->getUser(), 'tweet' => $tweet]);

                if (empty($tmpLike)) {
                    $like = new Like();
                    $like->setUser($userTMP);
                    $like->setTweet($tweet);
                    $entityManager = $doctrine->getManager();
                    //Actualizar el contador de likes
                    $tweet->addLike();
                    $entityManager->persist($like);
                    $numLikes++;
                    $entityManager->flush();
                }
            }
        }
        $data = ["tweetId" => $id, "numLikes" => $numLikes];
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/tweet/{id}/wholikes', name: 'tweet_wholikes', requirements: ['id' => '\d+'])]
    public function whoLikes(Request $request, ManagerRegistry $doctrine, int $id, string $firewallName = 'main'): JsonResponse
    {
        // $this->saveTargetPath($request->getSession(), $firewallName, $this->generateUrl("tweet_like", ['id' => $id]));
        // $this->denyAccessUnlessGranted("ROLE_USER");

        $repo = $doctrine->getRepository(Tweet::class);

        $tweet = $repo->find($id);

        $numLikes = 0;
        $data = [];
        if ($tweet) {
            foreach ($tweet->getLikesEntity() as $like) {
                $data[] = [
                    "id" => $like->getId(),
                    "username" => ($like->getUser()->getUserName()),
                ];
            }
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }
    #[Route('/tweets', name: 'user_tweets_timeline')]
    public function userTweetsTimeLine(Request $request, ManagerRegistry $doctrine, string $firewallName = 'main'): Response
    {
        $this->saveTargetPath($request->getSession(), $firewallName, $this->generateUrl("user_tweets_timeline", []));
        $this->denyAccessUnlessGranted("ROLE_USER");

        $repo = $doctrine->getRepository(Tweet::class);
        //No se por qué pero se hacen recursivos. Cada vez que refrescas la página se van concatenando
        //Será porque está mal hecho el parsear los mensajes?
        //$tweets = $this->getUser()->getTweets();
        /**
         * @var UserRepository $repoUser
         */
        $repoUser = $doctrine->getRepository(User::class);
        $user = $repoUser->findOneByUsername($this->getUser()->getUsername());
        $tweets = $user->getTweets();
        return $this->render('tweet/user_tweets.html.twig', [
            'tweetUser' => $user,
            'tweets' => $tweets
        ]);
    }

}
