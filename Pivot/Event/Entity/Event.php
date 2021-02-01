<?php


namespace AcMarche\Pivot\Event\Entity;

use AcMarche\Pivot\Event\Parser;
use AcMarche\Theme\Inc\Router;
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
    private $medias;
    /**
     * @var Categorie[]
     */
    private $categories;

    public static function createFromStd(\DOMElement $offre): ?Event
    {
        self::$today         = new \DateTime();
        $parser              = new Parser($offre);
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
        $event->url          = Router::getUrlEvent($event);

        return $event;
    }

    private static function getDatesEvent(stdClass $offre): array
    {
        $dates    = [];
        $horaires = $offre->horaires->horaire;
        if (is_array($horaires->horline)) {
            foreach ($horaires->horline as $horaire) {
                $date                   = [];
                $date['date_fin']       = $horaire->date_fin;
                $date['date_deb']       = $horaire->date_deb;
                $date['date_affichage'] = $horaire->date_deb;
                list($date['day'], $date['month'], $date['year']) = explode("/", $date['date_deb']);
                if (self::isObsolete($date['year'], $date['month'], $date['day'])) {
                    continue;
                }
                $dates[] = $date;
            }
        } else {
            $date                   = [];
            $date['date_fin']       = $horaires->horline->date_fin;
            $date['date_deb']       = $horaires->horline->date_deb;
            $date['date_affichage'] = $horaires->texte[0];
            list($date['day'], $date['month'], $date['year']) = explode("/", $date['date_deb']);
            if (self::isObsolete($date['year'], $date['month'], $date['day'])) {
                return [];
            }
            $dates[] = $date;
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

    private static function isObsolete(string $year, string $month, string $day): bool
    {
        $dateEnd = $year.'-'.$month.'-'.$day;
        if ($dateEnd < self::$today->format('Y-m-d')) {
            return true;
        }

        return false;
    }
}
