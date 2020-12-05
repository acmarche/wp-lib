<?php

namespace AcMarche\Elasticsearch;

use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query\Match;
use Elastica\ResultSet;
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

    /**
     * @var Index
     */
    private $index;

    public function createIndex()
    {
        $this->connect();
        $this->index = $this->client->getIndex('marchebe2');
        try {
            $analyser = Yaml::parse(file_get_contents(__DIR__.'/mappings/analyzers.yaml'));
            $maps     = Yaml::parse(file_get_contents(__DIR__.'/mappings/mapping.yaml'));
        } catch (ParseException $e) {
            printf('Unable to parse the YAML string: %s', $e->getMessage());
        }

        dump($maps);
        $maps['settings']['analysis'] = $analyser;

        $this->index->create($maps);
        dump($maps);
        $properties = [];
        $mapping    = new Mapping($properties);
        // $this->index->setMapping($mapping);
    }

    public function addPost(\WP_Post $post)
    {
        $content          = ['name' => 'Jf', 'content' => 'zeze'];
        $this->serializer = new SerializerJf();
        $this->serializer->serialize($content, 'json');
        $content = '{"name": "hans", "likes": ["2", "3"]}';
        $id      = 5;
        $doc     = new Document($id, $content);
        $this->index->addDocument($doc);
    }

    public function search(string $query): Result
    {
        $result = $this->index->search(new Match('name', $query));

        $result = new ResultSet\DefaultBuilder($hit);


        return $result;
    }
}
