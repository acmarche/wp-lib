<?php

namespace AcMarche\Elasticsearch;

use AcMarche\Common\AcSerializer;
use AcMarche\Theme\Lib\MarcheConst;
use AcMarche\Elasticsearch\Data\ElasticData;
use Elastica\Document;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;

class ElasticIndexer
{
    use ElasticClientTrait;

    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var ElasticData
     */
    private $elasticData;
    /**
     * @var SymfonyStyle|null
     */
    private $outPut;

    public function __construct(?SymfonyStyle $outPut)
    {
        $this->connect();
        $this->serializer  = (new AcSerializer())->create();
        $this->elasticData = new ElasticData();
        $this->outPut      = $outPut;
    }

    public function indexAllPosts(array $sites = array())
    {
        if (count($sites) === 0) {
            $sites = MarcheConst::SITES;
        }

        foreach ($sites as $siteId => $nom) {
            $this->outPut->section($nom);
            $posts = $this->elasticData->getPosts($siteId);
            foreach ($posts as $post) {
                $this->addPost($post);
                $this->outPut->writeln($post['name']);
            }
        }
    }

    private function addPost(array $post)
    {
        $content = $this->serializer->serialize($post, 'json');
        $id      = 'post_'.$post['blog'].'_'.$post['id'];
        $doc     = new Document($id, $content);
        $this->index->addDocument($doc);
    }

    public function indexAllCategories(array $sites = array())
    {
        if (count($sites) === 0) {
            $sites = MarcheConst::SITES;
        }

        foreach ($sites as $siteId => $nom) {
            $this->outPut->section($nom);
            $categories = $this->elasticData->getCategoriesBySite($siteId);
            foreach ($categories as $category) {
                $this->addCategory($category);
                $this->outPut->writeln($category['name']);
            }
        }
    }

    private function addCategory(array $category)
    {
        $content = $this->serializer->serialize($category, 'json');
        $id      = 'category_'.$category['blog'].'_'.$category['id'];
        $doc     = new Document($id, $content);
        $this->index->addDocument($doc);
    }

    public function indexAllBottin()
    {
        $this->indexFiches();
        $this->indexCategoriesBottin();
    }

    public function indexCategoriesBottin()
    {
        $categories = $this->elasticData->getAllCategoriesBottin();
        foreach ($categories as $category) {
            $content = $this->serializer->serialize($category, 'json');
            $id      = 'fiche_'.$category->id;
            $doc     = new Document($id, $content);
            $this->index->addDocument($doc);
            $this->outPut->writeln($category->name);
        }
    }

    public function indexFiches()
    {
        $fiches = $this->elasticData->getAllfiches();
        foreach ($fiches as $fiche) {
            $content = $this->serializer->serialize($fiche, 'json');
            $id      = 'fiche_'.$fiche->id;
            $doc     = new Document($id, $content);
            $this->index->addDocument($doc);
            $this->outPut->writeln($fiche->societe);
        }
    }
}
