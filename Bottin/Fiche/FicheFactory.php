<?php


namespace AcMarche\Bottin\Fiche;

use AcMarche\Bottin\Repository\BottinRepository;
use AcMarche\Bottin\Repository\WpBottinRepository;

class FicheFactory
{
    /**
     * @var WpBottinRepository
     */
    private $wpBottinRepository;
    /**
     * @var BottinRepository
     */
    private $bottinRepository;

    public function __construct()
    {
        $this->wpBottinRepository = new WpBottinRepository();
        $this->bottinRepository   = new BottinRepository();
    }

    public function getFicheBottin($ficheId): string
    {
        $content = '';

        $fiche = $this->bottinRepository->getFicheById($ficheId);

        $content .= ' '.$fiche->localite;
        $content .= ' '.$fiche->nom;
        $content .= ' '.$fiche->prenom;
        $content .= ' '.$fiche->email;
        $content .= ' '.$fiche->website;

        $content .= ' '.$fiche->contact_localite;
        $content .= ' '.$fiche->contact_email;

        $content .= ' '.$fiche->comment1;
        $content .= ' '.$fiche->comment2;
        $content .= ' '.$fiche->comment3;

        $content .= ' '.$fiche->facebook;
        $content .= ' '.$fiche->twitter;

        return $content;
    }
}
