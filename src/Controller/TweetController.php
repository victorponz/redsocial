<?php

namespace App\Controller;

use App\Entity\Tweet;
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
    public function index(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger, string $firewallName = 'main'): Response
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
            //$entityManager->persist($tweet);
            //$entityManager->flush();
            echo $this->replaceURLs($tweet->getContent());
            exit;
            //return $this->redirectToRoute('ficha_contacto', ["codigo" => $contacto->getId()]);
            return new Response("Grabado");
        }
        return $this->render('tweet/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    private function replaceURLs(string $content): string
    {
        while($this->isPossibleUrl($content)){
            $content = $this->replaceURL($content);
        }
        return $content;
    }

    private function isPossibleUrl($content): bool
    {

        $posini = strpos($content, "[");
        $posfin = strpos($content, ']', $posini);
       
        //Ahora comprobamos que el carácter siguient a ] es (
        if ($content[$posfin+1] != "("){
            return false;
        }else{
            $posParIzquierdo = $posfin + 1;
        }
        $posParDerecho = strpos($content, ')', $posParIzquierdo);
        if ($posParDerecho !== false){
            return true;
        }
        return false;
    }
    /**
     * Reemplaza [nombre-visible](url) por <a href='url'>nombre-visible<a>
     *
     * @param string $content
     * @return string
     */
    private function replaceURL(string $content): string
    {
        $posini = strpos($content, "[");
        $posfin = strpos($content, ']', $posini);
        
        //Ahora comprobamos que el carácter siguient a ] es (
        if ($content[$posfin+1] != "("){
            return $content;
        }else{
            $posParIzquierdo = $posfin + 1;
        }
        $posParDerecho = strpos($content, ')', $posParIzquierdo);
        if ($posParDerecho !== false){
            //todo bien
            $texto = substr($content, $posini + 1, $posfin  - $posini - 1);
            $url = substr($content, $posParIzquierdo + 1, $posParDerecho - $posParIzquierdo - 1);
            $enlace = "<a href='{$texto}'>{$url}</a>";
           
            $content = substr($content, 0, $posini) . $enlace . substr($content, $posParDerecho+1);
            return $content;            
        }
        return $content;
    }
}
