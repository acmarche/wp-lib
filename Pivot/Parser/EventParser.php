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

class EventParser
{
    /**
     * @var DOMElement
     */
    public $offre;
    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $propertyAccessor;

    public function __construct(DOMElement $offre)
    {
        $this->offre            = $offre;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function parseOffre(string $xml): DOMElement
    {
        $domdoc = new DOMDocument();
        $domdoc->loadXML($xml);
        $this->offre = $domdoc->documentElement;
        //$this->offre->nodeName;//offre
        //dump($offre->tagName);//offre
        return $this->offre;
    }

    public function offreId()
    {
        //$this->offre->attributes->item(0);//donne attribut id
        // $idOffreValue = $this->offre->getAttributeNode('id')->nodeValue;
        return $this->offre->getAttribute('id');
    }

    public function getAttributs(string $name): string
    {
        $domElement = $this->offre->getElementsByTagName($name);

        return $domElement->item(0)->nodeValue;
    }

    public function geocodes()
    {
        $coordinates = new Geocode();
        $geocodes    = $this->offre->getElementsByTagName('geocodes');
        $geocode     = $geocodes->item(0);
        if ( ! $geocode instanceof DOMElement) {
            return [];
        }

        foreach ($geocode->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                foreach ($child->childNodes as $cat) {
                    if ($cat->nodeType == XML_ELEMENT_NODE) {
                        $this->propertyAccessor->setValue($coordinates, $cat->nodeName, $cat->nodeValue);
                    }
                }
            }
        }

        return $coordinates;
    }

    public function localisation()
    {
        $data          = new Localite();
        $localisations = $this->offre->getElementsByTagName('localisation');
        $localisation  = $localisations->item(0);
        if ( ! $localisation instanceof DOMElement) {
            return [];
        }

        foreach ($localisation->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                //$catId      = $child->getAttributeNode('id');//134
                $data->id = $child->getAttributeNode('id')->nodeValue;
                foreach ($child->childNodes as $cat) {
                    if ($cat->nodeType == XML_ELEMENT_NODE) {
                        $this->propertyAccessor->setValue($data, $cat->nodeName, $cat->nodeValue);
                    }
                }
            }
        }

        return $data;
    }

    public function contacts()
    {
        $data     = [];
        $contacts = $this->offre->getElementsByTagName('contacts');
        $contacts = $contacts->item(0);//pour par prendre elements parents
        if ( ! $contacts instanceof DOMElement) {
            return [];
        }

        // dump($description->tagName);//
        foreach ($contacts->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                $t = new Contact();
                // dump($child->tagName);
                foreach ($child->childNodes as $cat) {
                    if ($cat->nodeType == XML_ELEMENT_NODE) {
                        if ($cat->nodeName == 'communications') {
                            $t->communications = $this->extractCommunications($cat);
                        } else {
                            $this->propertyAccessor->setValue($t, $cat->nodeName, $cat->nodeValue);
                        }
                    }
                }
                $data[] = $t;
            }
        }

        return $data;
    }

    public function descriptions()
    {
        $data         = [];
        $descriptions = $this->offre->getElementsByTagName('descriptions');
        $descriptions = $descriptions->item(0);//pour par prendre elements parents
        if ( ! $descriptions instanceof DOMElement) {
            return [];
        }
        foreach ($descriptions->childNodes as $child) {
            $t = new Description();
            if ($child->nodeType == XML_ELEMENT_NODE) {
                $t->dat = $child->getAttributeNode('dat')->nodeValue;
                $t->lot = $child->getAttributeNode('lot')->nodeValue;
                $t->typ = $child->getAttributeNode('typ')->nodeValue;
                foreach ($child->childNodes as $cat) {
                    if ($cat->nodeType == XML_ELEMENT_NODE) {
                        $lg = $cat->getAttribute('lg');
                        if ($lg == 'fr') {
                            $this->propertyAccessor->setValue($t, $cat->nodeName, $cat->nodeValue);
                        }
                    }
                }
                $data[] = $t;
            }
        }

        return $data;
    }

    public function horaires(): array
    {
        $data     = [];
        $horaires = $this->offre->getElementsByTagName('horaires');
        if ( ! $horaires) {
            return [];
        }

        foreach ($horaires as $horaire) {
            $t       = new Horaire();
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

    public function medias(): array
    {
        $data   = [];
        $object = $this->offre->getElementsByTagName('medias');
        $medias = $object->item(0);//pour par prendre elements parents
        if ( ! $medias instanceof DOMElement) {
            return [];
        }
        $t = new Media();
        foreach ($medias->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                $t->ext = $child->getAttributeNode('ext')->nodeValue;
                foreach ($child->childNodes as $cat) {
                    if ($cat->nodeType == XML_ELEMENT_NODE) {
                        $this->propertyAccessor->setValue($t, $cat->nodeName, $cat->nodeValue);
                        $lg = $cat->getAttribute('lg');
                        if ($lg == 'fr') {

                        }
                    }
                }
            }
        }
        $data[] = $t;

        return $data;
    }

    public function selections(): array
    {
        $data       = [];
        $object     = $this->offre->getElementsByTagName('selections');
        $selections = $object->item(0);//pour par prendre elements parents
        if ( ! $selections instanceof DOMElement) {
            return [];
        }

        foreach ($selections->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                $t     = new Selection();
                $t->id = $child->getAttributeNode('id')->nodeValue;
                $t->cl = $child->getAttributeNode('cl')->nodeValue;
                foreach ($child->childNodes as $cat) {
                    if ($cat->nodeType == XML_ELEMENT_NODE) {
                        $this->propertyAccessor->setValue($t, $cat->nodeName, $cat->nodeValue);
                    }
                }
                $data[] = $t;
            }
        }

        return $data;
    }

    public function categories(): array
    {
        $data       = [];
        $categories = $this->offre->getElementsByTagName('categories');
        $category   = $categories->item(0);//pour par prendre elements parents
        if ( ! $category instanceof DOMElement) {
            return [];
        }
        $t = new Categorie();
        foreach ($category->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                $catId = $child->getAttributeNode('id');
                foreach ($child->childNodes as $cat) {
                    if ($cat->nodeType == XML_ELEMENT_NODE) {
                        $lg = $cat->getAttribute('lg');
                        if ($lg == 'fr') {
                            $this->propertyAccessor->setValue($t, $cat->nodeName, $cat->nodeValue);
                            //   $t[$catId->nodeValue] = $cat->nodeValue;
                        }
                    }
                }
            }
        }
        $data[] = $t;

        return $data;
    }

    private function extractCommunications(DOMElement $communication): array
    {
        $data = [];
        foreach ($communication->childNodes as $childNode) {
            $t = new Communication();
            if ($childNode->nodeType == XML_ELEMENT_NODE) {
                $type    = $childNode->getAttribute('typ');
                $t->type = $type;
                foreach ($childNode->childNodes as $node) {
                    if ($node->nodeType == XML_ELEMENT_NODE) {
                        $lg = $node->getAttribute('lg');
                        if ($lg == 'fr') {
                            $t->name = $node->nodeValue;
                        }
                        if ($node->nodeName == 'val') {
                            $t->value = $node->nodeValue;
                        }
                    }
                }
            }
            if ($t->value != '') {
                $data[] = $t;
            }
        }

        return $data;
    }

    private function extractHoraires(DOMElement $horline): Horline
    {
        $data     = new Horline();
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