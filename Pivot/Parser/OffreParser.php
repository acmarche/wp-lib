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

    public function contacts()
    {
        $data = [];
        $contacts = $this->offre->getElementsByTagName('contacts');

        $contacts = $contacts->item(0);//pour par prendre elements parents
        if (!$contacts instanceof DOMElement) {
            return [];
        }

        // dump($description->tagName);//
        foreach ($contacts->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                $contact = new Contact();
                $libelle = new Libelle();
                $lgs = [];
                foreach ($child->childNodes as $cat) {
                    if ($cat->nodeType == XML_ELEMENT_NODE) {
                        if ($cat->nodeName == 'communications') {
                            $contact->communications = $this->extractCommunications($cat);
                        } else {
                            if ($cat->nodeName === 'lib') {
                                if ($lg = $cat->getAttributeNode('lg')) {
                                    $lgs[$cat->getAttributeNode('lg')->nodeValue] = $cat->nodeValue;
                                } else {
                                    $lgs['main'] = $cat->nodeValue;
                                }
                            }
                            $this->propertyAccessor->setValue($contact, $cat->nodeName, $cat->nodeValue);
                        }
                    }
                }
                $contact->lgs = $lgs;
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
                $description->libelle = $libelle;
                $libelle = new Libelle();
                $textes = $this->xpath->query("texte", $descriptionDom);
                foreach ($textes as $texte) {
                    $language = $texte->getAttributeNode('lg');
                    if ($language) {
                        $libelle->add($language->nodeValue, $lib->nodeValue);
                    } else {
                        $libelle->add('default', $lib->nodeValue);
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
                $t = new Selection();
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
                $category->libelle = $libelle;
                $data[] = $category;
            }
        }

        return $data;
    }

    private function extractCommunications(DOMElement $communication): array
    {
        $data = [];
        foreach ($communication->childNodes as $childNode) {
            $t = new Communication();
            if ($childNode->nodeType == XML_ELEMENT_NODE) {
                $type = $this->getAttribute($childNode, 'typ');
                $t->type = $type;
                foreach ($childNode->childNodes as $node) {
                    if ($node->nodeType == XML_ELEMENT_NODE) {
                        $lg = $this->getAttribute($node, 'lg');
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
                $horaire->libelle = $libelle;
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

    public function horairesOld(): array
    {
        $data = [];
        $horaires = $this->offre->getElementsByTagName('horaires');
        $horaires = $horaires->item(0);//pour par prendre elements parents

        if (!$horaires instanceof DOMElement) {
            return [];
        }

        $year = $horaires->getAttributeNode('an')->value;
        foreach ($horaires->childNodes as $child) {
            $horaire = new Horaire();
            $libs = [];
            $textes = [];
            $horaire->year = $year;
            if ($child->nodeType == XML_ELEMENT_NODE) {
                foreach ($child->childNodes as $cat) {
                    if ($cat->nodeType == XML_ELEMENT_NODE) {
                        if ($cat->nodeName == 'lib') {
                            $libs[$this->getAttribute($cat, 'lg')] = $cat->nodeValue;
                        }
                        if ($cat->nodeName == 'texte') {
                            $textes[$this->getAttribute($cat, 'lg')] = $cat->nodeValue;
                        }
                        if ($cat->nodeName == 'horline') {
                            $horaire->horlines[] = $this->extractHoraires($cat);
                        } else {
                            $lg = $this->getAttribute($cat, 'lg');
                            if ($lg == 'fr') {
                                $this->propertyAccessor->setValue($horaire, $cat->nodeName, $cat->nodeValue);
                            }
                        }
                    }
                }
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
