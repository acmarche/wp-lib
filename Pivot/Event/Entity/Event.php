<?php


namespace AcMarche\Pivot\Event\Entity;

use AcMarche\Pivot\Event\EventUtils;
use AcMarche\Pivot\Parser\EventParser;
use AcMarche\Pivot\RouterHades;

class Event
{
    /**
     * @var string
     */
    public $id;
    /**
     * @var string
     */
    public $titre;
    /**
     * @var string
     */
    public $reference;
    /**
     * @var Geocode
     */
    public $geocode;
    /**
     * @var Localite
     */
    public $localisation;
    /**
     * @var string
     */
    public $url;
    /**
     * @var Contact[]
     */
    public $contacts;
    /**
     * @var Horaire[]
     */
    public $horaires;
    /**
     * @var Description[]
     */
    public $descriptions;
    /**
     * @var Media[]
     */
    public $medias;
    /**
     * @var Categorie[]
     */
    public $categories;
    /**
     * @var Selection[]
     */
    public $selections;

    public function __construct()
    {
        $this->categories = [];
        $this->medias     = [];
        $this->horaires   = [];
        $this->contacts   = [];
    }

    public static function createFromDom(\DOMElement $offre): ?Event
    {
        $parser              = new EventParser($offre);
        $event               = new self();
        $event->id           = $parser->offreId();
        $event->titre        = $parser->getAttributs('titre');
        $event->reference    = $parser->getAttributs('off_id_ref');
        $event->geocode      = $parser->geocodes();
        $event->localisation = $parser->localisation();
        $event->horaires     = $parser->horaires();
        $event->descriptions = $parser->descriptions();
        $event->contacts     = $parser->contacts();
        $event->medias       = $parser->medias();
        $event->categories   = $parser->categories();
        $event->selections   = $parser->selections();
        $event->url          = RouterHades::getUrlEvent($event);
        $event->datesR       = $event->dates();

        if (EventUtils::isEventObsolete($event)) {
            return null;
        }

        EventUtils::sortDates($event);

        return $event;
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
     * @param Event $event
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


}
