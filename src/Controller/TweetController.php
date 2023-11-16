<?php

namespace App\Controller;

use App\Entity\Tweet;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function tweetAdd (Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger, string $firewallName = 'main'): Response
    {

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
                $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

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
            $tweet->setUser($this->getUser());
            $tweet->setLikes(0);
            
            $entityManager = $doctrine->getManager();
            $entityManager->persist($tweet);
            $entityManager->flush();

            return new Response("Grabado " . $tweet->getContent());
        }
        return $this->render('tweet/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/tweets/user/{username}', name: 'user_tweets')]
    public function userTweets (Request $request, ManagerRegistry $doctrine, string $username): Response
    {
        $repo = $doctrine->getRepository(Tweet::class);
        $repoUser = $doctrine->getRepository(User::class);
        $user = $repoUser->findOneByUsername($username);
        $tweets = null;
        if ($user)
            $tweets = $user->getTweets();
   
        return $this->render('tweet/user_tweets.html.twig', [
            'user' => $user,
            'tweets' => $tweets
        ]);
    }

    #[Route('/tweets/hashtag/{hashtag}', name: 'hashtag_tweets')]
    public function hashtagTweets (Request $request, ManagerRegistry $doctrine, string $hashtag): Response
    {
        $repo = $doctrine->getRepository(Tweet::class);

        $tweets = $repo->getAllByHashtag($hashtag);

        return $this->render('tweet/hashtag_tweets.html.twig', [
            'hashtag' => $hashtag,
            'tweets' => $tweets
        ]);
    }
}
