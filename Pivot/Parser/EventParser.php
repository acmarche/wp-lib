<?php


namespace AcMarche\Pivot\Parser;

use AcMarche\Pivot\Event\Entity\Categorie;
use AcMarche\Pivot\Event\Entity\Communication;
use AcMarche\Pivot\Event\Entity\Contact;
use AcMarche\Pivot\Event\Entity\Description;
use AcMarche\Pivot\Event\Entity\Geocode;
use AcMarche\Pivot\Event\Entity\Horaire;
use AcMarche\Pivot\Event\Entity\Horline;
use AcMarche\Pivot\Event\Entity\Localite;
use AcMarche\Pivot\Event\Entity\Media;
use AcMarche\Pivot\Event\Entity\Selection;
use DOMDocument;
use DOMElement;
use Symfony\Component\PropertyAccess\PropertyAccess;

class EventParser extends OffreParser
{
    public function horaires(): array
    {
        $data = [];
        $horaires = $this->offre->getElementsByTagName('horaires');
        if (!$horaires) {
            return [];
        }

        foreach ($horaires as $horaire) {
            $t = new Horaire();
            $t->year = $horaire->getAttributeNode('an');
            foreach ($horaire->childNodes as $child) {
                if ($child->nodeType == XML_ELEMENT_NODE) {
                    foreach ($child->childNodes as $cat) {
                        if ($cat->nodeType == XML_ELEMENT_NODE) {
                            if ($cat->nodeName == 'horline') {
                                $t->horlines[] = $this->extractHoraires($cat);
                            } else {
                                $lg = $cat->getAttribute('lg');
                                if ($lg == 'fr') {
                                    $this->propertyAccessor->setValue($t, $cat->nodeName, $cat->nodeValue);
                                }
                            }
                        }
                    }
                }
            }
            $data[] = $t;
        }

        return $data;
    }

    private function extractHoraires(DOMElement $horline): Horline
    {
        $data = new Horline();
        $data->id = $horline->getAttribute('id');
        foreach ($horline->childNodes as $node) {
            if ($node->nodeType == XML_ELEMENT_NODE) {
                $this->propertyAccessor->setValue($data, $node->nodeName, $node->nodeValue);
            }
        }
        list($data->day, $data->month, $data->year) = explode("/", $data->date_deb);

        return $data;
    }
}
