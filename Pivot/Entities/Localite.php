<?php


namespace AcMarche\Pivot\Entities;


class Localite
{
    /**
     * @var string
     */
    public $id;
    /**
     * @var string
     */
    public $l_nom;
    /**
     * @var string
     */
    public $postal;
    /**
     * @var string
     */
    public $com_id;
    /**
     * @var string
     */
    public $c_nom;
    /**
     * @var string
     */
    public $reg_id;
    /**
     * @var string
     */
    public $x;
    /**
     * @var string
     */
    public $y;

    public function localite() {
        return $this->l_nom;
    }

    public function latitude()
    {
        return $this->y;
    }

    public function longitude()
    {
        return $this->x;
    }
}
