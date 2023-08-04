<?php

namespace App\Services;

class Slugger
{
    public static  function slugify(string $text): string
    {
        // Remplace les caractères non lettres ou chiffres par "-"
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // Translitère les textes en caractères ASCII
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // Supprime les caractères indésirables
        $text = preg_replace('~[^-\w]+~', '', $text);

        // Trim les tirets au début et à la fin
        $text = trim($text, '-');

        // Met en minuscule
        $text = strtolower($text);

        // Si le texte est vide, retourne 'n-a'
        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
