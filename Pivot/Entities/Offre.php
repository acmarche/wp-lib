<?php


namespace AcMarche\Pivot\Entities;

use AcMarche\Pivot\Parser\OffreParser;

class Offre implements OffreInterface
{
    /**
     * @var string
     */
    public $id;
    /**
     * @var Libelle
     */
    public $libelle;
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
    /**
     * @var string|null
     */
    public $modif_date;
    /**
     * @var Horline[]
     */
    public $datesR;
    /**
     * @var string|null
     */
    public $publiable;
    /**
     * @var string|null
     */
    public $image;

    public function __construct()
    {
        $this->categories = [];
        $this->medias = [];
        $this->contacts = [];
        $this->horaires = [];
    }

    public static function createFromDom(\DOMElement $offreDom, \DOMDocument $document): ?Offre
    {
        $parser = new OffreParser($document, $offreDom);
        $offre = new self();
        $offre->id = $parser->offreId();
        $offre->libelle = $parser->getTitre($offreDom);
        $offre->publiable = $parser->getAttributs('publiable');
        $offre->reference = $parser->getAttributs('off_id_ref');
        $offre->modif_date = $parser->getAttributs('modif_date');
        $offre->categories = $parser->categories($offreDom);
        $offre->medias = $parser->medias($offreDom);
        $offre->geocode = $parser->geocodes($offreDom);
        $offre->localisation = $parser->localisation($offreDom);
        $offre->descriptions = $parser->descriptions($offreDom);
        $offre->selections = $parser->selections();
        $offre->contacts = $parser->contacts($offreDom);
        $offre->horaires = $parser->horaires($offreDom);
        $offre->datesR = $offre->dates();
        $offre->image = $offre->firstImage();

        return $offre;
    }

    public function getTitre(?string $language = 'fr'): string
    {
        if ($this->libelle->get($language) && $this->libelle->get($language)) {
            return $this->libelle->get($language);
        }
        //try in french
        if ($titre = $this->getTitre()) {
            return $titre;
        }

        return 'titre found';
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

    function firstImage(): ?string
    {
        return count($this->medias) > 0 ? $this->medias[0]->url : null;
    }

}
