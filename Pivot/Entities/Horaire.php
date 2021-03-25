<?php


namespace AcMarche\Pivot\Entities;


class Horaire
{
    /**
     * @var string
     */
    public $year;
    /**
     * @var Libelle
     */
    public $libelle;
    /**
     * @var Libelle
     */
    public $texte;
    /**
     * @var Horline[]
     */
    public $horlines = [];
}
