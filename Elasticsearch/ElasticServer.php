<?php

namespace AcMarche\Elasticsearch;

use Elastica\Mapping;
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

    public function __construct()
    {
        $this->connect();
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
}
