<?php

namespace AcMarche\Elasticsearch;

use AcMarche\Common\AcSerializer;
use AcMarche\Elasticsearch\Data\DocumentElastic;
use AcMarche\Elasticsearch\Data\ElasticData;
use AcMarche\Theme\Inc\Theme;
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
            $sites = Theme::SITES;
        }

        foreach ($sites as $siteId => $nom) {
            $this->outPut->section($nom);
            $posts = $this->elasticData->getPosts($siteId);
            foreach ($posts as $post) {
                $this->addPost($post, $siteId);
                $this->outPut->writeln($post->name);
            }
        }
    }

    private function addPost(DocumentElastic $documentElastic, int $blogId)
    {
        $content = $this->serializer->serialize($documentElastic, 'json');
        $id      = 'post_'.$blogId.'_'.$documentElastic->id;
        $doc     = new Document($id, $content);
        $this->index->addDocument($doc);
    }

    public function indexAllCategories(array $sites = array())
    {
        if (count($sites) === 0) {
            $sites = Theme::SITES;
        }

        foreach ($sites as $siteId => $nom) {
            $this->outPut->section($nom);
            $categories = $this->elasticData->getCategoriesBySite($siteId);
            foreach ($categories as $documentElastic) {
                $this->addCategory($documentElastic, $siteId);
                $this->outPut->writeln($documentElastic->name);
            }
        }
    }

    private function addCategory(DocumentElastic $documentElastic, int $blodId)
    {
        $content = $this->serializer->serialize($documentElastic, 'json');
        $id      = 'category_'.$blodId.'_'.$documentElastic->id;
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
        foreach ($categories as $documentElastic) {
            $content = $this->serializer->serialize($documentElastic, 'json');
            $id      = 'fiche_'.$documentElastic->id;
            $doc     = new Document($id, $content);
            $this->index->addDocument($doc);
            $this->outPut->writeln($documentElastic->name);
        }
    }

    public function indexFiches()
    {
        $fiches = $this->elasticData->getAllfiches();
        foreach ($fiches as $documentElastic) {
            $content = $this->serializer->serialize($documentElastic, 'json');
            $id      = 'fiche_'.$documentElastic->id;
            $doc     = new Document($id, $content);
            $this->index->addDocument($doc);
            $this->outPut->writeln($documentElastic->name);
        }
    }
}
