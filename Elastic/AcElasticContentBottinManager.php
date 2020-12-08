<?php
/**
 * Created by PhpStorm.
 * User: jfsenechal
 * Date: 5/04/18
 * Time: 17:06
 */

namespace AcElasticsearch;

use AcMarche\Bottin\Repository\BottinRepository;
use Elasticsearch\Client;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class AcElasticContentBottinManager
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var string
     */
    private $indexName;
    /**
     * @var array
     */
    private $params;
    /**
     * @var ConsoleOutput
     */
    private $output;
    /**
     * @var BottinRepository
     */
    private $bottinRepository;

    public function __construct(Client $client, string $indexName)
    {
        $this->client = $client;
        $this->indexName = $indexName;
        $this->output = new ConsoleOutput();
        $this->bottinRepository = new BottinRepository();
    }

    public function execute(int $id, string $title)
    {
        $table = new Table($this->output);
        $table->setHeaderTitle($title);
        $table->setHeaders(['Level 0', 'Level 1', 'Fiches']);
        $categories = $this->bottinRepository->getCategories($id);
        foreach ($categories as $category) {
            $row = [];
            $row[] = $category['name'];
            $table->addRow($row);
            $childs = $this->bottinRepository->getCategories($category['id']);
            $this->putCategories($category, $table);

            foreach ($childs as $child) {
                $row = [];
                $row[] = '';
                $row[] = $child['name'];
                $table->addRow($row);
                $this->putCategories($child, $table);
            }
        }

        $table->render();
    }

    public function putFiches(iterable $fiches, Table $table)
    {
        foreach ($fiches as $fiche) {
            $row = [0 => '', 1 => ''];
            try {
                $this->putFiche($fiche);
                $row[3] = $fiche->societe;
                $table->addRow($row);
            } catch (\Exception $e) {
                print_r($e);
                die();
            }
        }
    }

    /**
     * @param array $fiche
     * @return array
     * @throws \Exception
     */
    public function putFiche(\stdClass $fiche)
    {
        $fiche = $this->convertFicheToPost($fiche);
        $this->params = [
            'index' => $this->indexName,
        ];
        $this->params['type'] = '_doc';
        $this->params['id'] = "fiche_".$fiche->id;
        $this->params['body'] = $fiche;
        try {
            return $this->client->index($this->params);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function putCategories(array $category, Table $table)
    {
        $fiches = $this->bottinRepository->getFichesByCategory($category['id']);
        $this->putFiches($fiches, $table);
        $this->setContentForCategory($category, $fiches);
        $this->putCategorie($category);
    }

    /**
     * @param $category
     * @return array
     * @throws \Exception
     */
    public function putCategorie(array $category)
    {
        $category = $this->convertCategoryToPost($category);
        $this->params = [
            'index' => $this->indexName,
        ];
        $this->params['type'] = '_doc';
        $this->params['id'] = 'catbottin_'.$category->id;
        $this->params['body'] = $category;
        try {
            return $this->client->index($this->params);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function convertFicheToPost(\stdClass $fiche): \stdClass
    {
        $std = new \stdClass();
        $std->id = $fiche->id;
        $std->url_cap = $this->generateUrlCapFiche($fiche);
        $std->permalink = $std->url_cap;
        $std->post_title = $fiche->societe;
        $std->name = $fiche->societe;//pour meme champ categorie
        $std->post_content = $this->getContentFiche($fiche);
        $std->post_excerpt = $fiche->comment1;
        $std->post_name = $fiche->slug;
        $std->type = "fiche";

        return $std;
    }

    public function convertCategoryToPost(array $category): \stdClass
    {
        $std = new \stdClass();
        $std->id = $category['id'];
        $std->url_cap = $this->generateUrlCapCategorie($category);
        $std->permalink = $std->url_cap;
        $std->post_title = $category['name'];
        $std->name = $category['name'];//pour meme champ categorie
        $std->post_excerpt = $category['description'];
        $std->post_name = $category['slug'];
        $std->type = "categorybottin";

        return $std;
    }

    public function generateUrlCapCategorie(array $categorie): string
    {
        if ($categorie['parent_id']) {
            $parent = $this->bottinRepository->getCategory($categorie['parent_id']);
            $urlBase = "https://cap.marche.be/secteur/".$parent->slug."/";
            //$content = $this->getDataForCategoryByFiches($categorie);

        } else {
            $urlBase = "https://cap.marche.be/secteur/";
        }

        return $urlBase.$categorie['slug'];
    }

    public function generateUrlCapFiche(\stdClass $fiche): string
    {
        $urlBase = "https://cap.marche.be/commerces-et-entreprises/";
        $classements = $this->bottinRepository->getClassementsFiche($fiche->id);

        if (count($classements) > 0) {
            $first = $classements[0];
            $category = $this->bottinRepository->getCategory($first['category_id']);
            $secteur = $category->slug;

            return $urlBase.$secteur."/".$fiche->slug;
        }

        return $urlBase."/".$fiche->slug;
    }

    public function getContentFiche($fiche)
    {
        if (!isset($fiche->societe)) {
            var_dump($fiche);
            die();
        }

        return ' '.$fiche->societe.' '.$fiche->email.' '.$fiche->website.''.$fiche->twitter.' '.$fiche->facebook.' '.$fiche->nom.' '.$fiche->prenom.' '.$fiche->comment1.''.$fiche->comment2.' '.$fiche->comment3;
    }

    public function setContentForCategory(array $categorie, iterable $fiches)
    {
        $content = '';

        foreach ($fiches as $fiche) {
            $content .= $this->getContentFiche($fiche);
        }

        $categorie['content'] = $content;
    }

}