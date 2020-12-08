<?php

namespace AcMarche\Elasticsearch;

use AcMarche\Bottin\Repository\BottinRepository;

class ElasticBottinData
{
    public function __construct()
    {
        $this->bottinRepository = new BottinRepository();
    }

    public function convertFicheToPost(\stdClass $fiche): \stdClass
    {
        $std               = new \stdClass();
        $std->id           = $fiche->id;
        $std->url_cap      = $this->generateUrlCapFiche($fiche);
        $std->permalink    = $std->url_cap;
        $std->post_title   = $fiche->societe;
        $std->name         = $fiche->societe;//pour meme champ categorie
        $std->post_content = $this->getContentFiche($fiche);
        $std->post_excerpt = $fiche->comment1;
        $std->post_name    = $fiche->slug;
        $std->type         = "fiche";

        return $std;
    }

    public function convertCategoryToPost(array $category): \stdClass
    {
        $std               = new \stdClass();
        $std->id           = $category['id'];
        $std->url_cap      = $this->generateUrlCapCategorie($category);
        $std->permalink    = $std->url_cap;
        $std->post_title   = $category['name'];
        $std->name         = $category['name'];//pour meme champ categorie
        $std->post_excerpt = $category['description'];
        $std->post_name    = $category['slug'];
        $std->type         = "categorybottin";

        return $std;
    }

    public function generateUrlCapCategorie(array $categorie): string
    {
        if ($categorie['parent_id']) {
            $parent  = $this->bottinRepository->getCategory($categorie['parent_id']);
            $urlBase = "https://cap.marche.be/secteur/".$parent->slug."/";
            //$content = $this->getDataForCategoryByFiches($categorie);

        } else {
            $urlBase = "https://cap.marche.be/secteur/";
        }

        return $urlBase.$categorie['slug'];
    }

    public function generateUrlCapFiche(\stdClass $fiche): string
    {
        $urlBase     = "https://cap.marche.be/commerces-et-entreprises/";
        $classements = $this->bottinRepository->getClassementsFiche($fiche->id);

        if (count($classements) > 0) {
            $first    = $classements[0];
            $category = $this->bottinRepository->getCategory($first['category_id']);
            $secteur  = $category->slug;

            return $urlBase.$secteur."/".$fiche->slug;
        }

        return $urlBase."/".$fiche->slug;
    }

    public function getContentFiche($fiche): string
    {
        return ' '.$fiche->societe.' '.$fiche->email.' '.$fiche->website.''.$fiche->twitter.' '.$fiche->facebook.' '.$fiche->nom.' '.$fiche->prenom.' '.$fiche->comment1.''.$fiche->comment2.' '.$fiche->comment3;
    }

    public function getContentForCategory(iterable $fiches): string
    {
        $content = '';

        foreach ($fiches as $fiche) {
            dump("f: ".$fiche->societe);
            $content .= $this->getContentFiche($fiche);
        }

        return $content;
    }

}