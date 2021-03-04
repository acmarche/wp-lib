<?php


namespace AcMarche\Pivot\Entities;

class Contact
{
    /**
     * @var string
     */
    public $id;
    /**
     * @var string
     */
    public $lib;
    /**
     * @var string
     */
    public $civilite;
    /**
     * @var string
     */
    public $noms;
    /**
     * @var string
     */
    public $prenoms;
    /**
     * @var string
     */
    public $societe;
    /**
     * @var string
     */
    public $adresse;
    /**
     * @var string
     */
    public $numero;
    /**
     * @var string
     */
    public $boite;
    /**
     * @var string
     */
    public $postal;
    /**
     * @var string
     */
    public $pays;
    /**
     * @var string
     */
    public $l_nom;
    /**
     * @var string
     */
    public $remarque;
    /**
     * @var Communication[]
     */
    public $communications;
    /**
     * @var array
     */
    public $lgs;

    public function __construct()
    {
        $this->communications = [];
    }

    public function localite() {
        return $this->l_nom;
    }
}
