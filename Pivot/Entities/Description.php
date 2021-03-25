<?php


namespace AcMarche\Pivot\Entities;


class Description
{
    /**
     * @var string
     */
    public $dat;
    /**
     * @var string
     */
    public $lot;
    /**
     * @var string
     */
    public $typ;
    /**
     * @var Libelle
     */
    public $texte;
    /**
     * @var Libelle
     */
    public $lib;
    /**
     * @var string
     */
    public $tri;

    public function getLib(?string $language = 'fr'): string
    {
        if ($this->lib->get($language) && $this->lib->get($language)) {
            return $this->lib->get($language);
        }
        //try in french
        if ($titre = $this->getLib()) {
            return $titre;
        }

        return 'lib found';
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

        return 'texte found';
    }
}
