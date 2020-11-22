<?php


namespace AcMarche\Pivot;


class Pivot
{
    /**
     * 0 = (valeur par défaut) génère des offres ne contenant que le codeCgt et les dates de
     * création et de dernière modification.
     * • 1 = génère des « résumés » d’offres, ne contenant que le codeCgt, le nom, l’adresse et la
     * géolocalisation, ainsi que le classement, le label Qualité Wallonie et média par défaut
     * associé à l’offre.
     * • 2 = produit des offres au contenu complet. Les offres filles des relations ne sont
     * représentées que par leur codeCgt.
     * • 3 = produit les offres au contenu complet, avec également un contenu complet pour les
     * offres liées
     */

    const QUERY_DETAIL_LVL_DEFAULT = 0;
    const QUERY_DETAIL_LVL_RESUME = 1;
    const QUERY_DETAIL_LVL_COMPLET = 2;
    const QUERY_DETAIL_LVL_LIES = 3;

    const FORMAT_JSON = 'json';
    const FORMAT_XML = 'xml';
    const FORMAT_KML = 'kml';
    const FORMAT_ATOM = 'atom';
}
