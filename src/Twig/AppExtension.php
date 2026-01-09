<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class AppExtension extends AbstractExtension
{
    private $formatContentService;
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }
    public function getFilters(): array
    {
        return [
            new TwigFilter('formatMention', [$this, 'formatMention']),
            new TwigFilter('formatHashtag', [$this, 'formatHashtag']),
            new TwigFilter('formatURL', [$this, 'formatURL']),
        ];
    }

    public function formatMention(string $content): string
    {
        /* Capturar el grupo delimitado por el carácter @ y fin de línea o espacio
         */
        $expression = "/@[a-zA-Z0-9_-]+/";
        // Replace @mention with the HTML code using regular expression
        return \preg_replace_callback(
            $expression,
            function ($matches) {
                return "<a href='" . $this->urlGenerator->generate("user_tweets", ['username' => substr($matches[0], 1)]) . "'>" . $matches[0] . "</a>";
            },
            $content
        );

    }
    public function formatHashtag(string $content): string
    {
        /* Capturar el grupo delimitado por el carácter # y fin de línea o espacio
         */
        $expression = "/#([a-zA-Z0-9_-]+)/";
        // Replace @mention with the HTML code using regular expression
        return \preg_replace_callback(
            $expression,
            function ($matches) {
                return "<a href='" . $this->urlGenerator->generate("hashtag_tweets", ['hashtag' => substr($matches[0], 1)]) . "'>" . $matches[0] . "</a>";
            },
            $content
        );
    }
    public function formatURL(string $content): string
    {
        /*
        Para escribir una url se usa la expresión [nombre-visible](url)
        La siguiente expresión coincide con dos grupos:
        El primero, [/\[([^\]]+)\], coincide con cualquier texto entre corchetes.
        El segundo, \(([^)]+)\)/, coincide con cualquier texto entre paréntesis.
        */
        $expression = '/\[([^\]]+)\]\(([^)]+)\)/';
        return \preg_replace_callback(
            $expression,
            function ($matches) {
                return "<a href='" . $matches[2] . "'>" . $matches[1] . "</a>";
            },
            $content
        );

    }
}