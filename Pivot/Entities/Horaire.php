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
    public $lib;
    /**
     * @var Libelle
     */
    public $texte;
    /**
     * @var Horline[]
     */
    public $horlines = [];

    public function getLib(?string $language = 'fr'): string
    {
        if ($this->lib->get($language) && $this->lib->get($language)) {
            return $this->lib->get($language);
        }
        //try in french
        if ($titre = $this->getLib()) {
            return $titre;
        }

        return '';
    }

    public function getTexte(?string $language = 'fr'): string
    {
        if ($this->texte->get($language) && $this->texte->get($language)) {
            return $this->texte->get($language);
        }
        //try in french
        if ($titre = $this->getTexte()) {
            return $titre;
        }

        return '';
    }
}
