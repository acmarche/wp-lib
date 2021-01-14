<?php


namespace AcMarche\Pivot\Entity;

use AcMarche\Theme\Inc\Router;
use stdClass;

class Event
{
    private static $today = null;

    public static function createFromStd(stdClass $offre): ?array
    {
        self::$today = new \DateTime();

        $event = [];
        if (is_array($offre->titre)) {
            $event['nom'] = $offre->titre[0];
        } else {
            $event['nom'] = $offre->titre;
        }
        $localisations = $offre->localisation->localite;
        $descriptions  = $offre->descriptions;
        if ($offre->off_id_ref == 'BQZ2ZGN9') {
            //   dump($offre);
        }
        $event['description1'] = [];
        $event['description']  = [];
        if (is_array($descriptions->description)) {
            if ( ! is_array($description1 = $descriptions->description[1]->texte)) {
                $event['description1'][0] = $description1;
            } else {
                $event['description1'] = $description1;
            }
            if ( ! is_array($description = $descriptions->description[1]->texte)) {
                $event['description'][0] = $description;
            } else {
                $event['description'] = $description;
            }
        }

        if (is_array($localisations)) {
            $localites = [];
            foreach ($localisations as $localisation) {
                $localites[] = $localisation->l_nom;
            }
            $event['localite'] = join(',', $localites);
        } else {
            $event['localite']  = $localisations->l_nom;
            $event['latitude']  = $localisations->y;
            $event['longitude'] = $localisations->x;
        }
        $medias = $offre->medias->media;
        $images = [];
        if (is_array($medias)) {
            foreach ($medias as $media) {
                $images[] = $media->url;
            }
        } else {
            $images[] = $medias->url;
        }
        $attributs       = $offre->attributs;
        $event['dates']  = self::getDatesEvent($offre);
        $event['images'] = $images;
        $event['id']     = $offre->off_id_ref;
        $event['url']    = Router::getUrlEvent($event);

        return $event;
    }

    private static function getDatesEvent(stdClass $offre): array
    {
        $dates    = [];
        $horaires = $offre->horaires->horaire;
        if (is_array($horaires->horline)) {
            foreach ($horaires->horline as $horaire) {
                if (self::isObsolete($horaire->date_fin)) {
                    continue;
                }
                $date = [];
                //   dump($event['nom'], $horaire->date_fin);
                $date['date_fin'] = $horaire->date_fin;
                $date['date_deb'] = $horaire->date_deb;
                list($date['day'], $date['month'], $date['year']) = explode("/", $date['date_deb']);
                $date['date_affichage'] = $horaire->date_deb;
                $dates[]                = $date;
            }
        } else {
            $date = [];
            if (self::isObsolete($horaires->horline->date_fin)) {
                return [];
            }
            $date['date_fin'] = $horaires->horline->date_fin;
            $date['date_deb'] = $horaires->horline->date_deb;
            list($date['day'], $date['month'], $date['year']) = explode("/", $date['date_deb']);
            $date['date_affichage'] = $horaires->texte[0];
            $dates[]                = $date;
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

    private function getContacts(stdClass $offre)
    {
//todo
    }

    private function getHoraires(stdClass $offre)
    {
//todo
    }

    private static function isObsolete(string $dateEnd): bool
    {
        if ($dateEnd < self::$today->format('d-m-Y')) {
            return true;
        }

        return false;
    }
}
