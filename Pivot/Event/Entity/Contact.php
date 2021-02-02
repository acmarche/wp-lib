<?php


namespace AcMarche\Pivot\Event\Entity;

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
     * @var Communication[]
     */
    public $communications;

    public function localite() {
        return $this->l_nom;
    }
}
