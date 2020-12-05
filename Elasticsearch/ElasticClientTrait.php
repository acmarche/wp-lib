<?php

namespace AcMarche\Elasticsearch;

use Elastica\Client;

trait ElasticClientTrait
{
    /**
     * @var Client
     */
    private $client;

    public function connect(string $host = 'localhost', int $port = 9200)
    {
        $this->client = new Client(
            [
                'host' => $host,
                'port' => $port,
            ]
        );
    }

}
