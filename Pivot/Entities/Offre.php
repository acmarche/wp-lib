<?php


namespace AcMarche\Pivot\Entities;


use AcMarche\Pivot\Entities\Horline;
use AcMarche\Pivot\Parser\OffreParser;

class Offre extends BaseEntity
{
    public static function createFromDom(\DOMElement $offre): ?Offre
    {
        $parser = new OffreParser($offre);
        $offre = new self();
        $offre->id = $parser->offreId();
        $offre->titre = $parser->getAttributs('titre');
        $offre->reference = $parser->getAttributs('off_id_ref');
        $offre->publiable = $parser->getAttributs('publiable');
        $offre->modif_date = $parser->getAttributs('modif_date');
        $offre->geocode = $parser->geocodes();
        $offre->localisation = $parser->localisation();
        $offre->horaires = $parser->horaires();
        $offre->descriptions = $parser->descriptions();
        $offre->contacts = $parser->contacts();
        $offre->medias = $parser->medias();
        $offre->categories = $parser->categories();
        $offre->selections = $parser->selections();
        $offre->datesR = $offre->dates();
        $offre->image = $offre->firstImage();

        return $offre;
    }
    /**
     * Utilise dans @return Horline|null
     * @see EventUtils
     */
    public function firstHorline(): ?Horline
    {
        if (count($this->horaires) > 0) {
            if (count($this->horaires[0]->horlines)) {
                return $this->horaires[0]->horlines[0];
            }
        }

        return null;
    }

    /**
     * Raccourcis util a react
         *
     * @return Horline[]
     */
    public function dates(): array
    {
        $dates = [];
        foreach ($this->horaires as $horaire) {
            foreach ($horaire->horlines as $horline) {
                $dates[] = $horline;
            }
        }

        return $dates;
    }

    private function firstImage(): ?string
    {
       return count($this->medias) > 0 ? $this->medias[0]->url : null;
    }

}
