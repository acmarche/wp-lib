<?php
/**
 * Created by PhpStorm.
 * User: jfsenechal
 * Date: 5/04/18
 * Time: 15:30
 */

namespace AcElasticsearch;

use Elasticsearch\ClientBuilder;

class AcElasticServerManager
{
    /**
     * @var \Elasticsearch\Client
     */
    private $client;

    /**
     * @var string
     */
    private $indexName;

    /**
     * AcElasticServerManager constructor.
     * @param string $indexName
     * @throws \Exception
     */
    public function __construct(string $indexName)
    {
        $hosts = [
            'acmarche:Homer153@localhost',
           // 'acmarche:Homer153@172.17.1.249'
        ];

        try {
            $this->client = ClientBuilder::create()
                ->setHosts($hosts)
                ->build();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $this->indexName = $indexName;

        $this->params = [
            'index' => $this->indexName,
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function createIndex(string $fileName)
    {
        $settings = json_decode(file_get_contents($fileName), true);
        try {
            return $this->client->indices()->create($settings);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @return array|bool
     * @throws \Exception
     */
    public function deleteIndex()
    {
        $params = [
            'index' => $this->indexName,
        ];

        $exist = $this->client->indices()->exists($params);

        if ($exist) {

            try {
                return $this->client->indices()->delete($params);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }

        return true;

    }

    /**
     * @return \Elasticsearch\Client
     */
    public function getClient(): \Elasticsearch\Client
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getIndexName(): string
    {
        return $this->indexName;
    }


}