<?php

namespace App\Entity;

use App\Repository\TweetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TweetRepository::class)]
class Tweet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private ?int $likes = null;

    #[ORM\ManyToOne(inversedBy: 'tweets')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        $this->formatContent();
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): static
    {
        $this->likes = $likes;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    private function formatContent(){
        $this->replaceURLs();
        $this->replaceHashtags();
        $this->replaceMentions();
    }
    private function replaceURLs(): void
    {
        while($this->isPossibleUrl()){
            $this->replaceURL();
        }
    }

    private function isPossibleUrl(): bool
    {
        $expression = '/\[+([^\]]+)\]\((.+)\)/';
        $count = preg_match($expression, $this->content, $matches);
        return $count != 0;
    }
    /**
     * Reemplaza [nombre-visible](url) por <a href='url'>nombre-visible<a>
     *
     * @param string $content
     * @return string
     */
    private function replaceURL(): void
    {
        /*
        Para escribir una url se usa la expresión [nombre-visible](url)
        La siguiente expresión coincide con dos grupos:
        El primero, [([^\]]+)], coincide con cualquier texto entre corchetes.
        El segundo, (.+), coincide con cualquier texto entre paréntesis.
        */
        $expression = '/\[+([^\]]+)\]\((.+)\)/';
        $this->content = preg_replace($expression, '<a href="\2">\1</a>', $this->content);

    }
    private function replaceMentions(): void
    {
        while($this->isPossibleMention()){
            $this->replaceMention();
        }
    }
    private function isPossibleMention(): bool
    {
        $expression = "/@([a-zA-Z0-9_-]+)/";
        $count = preg_match($expression, $this->content, $matches);
        return $count != 0;
    }
    private function replaceMention(): void
    {
        /* Capurar el grupo delimitado por el carácter @ y fin de línea o espacio
        */
        $expression = "/@([a-zA-Z0-9_-]+)/";
        // Replace @mention with the HTML code using regular expression
        $this->content = preg_replace($expression, '<a href="/tweets/user/\1">\1</a>', $this->content);

    }

    private function replaceHashtags(): void
    {
        while($this->isPossibleHashtag()){
            $this->replaceHashtag();
        }
    }
    private function isPossibleHashtag(): bool
    {
        $expression = "/#([a-zA-Z0-9_-]+)/";
        $count = preg_match($expression, $this->content, $matches);
        return $count != 0;
    }
    private function replaceHashtag(): void
    {
        /* Capurar el grupo delimitado por el carácter # y fin de línea o espacio
        */
        $expression = "/#([a-zA-Z0-9_-]+)/";
        // Replace @mention with the HTML code using regular expression
        $this->content = preg_replace($expression, '<a href="/tweets/hashtag/\1">\1</a>', $this->content);
     }
}
