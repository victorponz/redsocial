<?php
namespace App\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
class FormatContentService
{
    private $urlGenerator;
    public function __construct(UrlGeneratorInterface $urlGenerator){
        $this->urlGenerator = $urlGenerator;

    }
  
    private function replaceURL(string $content): string
    {
        /*
        Para escribir una url se usa la expresión [nombre-visible](url)
        La siguiente expresión coincide con dos grupos:
        El primero, [([^\]]+)], coincide con cualquier texto entre corchetes.
        El segundo, (.+), coincide con cualquier texto entre paréntesis.
        */
        $expression = '/(https?:\/\/\S+)/';
        return preg_replace($expression, '<a href="\1">\1</a>', $content);

    }
    private function replaceMention(string $content): string
    {
        /* Capturar el grupo delimitado por el carácter @ y fin de línea o espacio
        */
        $expression = "/@([a-zA-Z0-9_-]+)/";
        // Replace @mention with the HTML code using regular expression
        return preg_replace($expression, '<a href="/tweets/user/@\1">@\1</a>', $content);

    }
    private function replaceHashtag(string $content): string
    {
        /* Capturar el grupo delimitado por el carácter # y fin de línea o espacio
        */
        $expression = "/#([a-zA-Z0-9_-]+)/";
        // Replace @mention with the HTML code using regular expression
        return preg_replace($expression, '<a href="/tweets/hashtag/\1">#\1</a>', $content);
    }

    public function format(string $originalContent): string
    {
        $formattedContent = $originalContent;
        $formattedContent = $this->replaceURL($formattedContent);
        $formattedContent = $this->replaceHashtag($formattedContent);
        $formattedContent = $this->replaceMention($formattedContent);

        return $formattedContent;
    }
}
