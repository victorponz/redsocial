<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use App\Service\FormatContentService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    private $formatContentService;
    public function __construct(FormatContentService $formatContentService){
        $this->formatContentService = $formatContentService;
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
        return \preg_replace_callback($expression, 
            function ($matches) {
                return "<a href='" . $this->formatContentService->getUrlGenerator()->generate("user_tweets", ['username'=>substr($matches[0], 1)]) . "'>" . $matches[0] . "</a>";
            },
            $content);

    }
    public function formatHashtag(string $content): string
    {
        /* Capturar el grupo delimitado por el carácter # y fin de línea o espacio
        */
        $expression = "/#([a-zA-Z0-9_-]+)/";
        // Replace @mention with the HTML code using regular expression
        return \preg_replace_callback($expression, 
            function ($matches) {
                return "<a href='" . $this->formatContentService->getUrlGenerator()->generate("hashtag_tweets", ['hashtag'=>substr($matches[0], 1)]) . "'>" . $matches[0] . "</a>";
            },
            $content);
    }
    public function formatURL(string $content): string
    {
        /*
        Para escribir una url se usa la expresión [nombre-visible](url)
        La siguiente expresión coincide con dos grupos:
        El primero, [([^\]]+)], coincide con cualquier texto entre corchetes.
        El segundo, (.+), coincide con cualquier texto entre paréntesis.
        */
        $expression = '/(https?:\/\/\S+)/';
        return \preg_replace_callback($expression, 
            function ($matches) {
                    return "<a href='".  $matches[0] . "'>" . $matches[0] . "</a>";               
            },
            $content);

    }
}