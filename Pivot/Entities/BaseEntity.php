<?php

namespace AcMarche\Pivot\Entities;

use AcMarche\Pivot\Entities\Categorie;
use AcMarche\Pivot\Entities\Contact;
use AcMarche\Pivot\Entities\Description;
use AcMarche\Pivot\Entities\Geocode;
use AcMarche\Pivot\Entities\Horaire;
use AcMarche\Pivot\Entities\Localite;
use AcMarche\Pivot\Entities\Media;
use AcMarche\Pivot\Entities\Selection;


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
    /**
     * @var Horaire[]
     */
    public $horaires;

    public function __construct()
    {
        $this->categories = [];
        $this->medias = [];
        $this->contacts = [];
        $this->horaires = [];
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

    public function communcationPrincipal(): array
    {
        $coms = [];
        $contact = $this->contactPrincipal();
        if ($contact) {
            foreach ($contact->communications as $communication) {
                $coms[$communication->type][$communication->name] = $communication->value;
            }
        }

        return $coms;
    }

    public function emailPrincipal(): ?string
    {
        $emails = isset($this->communcationPrincipal()['mail']) ? $this->communcationPrincipal()['mail'] : [];

        return isset($emails['E-mail']) ? $emails['E-mail'] : null;
    }

    public function telPrincipal()
    {
        $telephones = isset($this->communcationPrincipal()['mail']) ? $this->communcationPrincipal()['mail'] : [];

        return isset($telephones['E-mail']) ? $telephones['E-mail'] : null;
    }

    public function sitePrincipal()
    {
        $sites = isset($this->communcationPrincipal()['url']) ? $this->communcationPrincipal()['url'] : [];

        $site = isset($sites['Web']) ? $sites['Web'] : null;
        if ($site) {
            return $site;
        }

        $site = isset($sites['FaceBook']) ? $sites['FaceBook'] : null;
        if ($site) {
            return $site;
        }

        return null;
    }

}
