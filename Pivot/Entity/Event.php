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

        if (is_array($descriptions->description)) {
            $event['description1'] = $descriptions->description[0]->texte;//Informations pratiques
            $event['description']  = $descriptions->description[1]->texte;//Description générale
        } else {
        }

        if (is_array($localisations)) {
            $localites = [];
            foreach ($localisations as $localisation) {
                $localites[] = $localisation->l_nom;
            }
            $event['localite'] = join(',', $localites);
        } else {
            $event['localite']  = $localisations->l_nom;
            $event['latitude']  = $localisations->x;
            $event['longitude'] = $localisations->y;
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
        $event['url']    = Router::EVENT_URL.$event['nom'];
        $event['images'] = $images;
        $event['id']     = $offre->off_id_ref;

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

        return $dates;
    }

    private static function isObsolete(string $dateEnd): bool
    {
        if ($dateEnd < self::$today->format('d-m-Y')) {
            return true;
        }

        return false;
    }
}
