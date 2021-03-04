<?php


namespace AcMarche\Pivot\Entities;

/**
 * @property string $id
 *
 * @property string $titre
 *
 * @property string $reference
 *
 * @property Geocode $geocode
 *
 * @property Localite $localisation
 *
 * @property string $url
 *
 * @property Contact[] $contacts
 *
 * @property Description[] $descriptions
 *
 * @property Media[] $medias
 *
 * @property Categorie[] $categories
 *
 * @property Selection[] $selections
 *
 * @property Horaire[] $horaires
 */
interface OffreInterface
{
    function contactPrincipal(): ?Contact;

    function communcationPrincipal(): array;

    function emailPrincipal(): ?string;

    function telPrincipal();

    function sitePrincipal();

    static function createFromDom(\DOMElement $offre): ?Offre;

    /**
     * Utilise dans @return Horline|null
     * @see EventUtils
     */
    function firstHorline(): ?Horline;

    /**
     * Raccourcis util a react
     *
     * @return Horline[]
     */
    function dates(): array;

    function firstImage(): ?string;

}
