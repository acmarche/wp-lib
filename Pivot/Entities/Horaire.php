<?php


namespace AcMarche\Pivot\Entities;


class Horaire
{
    /**
     * @var string
     */
    public $year;
    /**
     * @var string
     */
    public $lib;
    /**
     * @var Libelle
     */
    public $libelle;
    /**
     * @var string
     */
    public $texte;
    /**
     * @var Horline[]
     */
    public $horlines = [];
}
