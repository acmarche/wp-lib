<?php


namespace AcMarche\Pivot\Event\Entity;


class Geocode
{
    /**
     * @var string
     */
    public $x;
    /**
     * @var string
     */
    public $y;
    /**
     * @var string
     */
    public $trace;

    public function latitude()
    {
        return $this->x;
    }

    public function longitude()
    {
        return $this->y;
    }
}
