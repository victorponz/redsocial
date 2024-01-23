<?php
namespace App\EventListener;

use App\Entity\Tweet;
use App\Service\FormatContentService;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postLoad, method: 'postLoad', entity: Tweet::class)]
class TweetLoadListener
{
    private $formatContentService;
    public function __construct(FormatContentService $formatContentService){
        $this->formatContentService = $formatContentService;
        
    }
    // the entity listener methods receive two arguments:
    // the entity instance and the lifecycle event
    public function postLoad(Tweet $tweet, PostLoadEventArgs $event): void
    {
        //$tweet->setFormattedContent($this->formatContentService->format($tweet->getContent()));
        // ... do something to notify the changes
    }
}