<?php


namespace AcMarche\Pivot\Entities;


use AcMarche\Pivot\Parser\HotelParser;

class Logement extends BaseEntity
{
    public static function createFromDom(\DOMElement $offre): ?Logement
    {
        $parser = new HotelParser($offre);
        $restauration = new self();
        $restauration->id = $parser->offreId();
        $restauration->titre = $parser->getAttributs('titre');
        $restauration->reference = $parser->getAttributs('off_id_ref');
        $restauration->publiable = $parser->getAttributs('publiable');
        $restauration->modif_date = $parser->getAttributs('modif_date');
        $restauration->geocode = $parser->geocodes();
        $restauration->localisation = $parser->localisation();
        $restauration->descriptions = $parser->descriptions();
        $restauration->contacts = $parser->contacts();
        $restauration->medias = $parser->medias();
        $restauration->categories = $parser->categories();
        $restauration->selections = $parser->selections();


        return $restauration;
    }
}
