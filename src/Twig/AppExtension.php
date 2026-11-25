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
        // El lookbehind evita capturar la @ dentro de una URL (https://medium.com/@ana) o de un email
        $expression = "/(?<![\\w\\/.?=&%-])@[a-zA-Z0-9_-]+/";
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
        // Las dos primeras alternativas reconocen una URL (https://web.com/doc#seccion) o una
        // entidad HTML ya escapada (&#039;) y con (*SKIP)(*FAIL) las saltan enteras, para no
        // capturar su #. Así un hashtag pegado a texto (hola#php) sí se enlaza.
        $expression = '/https?:\/\/[^\s<>"\']+(*SKIP)(*FAIL)|&#\d+;(*SKIP)(*FAIL)|#([a-zA-Z0-9_-]+)/';
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
        Convierte en enlace tanto [nombre-visible](url) como una URL suelta (http o https).
        Las dos formas van en una sola expresión con alternativa (|) para que una URL que ya
        está dentro de un [texto](url) no se enlace dos veces.

        1ª alternativa, enlace con nombre:
            \[([^\]]+)\]           grupo 1: texto entre corchetes
            \((https?:\/\/[^\s)]+)\)  grupo 2: URL http(s) entre paréntesis
        2ª alternativa, URL suelta:
            (?<![\w\/'"=])         no empieza pegada a una palabra o dentro de un atributo
            https?:\/\/             esquema http:// o https://
            [^\s<>"']*             resto de la URL: hasta un espacio, una etiqueta o comillas
            [^\s<>"'.,;:!?)\]]     el último carácter no puede ser puntuación, para no
                                   tragarse el punto o la coma que cierra la frase
        Solo se aceptan http y https: así [x](javascript:...) no genera un enlace peligroso.
        */
        $expression = '/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)|(?<![\w\/\'"=])(https?:\/\/[^\s<>"\']*[^\s<>"\'.,;:!?)\]])/i';
        return \preg_replace_callback(
            $expression,
            function ($matches) {
                if (!empty($matches[3])) {
                    // URL suelta: el texto visible es la propia URL
                    return "<a href='" . $matches[3] . "' target='_blank' rel='noopener noreferrer nofollow'>" . $matches[3] . "</a>";
                }
                return "<a href='" . $matches[2] . "' target='_blank' rel='noopener noreferrer nofollow'>" . $matches[1] . "</a>";
            },
            $content
        );
    }
}