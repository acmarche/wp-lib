<?php


namespace AcMarche\Pivot\Entities;


class Communication
{
    /**
     * @var string
     */
    public $val;
    /**
     * @var string
     */
    public $typ;
    /**
     * @var string
     */
    public $tri;
    /**
     * @var Libelle
     */
    public $lib;

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
