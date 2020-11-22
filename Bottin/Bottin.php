<?php


namespace AcMarche\Bottin;


use AcMarche\Common\Env;

class Bottin
{
    public static function sendEmail(string $sujet, string $message): void
    {
        Env::loadEnv();
        wp_mail($_ENV['DB_BOTTIN_EMAIL'], $sujet, $message);
    }

    public static function getUrlBottin(): string
    {
        Env::loadEnv();

        return $_ENV['DB_BOTTIN_URL'].'/bottin/fiches/';
    }

    public static function getUrlDocument(): string
    {
        Env::loadEnv();

        return $_ENV['DB_BOTTIN_URL'].'/bottin/documents/';
    }

    public function getImageUrl()
    {
        //  /public/bottin/fiches/
    }

    public static function getExcerpt(\stdClass $fiche): string
    {
        $excerpt = $fiche->localite;
        if ($fiche->nom) {
            $excerpt .= ' '.$fiche->nom.''.$fiche->prenom.' <br> ';
        }
        if ($fiche->telephone || $fiche->gsm || $fiche->telephone_autre) {
            $excerpt .= ' '.$fiche->telephone.''.$fiche->gsm.''.$fiche->telephone_autre;
        }
        if ($fiche->email) {
            $excerpt .= ' '.$fiche->email;
        }

        return $excerpt;
    }
}
