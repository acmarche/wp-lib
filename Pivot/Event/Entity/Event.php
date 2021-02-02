<?php


namespace AcMarche\Pivot\Event\Entity;

use AcMarche\Pivot\Parser\EventParser;
use AcMarche\Theme\Inc\Router;
use DateTime;
use stdClass;

class Event
{
    private static $today = null;
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
        self::$today         = new DateTime();
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
        $event->url          = Router::getUrlEvent($event);

        return $event;
    }

    private static function getDatesEvent(Event $event): array
    {
        foreach ($event->horaires as $horaire) {
            $horline = $horaire->horline;
            list($day, $month, $year) = explode("/", $horline->date_fin);
            if (self::isObsolete($year, $month, $day)) {
                continue;
            }
        }

        usort(
            $dates,
            function ($a, $b) {
                {
                    $debut1 = $a['year'].'-'.$a['month'].'-'.$a['day'];
                    $debut2 = $b['year'].'-'.$b['month'].'-'.$b['day'];
                    if ($debut1 == $debut2) {
                        return 0;
                    }

                    return ($debut1 < $debut2) ? -1 : 1;
                }
            }
        );

        return $dates;
    }

    private static function sortEvents()
    {

    }

    private static function isObsolete(string $year, string $month, string $day): bool
    {
        $dateEnd = $year.'-'.$month.'-'.$day;
        if ($dateEnd < self::$today->format('Y-m-d')) {
            return true;
        }

        return false;
    }
}
