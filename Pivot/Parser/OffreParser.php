<?php


namespace AcMarche\Pivot\Parser;

use AcMarche\Pivot\Entities\Categorie;
use AcMarche\Pivot\Entities\Communication;
use AcMarche\Pivot\Entities\Contact;
use AcMarche\Pivot\Entities\Description;
use AcMarche\Pivot\Entities\Geocode;
use AcMarche\Pivot\Entities\Horaire;
use AcMarche\Pivot\Entities\Horline;
use AcMarche\Pivot\Entities\Libelle;
use AcMarche\Pivot\Entities\Localite;
use AcMarche\Pivot\Entities\Media;
use AcMarche\Pivot\Entities\Selection;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Symfony\Component\PropertyAccess\PropertyAccess;

class OffreParser
{
    /**
     * @var DOMElement
     */
    public $offre;
    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    public $propertyAccessor;
    /**
     * @var DOMDocument
     */
    private $document;
    /**
     * @var DOMXPath
     */
    private $xpath;

    public function __construct(DOMDocument $document, DOMElement $offre)
    {
        $this->offre = $offre;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->document = $document;
        $this->xpath = new DOMXPath($document);
    }

    public function offreId()
    {
        return $this->getAttribute($this->offre, 'id');
    }

    public function getAttributs(string $name): ?string
    {
        $domList = $this->offre->getElementsByTagName($name);
        if ($domList instanceof \DOMNodeList) {
            $domElement = $domList->item(0);
            if ($domElement instanceof DOMElement) {
                return $domElement->nodeValue;
            }
        }

        return null;
    }

    public function getTitre(DOMElement $offreDom): Libelle
    {
        $titles = $this->xpath->query("titre", $offreDom);
        $libelle = new Libelle();
        foreach ($titles as $title) {
            $language = $title->getAttributeNode('lg');
            $libelle->add($language->nodeValue, $title->nodeValue);
        }

        return $libelle;
    }

    /**
     * @param DOMElement $offreDom
     * @return Geocode
     */
    public function geocodes(DOMElement $offreDom): Geocode
    {
        $coordinates = new Geocode();
        $geocodes = $this->xpath->query("geocodes", $offreDom);
        $geocode = $geocodes->item(0);
        if (!$geocode instanceof DOMElement) {
            return $coordinates;
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

    public function localisation(DOMElement $offreDom): Localite
    {
        $data = new Localite();
        $localisations = $offreDom->getElementsByTagName('localisation');
        $localisation = $localisations->item(0);
        if (!$localisation instanceof DOMElement) {
            return $data;
        }

        foreach ($localisation->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
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

    /**
     * @param DOMElement $offreDom
     * @return Contact[]
     */
    public function contacts(DOMElement $offreDom): array
    {
        $data = [];
        $contacts = $this->xpath->query("contacts", $offreDom);
        if ($contacts->length == 0) {
            return [];
        }
        foreach ($contacts->item(0)->childNodes as $contactDom) {
            if ($contactDom->nodeType == XML_ELEMENT_NODE) {
                $contact = new Contact();
                $libelle = new Libelle();
                $contact->tri = $contactDom->getAttributeNode('tri')->nodeValue;
                $libs = $this->xpath->query("lib", $contactDom);
                foreach ($libs as $lib) {
                    $language = $lib->getAttributeNode('lg');
                    if ($language) {
                        $libelle->add($language->nodeValue, $lib->nodeValue);
                    } else {
                        $libelle->add('default', $lib->nodeValue);
                    }
                }
                $contact->lib = $libelle;
                $contact->communications = $this->extractCommunications($contactDom);
                foreach ($contactDom->childNodes as $attribute) {
                    if ($attribute->nodeType == XML_ELEMENT_NODE) {
                        if ($attribute->nodeName != 'lib' && $attribute->nodeName != 'communications') {
                            $this->propertyAccessor->setValue($contact, $attribute->nodeName, $attribute->nodeValue);
                        }
                    }
                }
                $data[] = $contact;
            }

        }

        return $data;

    }

    /**
     * @param DOMElement $offreDom
     * @return Description[]
     */
    public function descriptions(DOMElement $offreDom): array
    {
        $data = [];
        $descriptions = $this->xpath->query("descriptions", $offreDom);
        foreach ($descriptions->item(0)->childNodes as $descriptionDom) {
            if ($descriptionDom instanceof \DOMElement) {
                $description = new Description();
                $libelle = new Libelle();
                $description->dat = $descriptionDom->getAttributeNode('dat')->nodeValue;
                $description->lot = $descriptionDom->getAttributeNode('lot')->nodeValue;
                $description->tri = $descriptionDom->getAttributeNode('tri')->nodeValue;
                $description->typ = $descriptionDom->getAttributeNode('typ')->nodeValue;
                $libs = $this->xpath->query("lib", $descriptionDom);
                foreach ($libs as $lib) {
                    $language = $lib->getAttributeNode('lg');
                    if ($language) {
                        $libelle->add($language->nodeValue, $lib->nodeValue);
                    } else {
                        $libelle->add('default', $lib->nodeValue);
                    }
                }
                $description->lib = $libelle;
                $libelle = new Libelle();
                $textes = $this->xpath->query("texte", $descriptionDom);
                foreach ($textes as $texte) {
                    $language = $texte->getAttributeNode('lg');
                    if ($language) {
                        $libelle->add($language->nodeValue, $texte->nodeValue);
                    } else {
                        $libelle->add('default', $texte->nodeValue);
                    }
                }
                $description->texte = $libelle;
                $data[] = $description;
            }
        }

        return $data;
    }

    /**
     * @param DOMElement $offreDom
     * @return Media[]
     */
    public function medias(DOMElement $offreDom): array
    {
        $data = [];
        $categories = $this->xpath->query("medias", $offreDom);
        foreach ($categories->item(0)->childNodes as $categoryDom) {
            if ($categoryDom instanceof \DOMElement) {
                $media = new Media();
                $media->ext = $categoryDom->getAttributeNode('ext')->nodeValue;
                $media->libelle = $this->getTitre($categoryDom);
                foreach ($categoryDom->childNodes as $cat) {
                    if ($cat->nodeType == XML_ELEMENT_NODE) {
                        $this->propertyAccessor->setValue($media, $cat->nodeName, $cat->nodeValue);
                    }
                }
                $data[] = $media;
            }
        }
        array_map(
            function ($media) {
                $media->url = preg_replace("#http:#", "https:", $media->url);
            },
            $data
        );

        return $data;
    }

    /**
     * @return Selection[]
     */
    public function selections(): array
    {
        $data = [];
        $object = $this->offre->getElementsByTagName('selections');
        $selections = $object->item(0);//pour par prendre elements parents
        if (!$selections instanceof DOMElement) {
            return [];
        }

        foreach ($selections->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                $selection = new Selection();
                $selection->id = $child->getAttributeNode('id')->nodeValue;
                $selection->cl = $child->getAttributeNode('cl')->nodeValue;
                foreach ($child->childNodes as $cat) {
                    if ($cat->nodeType == XML_ELEMENT_NODE) {
                        $this->propertyAccessor->setValue($selection, $cat->nodeName, $cat->nodeValue);
                    }
                }
                $data[] = $selection;
            }
        }

        return $data;
    }

    /**
     * @param DOMElement $offreDom
     * @return Categorie[]
     */
    public function categories(DOMElement $offreDom): array
    {
        $data = [];
        $categories = $this->xpath->query("categories", $offreDom);
        foreach ($categories->item(0)->childNodes as $categoryDom) {
            if ($categoryDom instanceof \DOMElement) {
                $category = new Categorie();
                $libelle = new Libelle();
                $category->id = $categoryDom->getAttributeNode('id')->nodeValue;
                $category->tri = $categoryDom->getAttributeNode('tri')->nodeValue;
                $labels = $this->xpath->query("lib", $categoryDom);
                foreach ($labels as $label) {
                    $language = $label->getAttributeNode('lg');
                    if ($language) {
                        $libelle->add($language->nodeValue, $label->nodeValue);
                    } else {
                        $libelle->add('default', $label->nodeValue);
                    }
                }
                $category->lib = $libelle;
                $data[] = $category;
            }
        }

        return $data;
    }

    /**
     * @param DOMElement $contactDom
     * @return Communication[]
     */
    private function extractCommunications(DOMElement $contactDom): array
    {
        $data = [];
        $communications = $this->xpath->query("communications", $contactDom);
        foreach ($communications as $communicationsDom) {
            foreach ($communicationsDom->childNodes as $communicationDom) {
                if ($communicationDom instanceof DOMElement) {
                    $communication = new Communication();
                    $communication->typ = $communicationDom->getAttributeNode('typ')->nodeValue;
                    $communication->tri = $communicationDom->getAttributeNode('tri')->nodeValue;
                    $labels = $this->xpath->query("lib", $communicationDom);
                    $libelle = new Libelle();
                    foreach ($labels as $label) {
                        $language = $label->getAttributeNode('lg');
                        if ($language) {
                            $libelle->add($language->nodeValue, $label->nodeValue);
                        } else {
                            $libelle->add('default', $label->nodeValue);
                        }
                    }
                    $communication->lib = $libelle;
                    $vals = $this->xpath->query("val", $communicationDom);
                    $communication->val = $vals->item(0)->nodeValue;
                    if ($communication->val != '') {
                        $data[] = $communication;
                    }
                }
            }
        }

        return $data;
    }

    public function horaires(DOMElement $offreDom): array
    {
        $data = [];
        $horaires = $this->xpath->query("horaires", $offreDom);
        $horaires = $horaires->item(0);//pour par prendre elements parents
        if (!$horaires instanceof DOMElement) {
            return [];
        }
        $year = $horaires->getAttributeNode('an')->value;

        foreach ($horaires->childNodes as $horaireDom) {
            if ($horaireDom instanceof \DOMElement) {
                $horaire = new Horaire();
                $horaire->year = $year;
                $labels = $this->xpath->query("lib", $horaireDom);
                $libelle = new Libelle();
                foreach ($labels as $label) {
                    $language = $label->getAttributeNode('lg');
                    if ($language) {
                        $libelle->add($language->nodeValue, $label->nodeValue);
                    } else {
                        $libelle->add('default', $label->nodeValue);
                    }
                }
                $horaire->lib = $libelle;
                $textes = $this->xpath->query("texte", $horaireDom);
                $libelle = new Libelle();
                foreach ($textes as $texte) {
                    $language = $texte->getAttributeNode('lg');
                    if ($language) {
                        $libelle->add($language->nodeValue, $texte->nodeValue);
                    } else {
                        $libelle->add('default', $texte->nodeValue);
                    }
                }
                $horaire->texte = $libelle;
                $horaire->horlines = $this->extractHoraires($horaireDom);
                $data[] = $horaire;
            }
        }

        return $data;
    }

    /**
     * @param DOMElement $horaireDom
     * @return Horline[]
     */
    private function extractHoraires(DOMElement $horaireDom): array
    {
        $data = [];
        $horlines = $this->xpath->query("horline", $horaireDom);
        foreach ($horlines as $horlineDom) {
            $horline = new Horline();
            $horline->id = $this->getAttribute($horlineDom, 'id');
            foreach ($horlineDom->childNodes as $node) {
                if ($node->nodeType == XML_ELEMENT_NODE) {
                    $this->propertyAccessor->setValue($horline, $node->nodeName, $node->nodeValue);
                }
            }
            list($horline->day, $horline->month, $horline->year) = explode("/", $horline->date_deb);
            $data[] = $horline;
        }

        return $data;
    }

    private function getAttribute(?DOMElement $element, string $name): string
    {
        if ($element) {
            return $element->getAttribute($name);
        }

        return '';
    }

}
