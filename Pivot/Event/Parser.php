<?php


namespace AcMarche\Pivot\Event;


use DOMDocument;
use DOMElement;
use DOMNodeList;

class Parser
{
    /**
     * @var DOMElement
     */
    public $offre;

    public function __construct(string $xml)
    {
        $this->offre = $this->parseOffre($xml);
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
        // $idOffreValue = $this->offre->getAttributeNode('id')->nodeValue;
        $idOffreValue = $this->offre->getAttribute('id');

        //$this->offre->attributes->item(0);//donne attribut id

        return $idOffreValue;
    }

    public function childs(DOMNodeList $details)
    {

    }

    public function getAttributs(string $name): string
    {
        $domElement = $this->offre->getElementsByTagName($name);

        return $domElement->item(0)->nodeValue;
    }

    public function geocodes()
    {
        $coordinates = [];
        $geocodes    = $this->offre->getElementsByTagName('geocodes');
        $geocode     = $geocodes->item(0);

        foreach ($geocode->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                foreach ($child->childNodes as $cat) {
                    if ($cat->nodeType == XML_ELEMENT_NODE) {
                        $coordinates[$cat->nodeName] = $cat->nodeValue;
                    }
                }
            }
        }

        return $coordinates;
    }

    public function localisation()
    {
        $data          = [];
        $localisations = $this->offre->getElementsByTagName('localisation');
        $localisation  = $localisations->item(0);

        foreach ($localisation->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                $catId = $child->getAttributeNode('id');//134
                foreach ($child->childNodes as $cat) {
                    if ($cat->nodeType == XML_ELEMENT_NODE) {
                        $data[$cat->nodeName] = $cat->nodeValue;
                    }
                }
            }
        }

        return $data;
    }


    public function categories()
    {
        $data       = [];
        $categories = $this->offre->getElementsByTagName('categories');
        $category   = $categories->item(0);
        //    foreach ($categories as $category) {
        $t = [];
        dump($category->tagName);//categories
        foreach ($category->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                dump($child->tagName);//categorie
                $catId = $child->getAttributeNode('id');
                foreach ($child->childNodes as $cat) {
                    if ($cat->nodeType == XML_ELEMENT_NODE) {
                        $lg = $cat->getAttribute('lg');
                        if ($lg == 'fr') {
                            dump($cat->nodeValue);
                            $t[$catId->nodeValue] = $cat->nodeValue;
                        }
                    }
                }
            }
        }
        $data[] = $t;

        //   }
        return $data;
    }

}
