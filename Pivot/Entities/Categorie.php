<?php


namespace AcMarche\Pivot\Entities;


class Categorie
{
    /**
     * @var string
     */
    public $id;
    /**
     * @var Libelle
     */
    public $lib;
    /**
     * @var string
     */
    public $value;
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

        return '';
    }
}
