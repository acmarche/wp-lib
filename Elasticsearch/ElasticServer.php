<?php

namespace AcMarche\Elasticsearch;

use Elastica\Document;
use Elastica\Mapping;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * https://elasticsearch-cheatsheet.jolicode.com/
 * Class ElasticServer
 * @package AcMarche\Elasticsearch
 */
class ElasticServer
{

    use ElasticClientTrait;

    const INDEX_NAME_MARCHE_BE = 'marchebe2';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct()
    {
        $this->connect();
        $this->index = $this->client->getIndex(self::INDEX_NAME_MARCHE_BE);
    }

    public function createIndex()
    {
        try {
            $analyser = Yaml::parse(file_get_contents(__DIR__.'/mappings/analyzers.yaml'));
            $maps     = Yaml::parse(file_get_contents(__DIR__.'/mappings/mapping.yaml'));
        } catch (ParseException $e) {
            printf('Unable to parse the YAML string: %s', $e->getMessage());
        }

        $maps['settings']['analysis'] = $analyser;
        $response                     = $this->index->create($maps, true);
        dump($response);
    }

    public function setProperties()
    {
        try {
            $properties = Yaml::parse(file_get_contents(__DIR__.'/mappings/properties.yaml'));
        } catch (ParseException $e) {
            printf('Unable to parse the YAML string: %s', $e->getMessage());
        }

        $mapping  = new Mapping($properties);
        $response = $this->index->setMapping($mapping);
        dump($response);
    }

    public function indexAllPosts()
    {
        $this->serializer = (new ElasticSerializer())->create();
        $elasticData      = new ElasticData();
        $posts            = $elasticData->getPosts(1);
        foreach ($posts as $post) {
            $this->addPost($post);
        }
    }

    public function addPost(array $post)
    {
        $content = $this->serializer->serialize($post, 'json');
        $id      = $post['blog'].'_'.$post['post_ID'];
        $doc     = new Document($id, $content);
        $this->index->addDocument($doc);
    }
}
