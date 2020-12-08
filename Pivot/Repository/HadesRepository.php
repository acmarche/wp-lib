<?php


namespace AcMarche\Pivot\Repository;


use AcMarche\Common\Cache;
use AcMarche\Pivot\ConnectionHadesTrait;
use Symfony\Contracts\Cache\CacheInterface;

class HadesRepository
{
    use ConnectionHadesTrait;

    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct()
    {
        $this->connect();
        $this->cache = Cache::instance();
    }

    /**
     * https://www.ftlb.be/rss/xmlinterreg.php?pays=9&offre=evenements&quoi=tout
     * @return string
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getEvents(): string
    {
        return $this->cache->get(
            'events_hades'.time(),
            function () {
                //$url      = $this->url.'pays=9&offre=evenements&quoi=tout';
                $response = $this->httpClient->request(
                    'GET',
                    $this->url,
                    [
                        'query' => [
                            'pays'  => 9,
                            'offre' => 'evenements',
                            'quoi'  => 'tout',
                        ],
                    ]
                );

                return $response->getContent();
            }
        );
    }
}
