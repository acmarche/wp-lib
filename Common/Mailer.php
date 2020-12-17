<?php


namespace AcMarche\Common;

class Mailer
{
    public static function sendError(string $subject, string $message)
    {
        Env::loadEnv();
        $to = $_ENV['WEBMASTER_EMAIL'];
        wp_mail($to, $subject, $message);
    }
}
