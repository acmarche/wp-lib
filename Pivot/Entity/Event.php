<?php


namespace AcMarche\Pivot\Entity;

use AcMarche\Common\PropertyUtils;
use AcMarche\Pivot\PivotType;
use stdClass;

class Event
{
    const CODE_OFFRE = 'EVT';
    const NUM_OFFRE = PivotType::TYPE_EVENEMENT;

    /**
     * @var stdClass
     */
    public $offer;
    /**
     * @var string
     */
    public $codeCgt;
    /**
     * @var stdClass
     */
    public $typeOffre;
    /**
     * @var string
     */
    public $nom;
    /**
     * @deprecated
     * @var string
     */
    public $visibilite;
    /**
     * @var stdClass
     */
    public $visibiliteUrn;
    /**
     * @deprecated
     * @var string
     */
    public $estActive;
    /**
     * @var stdClass
     */
    public $estActiveUrn;
    /**
     * @var stdClass
     */
    public $adresse1;
    /**
     * @var stdClass
     */
    public $spec;
    /**
     * @var string
     */
    public $dateModification;
    /**
     * @var string
     */
    public $textTypeOffre;
    /**
     * @var string
     */
    public $idTypeOffre;
    /**
     * @var string
     */
    public $longitude;
    /**
     * @var string
     */
    public $latitude;
    /**
     * @var string
     */
    public $localite;
    /**
     * @var string
     */
    public $dateDebut;
    /**
     * @var string
     */
    public $dateFin;
    /**
     * @var string
     */
    public $description;
    /**
     * @var string
     */
    public $dateRange;
    /**
     * @var string
     */
    public $code_postal;
    /**
     * @var string
     */
    public $url;
    /**
     * @var string
     */
    public $day;
    /**
     * @var string
     */
    public $month;
    /**
     * @var string
     */
    public $year;
    /**
     * @var array
     */
    public $images;
    /**
     * @var array
     */
    public $contact;

    public function createFromStd(stdClass $data): self
    {
        $this->offer = $data;
        $utils       = new PropertyUtils();
        $properties  = $utils->getProperties(Event::class);

        foreach ($properties as $property) {
            if (isset($data->$property)) {
                $this->$property = $data->$property;
            }
        }

        $this->setTypes();
        $this->setAdresses($this->adresse1);
        $this->setSpecs();
        $this->setImages();
        $this->setContact();

        return $this;
    }

    public function setTypes()
    {
        $type              = $this->typeOffre;
        $this->idTypeOffre = $type->idTypeOffre;
        $labels            = $type->label;
        $label             = $labels[0];//fr 1=> nl
        $label->lang;
        $this->textTypeOffre = $label->value;
    }

    public function getAdresse(stdClass $data): stdClass
    {
        $adresse              = new stdClass;
        $adresse->code_postal = $data->cp;
        $adresses             = $data->localite;//array multi languages
        $adresse->localite    = $adresses[0]->value;//fr
        $adresse->latitude    = $data->latitude;
        $adresse->longitude   = $data->longitude;

        return $adresse;
    }

    public function setAdresses(stdClass $data)
    {
        $adresse           = $this->getAdresse($data);
        $this->code_postal = $adresse->code_postal;
        $this->localite    = $adresse->localite;
        $this->latitude    = $adresse->latitude;
        $this->longitude   = $adresse->longitude;
    }

    public function setSpecs()
    {
        foreach ($this->spec as $spec) {
            //var_dump($spec);
            $urn = $spec->urn;

            if ($urn === 'urn:fld:descmarket') {
                $this->description = $spec->value;
            }

            if ($urn === 'urn:fld:datedebvalid') {
                $this->dateDebut = $spec->value;
            }

            if ($urn === 'urn:fld:datefinvalid') {
                $this->dateFin = $spec->value;
            }

            if ($urn === 'nl:urn:fld:nomofr') {
                $this->nomFr = $spec->value;
            }

            if ($urn === 'urn:fld:catevt:spectacle') {
                $this->category = $spec->value;
            }

            if ($urn === 'urn:obj:date') {
                $specs = $spec->spec;
                foreach ($specs as $spec2) {
                    if ($spec2->urn === 'urn:fld:date:daterange') {
                        $this->dateRange = $spec2->value;
                    }
                }
            }
        }

        if (preg_match("#/#", $this->dateDebut)) {
            [$this->day, $this->month, $this->year] = explode("/", $this->dateDebut);
        }
    }

    private function setContact()
    {
        $contacts  = [];
        if (is_array($this->offer->relOffre)) {
            foreach ($this->offer->relOffre as $relation) {
                if ($relation->urn === 'urn:lnk:offre:voiraussi') {
                    $contact = [];
                    $offre   = $relation->offre;
                    $codeCgt = $offre->codeCgt;
                    $nom     = $offre->nom;
                    foreach ($offre->spec as $spec) {

                        $contact = ['nom' => $nom];
                        if ($spec->urn === 'urn:fld:url') {
                            $contact['nom']     = $spec->value;
                            $contact['adresse'] = $this->getAdresse($spec->adresse1);
                        }
                    }
                    $contacts[] = $contact;
                }
            }
        }

        $this->contact = $contacts;
    }

    private function setImages()
    {
        $imgs      = [];
        $relations = [];
        if (is_array($this->offer->relOffre)) {
            foreach ($this->offer->relOffre as $relation) {
                $offre   = $relation->offre;
                if ($offre->typeOffre->idTypeOffre === PivotType::TYPE_MEDIA) {
                    $codeCgt = $offre->codeCgt;
                    $nom     = $offre->nom;
                    foreach ($offre->spec as $spec) {
                        if ($spec->urn === 'urn:fld:url') {
                            $imgs[] = $spec->value;
                        }
                    }
                    $relations[] = $relation;
                }
            }
        }
        $this->images = $imgs;
    }

}

?>
