<?php

namespace AcMarche\Elasticsearch;

use AcMarche\Common\AcSerializer;
use AcMarche\Common\MarcheConst;
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

    public function indexAll()
    {
        $this->indexAllPosts();
        $this->indexAllCategories();
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

    public function addPost(array $post)
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

    public function addCategory(array $category)
    {
        $content = $this->serializer->serialize($category, 'json');
        $id      = 'category_'.$category['blog'].'_'.$category['id'];
        $doc     = new Document($id, $content);
        $this->index->addDocument($doc);
    }
}
