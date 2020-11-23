<?php


namespace AcMarche\Pivot\Entity;

use AcMarche\Common\PropertyUtils;

class Event
{
    const PREFIX = 'event_';
    const CODE_OFFRE = 'EVT';
    const NUM_OFFRE = 9;

    public $offer;
    public $codeCgt;
    public $typeOffre;
    public $nom;
    /**
     * @deprecated
     * @var
     */
    public $visibilite;
    public $visibiliteUrn;
    /**
     * @deprecated
     * @var
     */
    public $estActive;
    public $estActiveUrn;
    public $adresse1;
    public $spec;
    public $dateModification;
    public $textTypeOffre;
    public $idTypeOffre;
    public $longitude;
    public $latitude;
    public $localite;
    public $dateDebut;
    public $dateFin;
    public $description;
    public $dateRange;
    public $code_postal;
    public $url;
    public $day;
    public $month;
    public $year;

    public function createFromStd(\stdClass $data): self
    {
        $this->offer   = $data;
        $utils         = new PropertyUtils();
        $properties    = $utils->getProperties(Event::class);

        foreach ($properties as $property) {
            if (isset($data->$property)) {
                $this->$property = $data->$property;
            }
        }

        $this->setTypes();
        $this->setAdresses();
        $this->setSpecs();

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
        }

        if (preg_match("#/#", $this->dateDebut)) {
            list($this->day, $this->month, $this->year) = explode("/", $this->dateDebut);
        }

        return;

        if ( ! isset($spec[8]->value)) {
            var_dump(123);
            var_dump($this->nom);
            var_dump($spec[8]);
            var_dump(456);
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

}

?>
