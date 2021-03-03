<?php

namespace AcMarche\Pivot\Entities;

use AcMarche\Pivot\Event\Entity\Categorie;
use AcMarche\Pivot\Event\Entity\Contact;
use AcMarche\Pivot\Event\Entity\Description;
use AcMarche\Pivot\Event\Entity\Geocode;
use AcMarche\Pivot\Event\Entity\Localite;
use AcMarche\Pivot\Event\Entity\Media;
use AcMarche\Pivot\Event\Entity\Selection;


abstract class BaseEntity implements OffreInterface
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
        $this->medias = [];
        $this->contacts = [];
    }

    public function contactPrincipal(): ?Contact
    {
        $contacts = array_filter(
            $this->contacts,
            function ($contact) {
                if (isset($contact->lgs['main']) && $contact->lgs['main'] == 'ap') {
                    return $contact;
                }

                return [];
            }
        );

        return count($contacts) > 0 ? $contacts[0] : null;
    }

    public function communciationPrincipal(): array
    {
        $coms = [];
        $contact = $this->contactPrincipal();
        if ($contact) {
            foreach ($contact->communications as $communication) {
                dump($communication);
                $coms[$communication->type] = [$communication->name => $communication->value];
            }
        }

        return $coms;
    }

}
