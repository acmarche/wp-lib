<?php
/**
 * Created by PhpStorm.
 * User: jfsenechal
 * Date: 5/04/18
 * Time: 17:06
 */

namespace AcElasticsearch;

use Elasticsearch\Client;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class AcElasticContentManager
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

    public function __construct(Client $client, string $indexName)
    {
        $this->client = $client;
        $this->indexName = $indexName;
        $this->output = new ConsoleOutput();
    }

    /**
     * @param array $data
     * @return array|callable
     * @throws \Exception
     */
    public function putPost(array $data)
    {
        $this->params = [
            'index' => $this->indexName,
        ];
        $this->params['type'] = '_doc';
        $id = $data['blog']."-".$data['post_ID'];
        $this->params['id'] = $id;
        $this->params['body'] = $data;

        try {
            return $this->client->index($this->params);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function putContent(array $content, Table $table)
    {
        foreach ($content as $posts) {
            foreach ($posts as $post) {
                try {
                    $ligne = [];
                    $this->putPost($post);
                    $ligne[] = $post['post_title'];
                    $table->addRow($ligne);
                } catch (\Exception $e) {
                    print_r($e);
                    die();
                }
            }
        }
    }
}
