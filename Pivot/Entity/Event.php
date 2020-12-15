<?php


namespace AcMarche\Pivot\Entity;

use stdClass;

class Event
{
    public static function createFromStd(stdClass $offre): array
    {
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
        $attributs = $offre->attributs;
        $horaires  = $offre->horaires->horaire;
        if (is_array($horaires->horline)) {
            foreach ($horaires->horline as $horaire) {
                $event['date_deb'] = $horaire->date_deb;
                list($event['day'], $event['month'], $event['year']) = explode("/", $event['date_deb']);
                $event['date_fin']       = $horaire->date_fin;
                $event['date_affichage'] = $horaire->date_deb;
            }
        } else {
            $event['date_deb'] = $horaires->horline->date_deb;
            list($event['day'], $event['month'], $event['year']) = explode("/", $event['date_deb']);
            $event['date_deb']       = $horaires->horline->date_fin;
            $event['date_affichage'] = $horaires->texte[0];
        }

        $event['url']    = 'iti';
        $event['images'] = $images;

        return $event;
    }
}
