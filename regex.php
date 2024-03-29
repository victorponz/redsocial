<?php
// Your Markdown string with links
$markdown = "This is a [Markdown link](https://www.example.com) exam[Markdown link](https://www.example.2com)ple. [otro](sdfg)";

// Define the regular expression pattern
$pattern = '/\[([^]]+)\]\(([^)]+)\)/';

// Use preg_replace_callback to replace Markdown links with HTML links
$html = preg_replace_callback($pattern, function ($matches) {
    $text = $matches[1];
    $url = $matches[2];
    return "<a href=\"$url\">$text</a>";
}, $markdown);

// Print the HTML result
echo $html;
exit;
  $expression = '/\[+([^\]]+)\]\((.+)\)/';
  $expression = '/\[+([^\]]+)\]/((.*?)\)';
////$expression = '/\((.*?)\)/';

$text = 'a [Este es un enlace](https://www.google.com) holoa @Bard123 a [sdff](sdg)';

$replaced_text = preg_replace($expression, '<a href="\2">\1</a>', $text);

echo $replaced_text;
exit;
$expression = '/@([a-zA-Z0-9_]+)@|$/';
$text = 'holoa @Bard123 a';

$replaced_text = preg_replace($expression, '<a href="tweets/\1">@\1</a>', $text);

//echo $replaced_text;

$expression = '/@([a-zA-Z0-9_]+)@\1/';
$text = '@Bard123_456 @victor';

$expression = "/@([a-zA-Z0-9_-]+)/";
// Replace @mention with the HTML code using regular expression
$text = preg_replace($expression, '<a href="/tweets/user/@\1">@\1</a>', $text);
echo $text;
exit;
//echo $replaced_text;

$string = "espacio@123-_23 espacio @victor";
$pattern = "/(@[a-zA-Z0-9_-]+)/";

// Replace @mention with the HTML code using regular expression
//$newString = preg_replace($pattern, '<a href="/tweets/\1">\1</a>', $string);
$count = preg_match($pattern, $string, $matches);

// Output the result
//echo $count;


$string = "espacio##123-_23 espacio #victor";
$pattern = "/(##[a-zA-Z0-9_-]+)/";

// Replace @mention with the HTML code using regular expression
//$newString = preg_replace($pattern, '<a href="/tweets/#\1">\1</a>', $string);
$count = preg_match_all($pattern, $string, $matches);
//echo $count;


$string = "espacio@123-_23 espacior";
$expression = "/(@[a-zA-Z0-9_-]+)/";
$count = preg_match($expression, $string, $matches);
echo $count;
        
        
 function replaceMentions(string $content): string
    {
        while($this->isPossibleMention($content)){
            $content = $this->replaceMention($content);
        }
        return $content;
    }
     function isPossibleMention($content): bool
    {
        $expression = "/(@[a-zA-Z0-9_-]+)/";
        $count = preg_match($expression, $content, $matches);
        return $count != 0;
    }
     function replaceMention(string $content): string
    {
        /* Capurar el grupo delimitado por el carácter @ y fin de línea o espacio
        */
        $expression = "/(@[a-zA-Z0-9_-]+)/";
        // Replace @mention with the HTML code using regular expression
        $content = preg_replace($expression, '<a href="/tweets/\1">\1</a>', $content);
        return $content;        

    }

$cadena = "@@victor";
$expression = "/@@([a-zA-Z0-9_-]+)/";
// Replace @mention with the HTML code using regular expression
echo preg_replace($expression, '<a href="/tweets/user/@\1">@\1</a>', $cadena);

?>


