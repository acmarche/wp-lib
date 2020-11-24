<?php


namespace AcMarche\Pivot\Entity;

use AcMarche\Common\PropertyUtils;
use stdClass;

class Event
{
    const CODE_OFFRE = 'EVT';
    const NUM_OFFRE = 9;

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
        $this->setAdresses();
        $this->setSpecs();
        $this->setRelOffre();

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

    public function setAdresses()
    {
        $adresse1          = $this->adresse1;
        $this->code_postal = $adresse1->cp;
        $localite          = $adresse1->localite;
        $this->localite    = $localite[0]->value;
        $this->latitude    = $adresse1->latitude;
        $this->longitude   = $adresse1->longitude;
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
            list($this->day, $this->month, $this->year) = explode("/", $this->dateDebut);
        }

        return;
        $this->description = $spec[8]->value; //urn:fld:descmarket30
        $this->description = $spec[9]->value; //urn:fld:descmarket
        $specs             = $spec[10]->spec; //urn:obj:date
        if (is_array($specs)) {
            $dateDebut       = $specs[0]->value;//urn:fld:date:datedeb
            $dateFin         = $specs[1]->value;//urn:fld:date:datefin
            $this->dateRange = $specs[2]->value;//urn:fld:date:daterange
        }
    }

    private function setRelOffre()
    {
        $imgs      = [];
        $relations = [];
        if (is_array($this->offer->relOffre)) {
            foreach ($this->offer->relOffre as $relation) {
                if ($relation->urn === 'urn:lnk:media:autre') {
                    //   var_dump($relation);
                    $offre   = $relation->offre;
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
        //echo json_encode($relations);
    }

}

?>
