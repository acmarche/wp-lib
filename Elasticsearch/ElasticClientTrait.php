<?php

namespace AcMarche\Elasticsearch;

use Elastica\Client;
use Elastica\Index;

trait ElasticClientTrait
{
    /**
     * @var Client
     */
    public $client;

    /**
     * @var Index
     */
    private $index;

    public function connect(string $host = 'localhost', int $port = 9200)
    {
        $this->client = new Client(
            [
                'host' => $host,
                'port' => $port,
            ]
        );
        $this->setIndex(ElasticServer::INDEX_NAME_MARCHE_BE);
    }

    public function setIndex(string $name)
    {
        $this->index = $this->client->getIndex($name);
    }
}
