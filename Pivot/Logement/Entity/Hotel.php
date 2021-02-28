<?php

namespace AcMarche\Pivot\Logement\Entity;

use AcMarche\Pivot\Entities\BaseEntity;
use AcMarche\Pivot\Parser\HotelParser;

class Hotel extends BaseEntity
{

    public function __construct()
    {
        parent::__construct();
        $this->horaires = [];
    }

    public static function createFromDom(\DOMElement $offre): ?Hotel
    {
        $parser = new HotelParser($offre);
        $hotel = new self();
        $hotel->id = $parser->offreId();
        $hotel->titre = $parser->getAttributs('titre');
        $hotel->reference = $parser->getAttributs('off_id_ref');
        $hotel->publiable = $parser->getAttributs('publiable');
        $hotel->modif_date = $parser->getAttributs('modif_date');
        $hotel->geocode = $parser->geocodes();
        $hotel->localisation = $parser->localisation();
        $hotel->descriptions = $parser->descriptions();
        $hotel->contacts = $parser->contacts();
        $hotel->medias = $parser->medias();
        $hotel->categories = $parser->categories();
        $hotel->selections = $parser->selections();


        return $hotel;
    }


}
